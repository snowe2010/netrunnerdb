<?php

namespace Netrunnerdb\BuilderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Netrunnerdb\BuilderBundle\Entity\Deck;
use Netrunnerdb\BuilderBundle\Entity\Deckslot;

class ApiController extends Controller
{

    public function decklistAction ($decklist_id)
    {

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(600);
        $response->headers->add(array(
                'Access-Control-Allow-Origin' => '*'
        ));
        
        $jsonp = $this->getRequest()->query->get('jsonp');
        $locale = $this->getRequest()->query->get('_locale');
        if (isset($locale))
            $this->getRequest()->setLocale($locale);
        
        $dbh = $this->get('doctrine')->getConnection();
        $rows = $dbh->executeQuery(
                "SELECT
				d.id,
				d.ts,
				d.name,
				d.creation,
				d.description,
				u.username
				from decklist d
				join user u on d.user_id=u.id
				where d.id=?
				", array(
                        $decklist_id
                ))->fetchAll();
        
        if (empty($rows)) {
            throw new NotFoundHttpException('Wrong id');
        }
        
        $decklist = $rows[0];
        $decklist['id'] = intval($decklist['id']);
        
        $lastModified = new DateTime($decklist['ts']);
        $response->setLastModified($lastModified);
        if ($response->isNotModified($this->getRequest())) {
            return $response;
        }
        unset($decklist['ts']);
        
        $cards = $dbh->executeQuery("SELECT
				c.code card_code,
				s.quantity qty
				from decklistslot s
				join card c on s.card_id=c.id
				where s.decklist_id=?
				order by c.code asc", array(
                $decklist_id
        ))->fetchAll();
        
        $decklist['cards'] = array();
        foreach ($cards as $card) {
            $decklist['cards'][$card['card_code']] = intval($card['qty']);
        }
        
        $content = json_encode($decklist);
        if (isset($jsonp)) {
            $content = "$jsonp($content)";
            $response->headers->set('Content-Type', 'application/javascript');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }
        
        $response->setContent($content);
        return $response;
    
    }

    public function decklistsAction ($date)
    {

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge(600);
        $response->headers->add(array(
                'Access-Control-Allow-Origin' => '*'
        ));
        
        $jsonp = $this->getRequest()->query->get('jsonp');
        $locale = $this->getRequest()->query->get('_locale');
        if (isset($locale))
            $this->getRequest()->setLocale($locale);
        
        $dbh = $this->get('doctrine')->getConnection();
        $decklists = $dbh->executeQuery(
                "SELECT
				d.id,
				d.ts,
				d.name,
				d.creation,
				d.description,
				u.username
				from decklist d
				join user u on d.user_id=u.id
				where substring(d.creation,1,10)=?
				", array(
                        $date
                ))->fetchAll();
        
        $lastTS = null;
        foreach ($decklists as $i => $decklist) {
            $lastTS = max($lastTS, $decklist['ts']);
            unset($decklists[$i]['ts']);
        }
        $response->setLastModified(new \DateTime($lastTS));
        if ($response->isNotModified($this->getRequest())) {
            return $response;
        }
        
        foreach ($decklists as $i => $decklist) {
            $decklists[$i]['id'] = intval($decklist['id']);
            
            $cards = $dbh->executeQuery("SELECT
				c.code card_code,
				s.quantity qty
				from decklistslot s
				join card c on s.card_id=c.id
				where s.decklist_id=?
				order by c.code asc", array(
                    $decklists[$i]['id']
            ))->fetchAll();
            
            $decklists[$i]['cards'] = array();
            foreach ($cards as $card) {
                $decklists[$i]['cards'][$card['card_code']] = intval($card['qty']);
            }
        }
        
        $content = json_encode($decklists);
        if (isset($jsonp)) {
            $content = "$jsonp($content)";
            $response->headers->set('Content-Type', 'application/javascript');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }
        
        $response->setContent($content);
        return $response;
    
    }

    public function decksAction ()
    {
        $response = new Response();
        $response->setPrivate();
        $response->headers->set('Content-Type', 'application/json');
        
        $locale = $this->getRequest()->query->get('_locale');
        if (isset($locale))
            $this->getRequest()->setLocale($locale);
        
        /* @var $user \Netrunnerdb\UserBundle\Entity\User */
        $user = $this->getUser();
        
        if (! $user) {
            throw new UnauthorizedHttpException();
        }
        
        $response->setContent(json_encode($this->get('decks')->getByUser($user)));
        return $response;
    }
 
    public function saveDeckAction($id)
    {
        $response = new Response();
        $response->setPrivate();
        $response->headers->set('Content-Type', 'application/json');

        $user = $this->getUser();
        if (count($user->getDecks()) > $user->getMaxNbDecks())
        {
            $response->setContent(json_encode(array('success' => false, 'message' => 'You have reached the maximum number of decks allowed. Delete some decks or increase your reputation.')));
            return $response;
        }
        
        $request = $this->getRequest();
        $name = filter_var($request->get('name'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $decklist_id = filter_var($request->get('decklist_id'), FILTER_SANITIZE_NUMBER_INT);
        $description = filter_var($request->get('description'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $content = json_decode($request->get('content'), true);
        if (! count($content)) 
        {
            $response->setContent(json_encode(array('success' => false, 'message' => 'Cannot import empty deck')));
            return $response;
        }
        
        $em = $this->getDoctrine()->getManager();
        
        if ($id) {
            $deck = $em->getRepository('NetrunnerdbBuilderBundle:Deck')->find($id);
            if ($user->getId() != $deck->getUser()->getId()) 
            {
                $response->setContent(json_encode(array('success' => false, 'message' => 'Wrong user')));
                return $response;
            }
            foreach ($deck->getSlots() as $slot) {
                $deck->removeSlot($slot);
                $em->remove($slot);
            }
        } else {
            $deck = new Deck();
        }
        
        // $content is formatted as {card_code,qty}, expected {card_code=>qty}
        $slots = array();
        foreach($content as $arr) {
            $slots[$arr['card_code']] = intval($arr['qty']);
        }
        
        $deck_id = $this->get('decks')->save($this->getUser(), $deck, $decklist_id, $name, $description, $slots);
        
        if(isset($deck_id)) 
        {
            $response->setContent(json_encode(array('success' => true, 'message' => $this->get('decks')->getById($deck_id))));
            return $response;
        }
        else 
        {
            $response->setContent(json_encode(array('success' => false, 'message' => 'Unknown error')));
            return $response;
        }
    }
    
}
