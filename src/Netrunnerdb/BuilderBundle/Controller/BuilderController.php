<?php

namespace Netrunnerdb\BuilderBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Netrunnerdb\BuilderBundle\Entity\Deck;
use Netrunnerdb\BuilderBundle\Entity\Deckslot;
use Netrunnerdb\CardsBundle\Entity\Card;
use Doctrine\ORM\EntityManager;

class BuilderController extends Controller
{
	public function buildformAction($side_text)
	{
		/* @var $em \Doctrine\ORM\EntityManager */
		$em = $this->get('doctrine')->getManager();

		$side = $em->getRepository('NetrunnerdbCardsBundle:Side')->findOneBy(array("name" => $side_text));
		$type = $em->getRepository('NetrunnerdbCardsBundle:Type')->findOneBy(array("name" => "Identity"));

		$identities = $em->getRepository('NetrunnerdbCardsBundle:Card')
			->findBy(array("side" => $side, "type" => $type), array("faction" => "ASC", "title" => "ASC"));

		return $this
			->render('NetrunnerdbBuilderBundle:Builder:initbuild.html.twig',
				array(
				'locales' => $this->renderView('NetrunnerdbCardsBundle:Default:langs.html.twig'),
						"identities" => array_filter($identities,
						function ($iden)
						{
							return $iden->getPack()->getCode() != "alt";
						})));
	}

	public function initbuildAction($card_code)
	{
		/* @var $em \Doctrine\ORM\EntityManager */
		$em = $this->get('doctrine')->getManager();

		$card = $em->getRepository('NetrunnerdbCardsBundle:Card')->findOneBy(array("code" => $card_code));
		if (!$card)
			return new Response('card not found.');

		$arr = array($card_code => 1);
		return $this
			->render('NetrunnerdbBuilderBundle:Builder:deck.html.twig',
				array(
					'locales' => $this->renderView('NetrunnerdbCardsBundle:Default:langs.html.twig'),
					'deck' => array('side_name' => mb_strtolower($card->getSide()->getName()),"slots" => $arr, "name" => "New " . $card->getSide()->getName() . " Deck", "description" => "", "id" => ""),
					"published_decklists"=>array()));
	}

	public function importAction()
	{
		return $this->render('NetrunnerdbBuilderBundle:Builder:directimport.html.twig', array(
				'locales' => $this->renderView('NetrunnerdbCardsBundle:Default:langs.html.twig'),
				
		));
	}

	/* obsolete */
	public function textimportAction()
	{
		$request = $this->getRequest();
		$deck_name = filter_var($request->get('name'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		$import = filter_var($request->get('import'), FILTER_UNSAFE_RAW);
		$content = $this->parseTextImport($import);
		return $this->forward('NetrunnerdbBuilderBundle:Builder:save', array('name' => $deck_name, 'content' => json_encode($content)));
	}

	public function fileimportAction()
	{
		$request = $this->getRequest();
		$filetype = filter_var($request->get('type'), FILTER_SANITIZE_STRING);
		$uploadedFile = $request->files->get('upfile');
		if (!isset($uploadedFile))
			return new Response('No file');
		$origname = $uploadedFile->getClientOriginalName();
		$origext = $uploadedFile->getClientOriginalExtension();
		$filename = $uploadedFile->getPathname();

		if (function_exists("finfo_open")) {
			// return mime type ala mimetype extension
			$finfo = finfo_open(FILEINFO_MIME);

			//check to see if the mime-type starts with 'text'
			$is_text = substr(finfo_file($finfo, $filename), 0, 4) == 'text' || substr(finfo_file($finfo, $filename), 0, 15) == "application/xml";
			if (!$is_text)
				return new Response('Bad file');
		}

		if ($filetype == "octgn" || ($filetype == "auto" && $origext == "o8d")) {
			$parse = $this->parseOctgnImport(file_get_contents($filename));
		} else {
			$parse = $this->parseTextImport(file_get_contents($filename));
		}
		return $this->forward('NetrunnerdbBuilderBundle:Builder:save', array('name' => $origname, 'content' => json_encode($parse['content']), 'description' => $parse['description']));
	}

	public function parseTextImport($text)
	{
		/* @var $em \Doctrine\ORM\EntityManager */
		$em = $this->get('doctrine')->getManager();
		
		$content = array();
		$lines = explode("\n", $text);
		$identity = null;
		foreach ($lines as $line) {
			if (preg_match('/^\s*(\d)x?([\pLl\pLu\pN\-\.\'\!\: ]+)/u', $line, $matches)) {
				$quantity = intval($matches[1]);
				$name = trim($matches[2]);
			} else if (preg_match('/^([^\(]+).*x(\d)/', $line, $matches)) {
				$quantity = intval($matches[2]);
				$name = trim($matches[1]);
			} else if (empty($identity) && preg_match('/([^\(]+):([^\(]+)/', $line, $matches)) {
				$quantity = 1;
				$name = trim($matches[1] . ":" . $matches[2]);
				$identity = $name;
			} else {
				continue;
			}
			$card = $em->getRepository('NetrunnerdbCardsBundle:Card')->findOneBy(array('title' => $name));
			if ($card) {
				$content[$card->getCode()] = $quantity;
			}
		}
		return array("content" => $content, "description" => "");
	}

	public function parseOctgnImport($octgn)
	{
		/* @var $em \Doctrine\ORM\EntityManager */
		$em = $this->get('doctrine')->getManager();
		
		$content = array();

		$crawler = new Crawler();
		$crawler->addXmlContent($octgn);
		$cardcrawler = $crawler->filter('deck > section > card');

		$content = array();
		foreach ($cardcrawler as $domElement) {
			$quantity = intval($domElement->getAttribute('qty'));
			if (preg_match('/bc0f047c-01b1-427f-a439-d451eda(\d{5})/', $domElement->getAttribute('id'), $matches)) {
				$card_code = $matches[1];
			} else {
				continue;
			}
			$card = $em->getRepository('NetrunnerdbCardsBundle:Card')->findOneBy(array('code' => $card_code));
			if ($card) {
				$content[$card->getCode()] = $quantity;
			}
		}
		
		$desccrawler = $crawler->filter('deck > notes');
		$description = array();
		foreach ($desccrawler as $domElement) {
			$description[] = $domElement->nodeValue;
		}
		return array("content" => $content, "description" => implode("\n", $description));
	}

	public function textexportAction($deck_id)
	{
		/* @var $em \Doctrine\ORM\EntityManager */
		$em = $this->get('doctrine')->getManager();
				
		/* @var $deck \Netrunnerdb\BuilderBundle\Entity\Deck */
		$deck = $em->getRepository('NetrunnerdbBuilderBundle:Deck')->find($deck_id);
		if (!$this->getUser() || $this->getUser()->getId() != $deck->getUser()->getId())
			throw new UnauthorizedHttpException("You don't have access to this deck.");
		
		/* @var $judge \Netrunnerdb\SocialBundle\Services\Judge */
		$judge = $this->get('judge');
		$classement = $judge->classe($deck->getCards(), $deck->getIdentity());
		
		$lines = array();
		$types = array("Event", "Hardware", "Resource", "Icebreaker", "Program", "Agenda", "Asset", "Upgrade", "Operation", "Barrier", "Code Gate", "Sentry", "ICE");
		
		$lines[] = $deck->getIdentity()->getTitle(). " (" . $deck->getIdentity()->getPack()->getName(). ")";
		foreach($types as $type) {
			if(isset($classement[$type]) && $classement[$type]['qty']) {
				$lines[] = "";
				$lines[] = $type." (".$classement[$type]['qty'].")";
				foreach($classement[$type]['slots'] as $slot) {
					$inf = "";
					for($i=0; $i<$slot['influence']; $i++) {
						if($i % 5 == 0) $inf .= " ";
						$inf .= "•";
					}
					$lines[] = $slot['qty'] . "x " . $slot['card']->getTitle(). " (" . $slot['card']->getPack()->getName(). ") ".$inf;
				}
			}
		}
		$content = implode("\r\n", $lines);
		
		$name = mb_strtolower($deck->getName());
		$name = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $name);
		$name = preg_replace('/--+/', '-', $name);
		
		$response = new Response();
		
		$response->headers->set('Content-Type', 'text/plain');
		$response->headers->set('Content-Disposition', 'attachment;filename=' . $name . ".txt");
		
		$response->setContent($content);
		return $response;
	}
	
	public function octgnexportAction($deck_id)
	{
		/* @var $em \Doctrine\ORM\EntityManager */
		$em = $this->get('doctrine')->getManager();
		
		/* @var $deck \Netrunnerdb\BuilderBundle\Entity\Deck */
		$deck = $em->getRepository('NetrunnerdbBuilderBundle:Deck')->find($deck_id);
		if (!$this->getUser() || $this->getUser()->getId() != $deck->getUser()->getId())
			throw new UnauthorizedHttpException("You don't have access to this deck.");

		$rd = array();
		$identity = null;
		/** @var $slot Deckslot */
		foreach ($deck->getSlots() as $slot) {
			if ($slot->getCard()->getType()->getName() == "Identity") {
				$identity = array("index" => $slot->getCard()->getCode(), "name" => $slot->getCard()->getTitle());
			} else {
				$rd[] = array("index" => $slot->getCard()->getCode(), "name" => $slot->getCard()->getTitle(), "qty" => $slot->getQuantity());
			}
		}
		$name = mb_strtolower($deck->getName());
		$name = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $name);
		$name = preg_replace('/--+/', '-', $name);
		if (empty($identity)) {
			return new Response('no identity found');
		}
		return $this->octgnexport("$name.o8d", $identity, $rd, $deck->getDescription());
	}

	public function octgnexport($filename, $identity, $rd, $description)
	{
		$content = $this->renderView('NetrunnerdbBuilderBundle::octgn.xml.twig', array("identity" => $identity, "rd" => $rd, "description" => strip_tags($description)));

		$response = new Response();

		$response->headers->set('Content-Type', 'application/octgn');
		$response->headers->set('Content-Disposition', 'attachment;filename=' . $filename);

		$response->setContent($content);
		return $response;
	}

	public function saveAction()
	{
		/* @var $em \Doctrine\ORM\EntityManager */
		$em = $this->get('doctrine')->getManager();
		
		$user = $this->getUser();
		if(count($user->getDecks()) > $user->getMaxNbDecks())
			return new Response('You have reached the maximum number of decks allowed. Delete some decks or increase your reputation.');
		
		$judge = $this->get('judge');
		$request = $this->getRequest();
		$is_copy = (boolean) filter_var($request->get('copy'), FILTER_SANITIZE_NUMBER_INT);
		$name = filter_var($request->get('name'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		$id = filter_var($request->get('id'), FILTER_SANITIZE_NUMBER_INT);
		$decklist_id = filter_var($request->get('decklist_id'), FILTER_SANITIZE_NUMBER_INT);
		$description = filter_var($request->get('description'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		$content = (array) json_decode($request->get('content'));
		if (!count($content))
			return new Response('Cannot import empty deck');

		$deck_content = array();

		if($is_copy && $id) {
			$id = null;
		}
		
		if ($id) {
			$deck = $em->getRepository('NetrunnerdbBuilderBundle:Deck')->find($id);
			if ($user->getId() != $deck->getUser()->getId())
				throw new UnauthorizedHttpException("You don't have access to this deck.");
			foreach ($deck->getSlots() as $slot) {
				$deck->removeSlot($slot);
				$em->remove($slot);
			}
		} else {
			$deck = new Deck;
		}
		if($decklist_id) {
			$decklist = $em->getRepository('NetrunnerdbBuilderBundle:Decklist')->find($decklist_id);
			if($decklist) $deck->setParent($decklist);
		}
		$deck->setName($name);
		$deck->setDescription($description);
		$deck->setUser($user);
		if (!$id) {
			$deck->setCreation(new \DateTime());
		}
		$deck->setLastupdate(new \DateTime());
		$identity = null;
		$cards = array();
		/* @var $latestPack \Netrunnerdb\CardsBundle\Entity\Pack */
		$latestPack = null;
		foreach ($content as $card_code => $qty) {
			$card = $em->getRepository('NetrunnerdbCardsBundle:Card')->findOneBy(array("code" => $card_code));
			$pack = $card->getPack();
			if (!$latestPack) {
				$latestPack = $pack;
			} else if ($latestPack->getCycle()->getNumber() < $pack->getCycle()->getNumber()) {
				$latestPack = $pack;
			} else if($latestPack->getCycle()->getNumber() == $pack->getCycle()->getNumber() && $latestPack->getNumber() < $pack->getNumber()) {
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
			$identity = $em->getRepository('NetrunnerdbCardsBundle:Card')->findOneBy(array("side" => $deck->getSide()));
			$cards[$identity->getCode()] = $identity;
			$content[$identity->getCode()] = 1;
			$deck->setIdentity($identity);
		}
		foreach ($content as $card_code => $qty) {
			$card = $cards[$card_code];
			if ($card->getSide()->getId() != $deck->getSide()->getId())
				continue;
			$card = $cards[$card_code];
			$slot = new Deckslot;
			$slot->setQuantity($qty);
			$slot->setCard($card);
			$slot->setDeck($deck);
			$deck->addSlot($slot);
			$deck_content[$card_code] = array('card' => $card, 'qty' => $qty);
		}
		$analyse = $judge->analyse($deck_content);
		if (is_string($analyse)) {
			$deck->setProblem($analyse);
		} else {
			$deck->setProblem(NULL);
			$deck->setDeckSize($analyse['deckSize']);
			$deck->setInfluenceSpent($analyse['influenceSpent']);
			$deck->setAgendaPoints($analyse['agendaPoints']);
		}

		if (!$id)
			$em->persist($deck);
		
		$em->flush();
		
		return $this->redirect($this->generateUrl('decks_list'));

	}

	public function deleteAction()
	{
		/* @var $em \Doctrine\ORM\EntityManager */
		$em = $this->get('doctrine')->getManager();
		
		$request = $this->getRequest();
		$deck_id = filter_var($request->get('deck_id'), FILTER_SANITIZE_NUMBER_INT);
		$deck = $em->getRepository('NetrunnerdbBuilderBundle:Deck')->find($deck_id);
		if (!$deck)
			return $this->redirect($this->generateUrl('decks_list'));
		if ($this->getUser()->getId() != $deck->getUser()->getId())
			throw new UnauthorizedHttpException("You don't have access to this deck.");

		foreach($deck->getChildren() as $decklist) {
			$decklist->setParent(null);
		}
		$em->remove($deck);
		$em->flush();

		return $this->redirect($this->generateUrl('decks_list'));
	}

	public function editAction($deck_id)
	{
		$dbh = $this->get('doctrine')->getConnection();
		$rows = $dbh
		->executeQuery(
				"SELECT
				d.id,
				d.name,
				d.description,
				s.name side_name
				from deck d
				left join side s on d.side_id=s.id
				where d.id=?
				", array($deck_id))->fetchAll();
		
		$deck = $rows[0];
		$deck['side_name'] = mb_strtolower($deck['side_name']);
		
		$rows = $dbh
		->executeQuery(
				"SELECT
				c.code,
				s.quantity
				from deckslot s
				join card c on s.card_id=c.id
				where s.deck_id=?", array($deck_id))->fetchAll();
		
		$cards = array();
		foreach($rows as $row) {
			$cards[$row['code']] = $row['quantity'];
		}
		$deck['slots'] = $cards;

		$published_decklists = $dbh->executeQuery(
				"SELECT
					d.id,
					d.name,
					d.prettyname,
					d.nbvotes,
					d.nbfavorites,
					d.nbcomments
					from decklist d
					where d.parent_deck_id=?
					order by d.creation asc", array($deck_id))->fetchAll();

		return $this
			->render('NetrunnerdbBuilderBundle:Builder:deck.html.twig',
				array(
						'locales' => $this->renderView('NetrunnerdbCardsBundle:Default:langs.html.twig'),
						'deck' => $deck,
						'published_decklists' => $published_decklists));
	}

	public function listAction()
	{
		/* @var $judge \Netrunnerdb\SocialBundle\Services\Judge */
		$judge = $this->get('judge');

		/* @var $user \Netrunnerdb\UserBundle\Entity\User */
		$user = $this->getUser();

		$dbh = $this->get('doctrine')->getConnection();
		$decks = $dbh
		->executeQuery(
				"SELECT
				d.id,
				d.name,
				d.creation,
				d.description,
				d.problem,
				c.title identity_title,
				f.code faction_code,
				s.name side 
				from deck d
				left join card c on d.identity_id=c.id
				left join faction f on c.faction_id=f.id
				left join side s on d.side_id=s.id
				where d.user_id=?
				order by lastupdate desc", array($user->getId()))->fetchAll();
		
		$rows = $dbh
		->executeQuery(
				"SELECT
				s.deck_id,
				c.code card_code,
				s.quantity qty
				from deckslot s
				join card c on s.card_id=c.id
				join deck d on s.deck_id=d.id
				where d.user_id=?", array($user->getId()))->fetchAll();
		
		$cards = array();
		foreach($rows as $row) {
			if(!isset($cards[$row['deck_id']])) $cards[$row['deck_id']] = array();
			$cards[$row['deck_id']][] = $row;
		}
		
		foreach($decks as $i => $deck) {
			$decks[$i]['cards'] = $cards[$deck['id']];
			$problem = $deck['problem'];
			$decks[$i]['message'] = isset($problem) ? $judge->problem($problem) : '';
		}
		
		return $this->render('NetrunnerdbBuilderBundle:Builder:decks.html.twig', array(
								'locales' => $this->renderView('NetrunnerdbCardsBundle:Default:langs.html.twig'),
				'decks' => $decks, 'nbmax' => $user->getMaxNbDecks()));
	}
	
	public function copyAction($decklist_id)
	{
		/* @var $em \Doctrine\ORM\EntityManager */
		$em = $this->get('doctrine')->getManager();
				
		/* @var $decklist \Netrunnerdb\BuilderBundle\Entity\Decklist */
		$decklist = $em->getRepository('NetrunnerdbBuilderBundle:Decklist')->find($decklist_id);
		
		$content = array();
		foreach($decklist->getSlots() as $slot) {
			$content[$slot->getCard()->getCode()] = $slot->getQuantity();
		}
		return $this->forward('NetrunnerdbBuilderBundle:Builder:save', array('name' => $decklist->getName(), 'content' => json_encode($content), 'decklist_id' => $decklist_id));
	}
}
