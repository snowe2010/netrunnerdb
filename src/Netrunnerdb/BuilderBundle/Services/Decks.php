<?php


namespace Netrunnerdb\BuilderBundle\Services;

use Doctrine\ORM\EntityManager;
use Netrunnerdb\BuilderBundle\Services\Judge;
use Netrunnerdb\BuilderBundle\Entity\Deck;
use Netrunnerdb\BuilderBundle\Entity\Deckslot;

class Decks 
{
	public function __construct(EntityManager $doctrine, Judge $judge) {
		$this->doctrine = $doctrine;
        $this->judge = $judge;
	}
    

    public function getByUser ($user)
    {
        $dbh = $this->doctrine->getConnection();
        $decks = $dbh->executeQuery(
                "SELECT
				d.id,
				d.name,
				d.creation,
				d.description,
                d.tags,
				d.problem,
				c.title identity_title,
                c.code identity_code,
				f.code faction_code,
				s.name side
				from deck d
				left join card c on d.identity_id=c.id
				left join faction f on c.faction_id=f.id
				left join side s on d.side_id=s.id
				where d.user_id=?
				order by lastupdate desc", array(
                        $user->getId()
                ))
            ->fetchAll();
        
        $rows = $dbh->executeQuery(
                "SELECT
				s.deck_id,
				c.code card_code,
				s.quantity qty
				from deckslot s
				join card c on s.card_id=c.id
				join deck d on s.deck_id=d.id
				where d.user_id=?", array(
                        $user->getId()
                ))
            ->fetchAll();
        
        $cards = array();
        foreach ($rows as $row) {
            $deck_id = $row['deck_id'];
            unset($row['deck_id']);
            $row['qty'] = intval($row['qty']);
            if (! isset($cards[$deck_id])) {
                $cards[$deck_id] = array();
            }
            $cards[$deck_id][] = $row;
        }
        
        foreach ($decks as $i => $deck) {
            $decks[$i]['cards'] = $cards[$deck['id']];
            $decks[$i]['tags'] = explode(' ', $deck['tags'] ?: '') ?: array();
            $problem = $deck['problem'];
            $decks[$i]['message'] = isset($problem) ? $this->judge->problem($problem) : '';
        }
        
        return $decks;
    
    }

    public function getById ($deck_id)
    {
        $dbh = $this->doctrine->getConnection();
        $deck = $dbh->executeQuery(
                "SELECT
				d.id,
				d.name,
				d.creation,
				d.description,
                d.tags,
				d.problem,
				c.title identity_title,
                c.code identity_code,
				f.code faction_code,
				s.name side
				from deck d
				left join card c on d.identity_id=c.id
				left join faction f on c.faction_id=f.id
				left join side s on d.side_id=s.id
				where d.id=?", array(
                        $deck_id
                ))
            ->fetch();
        
        $rows = $dbh->executeQuery(
                "SELECT
				s.deck_id,
				c.code card_code,
				s.quantity qty
				from deckslot s
				join card c on s.card_id=c.id
				join deck d on s.deck_id=d.id
				where d.id=?", array(
                        $deck_id
                ))
            ->fetchAll();
        
        $cards = array();
        foreach ($rows as $row) {
            $deck_id = $row['deck_id'];
            unset($row['deck_id']);
            $row['qty'] = intval($row['qty']);
            $cards[] = $row;
        }
        
        $deck['cards'] = $cards;
        $deck['tags'] = explode(' ', $deck['tags'] ?: '') ?: array();
        $problem = $deck['problem'];
        $deck['message'] = isset($problem) ? $this->judge->problem($problem) : '';
        
        return $deck;
    }
    

    public function save ($user, $deck, $decklist_id, $name, $description, $tags, $content)
    {
        $deck_content = array();
        
        if ($decklist_id) {
            $decklist = $this->doctrine->getRepository('NetrunnerdbBuilderBundle:Decklist')->find($decklist_id);
            if ($decklist)
                $deck->setParent($decklist);
        }
        
        $deck->setName($name);
        $deck->setDescription($description);
        $deck->setUser($user);
        if (! $deck->getCreation()) {
            $deck->setCreation(new \DateTime());
        }
        $deck->setLastupdate(new \DateTime());
        $identity = null;
        $cards = array();
        /* @var $latestPack \Netrunnerdb\CardsBundle\Entity\Pack */
        $latestPack = null;
        foreach ($content as $card_code => $qty) {
            $card = $this->doctrine->getRepository('NetrunnerdbCardsBundle:Card')->findOneBy(array(
                    "code" => $card_code
            ));
            if(!$card) continue;
            $pack = $card->getPack();
            if (! $latestPack) {
                $latestPack = $pack;
            } else 
                if ($latestPack->getCycle()->getNumber() < $pack->getCycle()->getNumber()) {
                    $latestPack = $pack;
                } else 
                    if ($latestPack->getCycle()->getNumber() == $pack->getCycle()->getNumber() && $latestPack->getNumber() < $pack->getNumber()) {
                        $latestPack = $pack;
                    }
            if ($card->getType()->getName() == "Identity") {
                $identity = $card;
            }
            $cards[$card_code] = $card;
        }
        $deck->setLastPack($latestPack);
        if ($identity) {
            $deck->setSide($identity->getSide());
            $deck->setIdentity($identity);
        } else {
            $deck->setSide(current($cards)->getSide());
            $identity = $this->doctrine->getRepository('NetrunnerdbCardsBundle:Card')->findOneBy(array(
                    "side" => $deck->getSide()
            ));
            $cards[$identity->getCode()] = $identity;
            $content[$identity->getCode()] = 1;
            $deck->setIdentity($identity);
        }
        if(empty($tags)) { 
            // tags can never be empty. if it is we put faction and side in
            $faction_code = $identity->getFaction()->getCode();
            $side_code = strtolower($identity->getSide()->getName());
            $tags = array($faction_code, $side_code);
        }
        if(is_array($tags)) {
            $tags = implode(' ', $tags);
        }
        $deck->setTags($tags);
        foreach ($content as $card_code => $qty) {
            $card = $cards[$card_code];
            if ($card->getSide()->getId() != $deck->getSide()->getId())
                continue;
            $card = $cards[$card_code];
            $slot = new Deckslot();
            $slot->setQuantity($qty);
            $slot->setCard($card);
            $slot->setDeck($deck);
            $deck->addSlot($slot);
            $deck_content[$card_code] = array(
                    'card' => $card,
                    'qty' => $qty
            );
        }
        $analyse = $this->judge->analyse($deck_content);
        if (is_string($analyse)) {
            $deck->setProblem($analyse);
        } else {
            $deck->setProblem(NULL);
            $deck->setDeckSize($analyse['deckSize']);
            $deck->setInfluenceSpent($analyse['influenceSpent']);
            $deck->setAgendaPoints($analyse['agendaPoints']);
        }
        
        $this->doctrine->persist($deck);
        $this->doctrine->flush();
        
        return $deck->getId();
    }
    
    
}