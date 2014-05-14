<?php


namespace Netrunnerdb\SocialBundle\Services;


/*
 * 
 */
class Judge 
{
	public function __construct($doctrine) {
		$this->doctrine = $doctrine;
	}
	
	/**
	 * Decoupe un deckcontent pour son affichage par type
	 * 
	 * @param \Netrunnerdb\CardsBundle\Entity\Card $identity
	 */
	public function classe($cards, $identity)
	{
		$analyse = $this->analyse($cards);
		
		$classeur = array();
		/* @var $slot \Netrunnerdb\BuilderBundle\Entity\Deckslot */
		foreach($cards as $elt) {
			/* @var $card \Netrunnerdb\CardsBundle\Entity\Card */
			$card = $elt['card'];
			$qty = $elt['qty'];
			$type = $card->getType()->getName();
			if($type == "Identity") continue;
			if($type == "ICE") {
				$keywords = explode(" - ", $card->getKeywords());
				if(in_array("Barrier", $keywords)) $type = "Barrier";
				if(in_array("Code Gate", $keywords)) $type = "Code Gate";
				if(in_array("Sentry", $keywords)) $type = "Sentry";
			}
			if($type == "Program") {
				$keywords = explode(" - ", $card->getKeywords());
				if(in_array("Icebreaker", $keywords)) $type = "Icebreaker";
			}
			$influence = 0;
			$qty_influence = $qty;
			if($identity->getCode() == "03029" && $card->getType()->getName() == "Program") $qty_influence--;
			if($card->getFaction()->getId() != $identity->getFaction()->getId()) $influence = $card->getFactionCost() * $qty_influence;
			$elt['influence'] = $influence;
			$elt['faction'] = str_replace(' ', '-', mb_strtolower($card->getFaction()->getName()));
			
			if(!isset($classeur[$type])) $classeur[$type] = array("qty" => 0, "slots" => array());
			$classeur[$type]["slots"][] = $elt;
			$classeur[$type]["qty"] += $qty;
		}
		if(is_string($analyse)) {
			$classeur['problem'] = $this->problem($analyse);
		} else {
			$classeur = array_merge($classeur, $analyse);
		}
		return $classeur;
	}
	
    /**
     * Analyse un deckcontent et renvoie un code indiquant le pbl du deck
     *
     * @param array $content
     * @return array
     */
	public function analyse($cards)
	{
		$identity = null;
		$deck = array();
		$deckSize = 0;
		$influenceSpent = 0;
		$agendaPoints = 0;
		
		foreach($cards as $elt) {
			$card = $elt['card'];
			$qty = $elt['qty'];
			if($card->getType()->getName() == "Identity") {
				$identity = $card;
			} else {
				$deck[] = $card;
				$deckSize += $qty;
			}
		}
		
		if(!isset($identity)) {
			return 'identity';
		}
		
		if($deckSize < $identity->getMinimumDeckSize()) {
			return 'deckSize';
		}
		
		foreach($deck as $card) {
			$qty = $cards[$card->getCode()]['qty'];
			
			if($card->getSide() != $identity->getSide()) {
				return 'side';
			}
			if($identity->getCode() == "03002" && $card->getFaction()->getName() == "Jinteki") {
				return 'forbidden';
			}
			if($card->getType()->getName() == "Agenda") {
				if($card->getFaction()->getName() != "Neutral" && $card->getFaction() != $identity->getFaction() && $identity->getFaction()->getName() != "Neutral") {
					return 'agendas';
				}
				$agendaPoints += $card->getAgendaPoints() * $qty;
			}
			if($card->getFaction() != $identity->getFaction()) {
				if($identity->getCode() == "03029" && $card->getType()->getName() == "Program") {
					$influenceSpent += $card->getFactionCost() * ($qty - 1);
				} else {
					$influenceSpent += $card->getFactionCost() * $qty;
				}
			}
		}
		
		if($identity->getInfluenceLimit() !== null && $influenceSpent > $identity->getInfluenceLimit()) return 'influence';
		if($identity->getSide()->getName() == "Corp" && $identity->getFaction()->getName() != "Neutral") {
			$minAgendaPoints = floor($deckSize / 5) * 2 + 2;
			if($agendaPoints < $minAgendaPoints || $agendaPoints > $minAgendaPoints + 1) return 'agendapoints';
		}
		
		return array(
			'deckSize' => $deckSize,
			'influenceSpent' => $influenceSpent,
			'agendaPoints' => $agendaPoints
		);
	}
	
	public function problem($problem)
	{
		switch($problem) {
			case 'identity': return "The deck lacks an Identity card.";
			case 'deckSize': return "The deck has less cards than the minimum required by the Identity.";
			case 'side': return "The deck mixes Corp and Runner cards.";
			case 'forbidden': return "The deck includes forbidden cards.";
			case 'agendas': return "The deck uses Agendas from a different faction.";
			case 'influence': return "The deck spends more influence than available on the Identity.";
			case 'agendapoints': return "The deck has a wrong number of Agenda Points.";
		}
	}
	
}