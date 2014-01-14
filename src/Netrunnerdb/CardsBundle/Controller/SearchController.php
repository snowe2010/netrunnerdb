<?php

namespace Netrunnerdb\CardsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Netrunnerdb\CardsBundle\Controller\DefaultController;

class SearchController extends Controller
{
	private function getCardAlternatives($card)
	{
		$qb = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Card')->createQueryBuilder('c');
		$qb->andWhere("c.title = ?1")->setParameter(1, $card->getTitle());
		$qb->andWhere("c.code != ?2")->setParameter(2, $card->getCode());
		$qb->orderBy('c.code');
		$query = $qb->getQuery();
		$rows = $query->getResult();
		$alternatives = array();
		foreach($rows as $alt)
		{
			$alternatives[] = array(
				"setname" => $alt->getPack()->getName($this->getRequest()->getLocale()),
				"set_code" => $alt->getPack()->getCode(),
				"number" => $alt->getNumber(),
				"code" => $alt->getCode(),
				"url" => $this->get('router')->generate('netrunnerdb_netrunner_cards_zoom', array('card_code' => $alt->getCode()), true),
			);
		}
		return $alternatives;
	}
	
	private function getCardInfo($card, $api = false)
	{
		$locale = $this->getRequest()->getLocale();
		$cardinfo = array(
				"id" => $card->getId(),
				"last-modified" => $card->getTs()->format('c'),
				"code" => $card->getCode(),
				"title" => $card->getTitle($locale),
				"type" => $card->getType()->getName($locale),
				"type_code" => mb_strtolower($card->getType()->getName()),
				"subtype" => $card->getKeywords($locale),
				"subtype_code" => mb_strtolower($card->getKeywords()),
				"text" => $card->getText($locale),
				"advancementcost" => $card->getAdvancementCost(),
				"agendapoints" => $card->getAgendaPoints(),
				"baselink" => $card->getBaseLink(),
				"cost" => $card->getCost(),
				"faction" => $card->getFaction()->getName($locale),
				"faction_code" => $card->getFaction()->getCode(),
				"factioncost" => $card->getFactionCost(),
				"flavor" => $card->getFlavor($locale),
				"illustrator" => $card->getIllustrator(),
				"influencelimit" => $card->getInfluenceLimit(),
				"memoryunits" => $card->getMemoryUnits(),
				"minimumdecksize" => $card->getMinimumDeckSize(),
				"number" => $card->getNumber(),
				"quantity" => $card->getQuantity(),
				"id_set" => $card->getPack()->getId(),
				"setname" => $card->getPack()->getName($locale),
				"set_code" => $card->getPack()->getCode(),
				"side" => $card->getSide()->getName($locale),
				"side_code" => mb_strtolower($card->getSide()->getName()),
				"strength" => $card->getStrength(),
				"trash" => $card->getTrashCost(),
				"uniqueness" => $card->getUniqueness(),
		);
		$cardinfo['url'] = $this->get('router')->generate('netrunnerdb_netrunner_cards_zoom', array('card_code' => $card->getCode(), '_locale' => $locale), true);
		if(file_exists(__DIR__."/../Resources/public/images/cards/$locale/".$card->getCode() . ".png")) {
			$cardinfo['imagesrc'] = "/web/bundles/netrunnerdbcards/images/cards/$locale/". $card->getCode() . ".png";
		} else {
			$cardinfo['imagesrc'] = "/web/bundles/netrunnerdbcards/images/cards/en/". $card->getCode() . ".png";
		}
		if($api) {
			unset($cardinfo['id']);
			unset($cardinfo['id_set']);
			$cardinfo = array_filter($cardinfo, function ($var) { return isset($var); });
		} else {
			$cardinfo['cssfaction'] = str_replace(" ", "-", mb_strtolower($card->getFaction()->getName()));
		}
		return $cardinfo;
	}

	public function zoomAction($card_code)
	{
		$request  = $this->getRequest();
		$mode = $request->query->get('mode');
		$card = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Card')->findOneBy(array("code" => $card_code));
		if(!$card) throw $this->createNotFoundException('Sorry, this card is not in the database (yet?)');
		$meta = $card->getTitle().", a ".$card->getFaction()->getName()." ".$card->getType()->getName()." card for Android:Netrunner from the set ".$card->getPack()->getName()." published by Fantasy Flight Games.";
		return $this->forward(
			'NetrunnerdbCardsBundle:Search:display', 
			array(
				'q' => $card->getCode(), 
				'view' => 'card', 
				'sort' => 'set',
				'title' => $card->getTitle(),
				'mode' => $mode,
				'meta' => $meta,
				'locale' => $this->getRequest()->getLocale(),
				'locales' => $this->renderView('NetrunnerdbCardsBundle:Default:langs.html.twig'),
			)
		);
	}
	
	public function listAction($pack_code)
	{
		$request  = $this->getRequest();
		$mode = $request->query->get('mode');
		$pack = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Pack')->findOneBy(array("code" => $pack_code));
		if(!$pack) throw $this->createNotFoundException('This pack does not exist');
		$meta = $pack->getName($this->getRequest()->getLocale()).", a set of cards for Android:Netrunner"
				.($pack->getReleased() ? " published on ".$pack->getReleased()->format('Y/m/d') : "")
				." by Fantasy Flight Games.";
		return $this->forward(
			'NetrunnerdbCardsBundle:Search:display', 
			array(
				'q' => 'e:'.$pack_code,
				'view' => 'list',
				'sort' => 'set',
				'title' => $pack->getName($this->getRequest()->getLocale()),
				'mode' => $mode,
				'meta' => $meta,
				'locale' => $this->getRequest()->getLocale(),
				'locales' => $this->renderView('NetrunnerdbCardsBundle:Default:langs.html.twig'),
			)
		);
	}

	public function cycleAction($cycle_code)
	{
		$request  = $this->getRequest();
		$mode = $request->query->get('mode');
		$cycle = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Cycle')->findOneBy(array("code" => $cycle_code));
		if(!$cycle) throw $this->createNotFoundException('This cycle does not exist');
		$meta = $cycle->getName($this->getRequest()->getLocale()).", a cycle of datapack for Android:Netrunner published by Fantasy Flight Games.";
		return $this->forward(
			'NetrunnerdbCardsBundle:Search:display',
			array(
				'q' => 'c:'.$cycle->getNumber(),
				'view' => 'list',
				'sort' => 'faction',
				'title' => $cycle->getName($this->getRequest()->getLocale()),
				'mode' => $mode,
				'meta' => $meta,
				'locale' => $this->getRequest()->getLocale(),
				'locales' => $this->renderView('NetrunnerdbCardsBundle:Default:langs.html.twig'),
			)
		);
	}
	
	public function processAction()
	{
		$request  = $this->getRequest();
		$view = $request->query->get('view') ?: 'list';
		$sort = $request->query->get('sort') ?: 'name';
		$locale = $request->query->get('_locale') ?: $this->getRequest()->getLocale();
		
		$operators = array(":","!","<",">");
		
		$params = array();
		if($request->query->get('q') != "") {
			$params[] = $request->query->get('q');
		}
		$keys = array("e","t","f","s","x","p","o","n","d","r","i","l","y","a");
		foreach($keys as $key) {
			$val = $request->query->get($key);
			if(isset($val) && $val != "") {
				if(is_array($val)) {
					if($key == "f" && count($val) == 8) continue;
					$params[] = $key.":".implode("|", array_map(function ($s) { return strstr($s, " ") !== FALSE ? "\"$s\"" : $s; }, $val));
				} else {
					if(strstr($val, " ") != FALSE) {
						$val = "\"$val\"";
					}
					$op = $request->query->get($key."o");
					if(!in_array($op, $operators)) {
						$op = ":";
					}
					if($key == "r") {
						$op = "";
					}
					$params[] = "$key$op$val";
				}
			}
		}
		$find = array('q' => implode(" ",$params));
		if($sort != "name") $find['sort'] = $sort;
		if($view != "list") $find['view'] = $view;
		if($locale != "en") $find['_locale'] = $locale;
		return $this->redirect($this->generateUrl('netrunnerdb_netrunner_cards_find').'?'.http_build_query($find));
	}

	public function findAction()
	{
		$request  = $this->getRequest();
		$q = $request->query->get('q');
		$page = $request->query->get('page') ?: 1;
		$view = $request->query->get('view') ?: 'list';
		$sort = $request->query->get('sort') ?: 'name';
		$locale = $request->query->get('_locale') ?: 'en';
		$mode = $request->query->get('mode');
		
		$request->setLocale($locale);

		return $this->forward(
			'NetrunnerdbCardsBundle:Search:display', 
			array(
				'q' => $q, 
				'view' => $view, 
				'sort' => $sort,
				'page' => $page, 
				'mode' => $mode,
				'locale' => $locale,
				'locales' => $this->renderView('NetrunnerdbCardsBundle:Default:langs.html.twig'),
			)
		);
	}
	
	public function syntax($query)
	{
		// renvoie une liste de conditions (array)
		// chaque condition est un tableau à n>1 éléments
		// le premier est le type de condition (0 ou 1 caractère)
		// les suivants sont les arguments, en OR
		
		$query = preg_replace('/\s+/u', ' ', trim($query));

		$list = array();
		$cond;
		// l'automate a 3 états : 
		// 1:recherche de type
		// 2:recherche d'argument principal
		// 3:recherche d'argument supplémentaire
		// 4:erreur de parsing, on recherche la prochaine condition
		// s'il tombe sur un argument alors qu'il est en recherche de type, alors le type est vide
		$etat = 1;
		while($query != "") {
			if($etat == 1) {
				if(isset($cond) && $etat != 4 && count($cond)>2) {
					$list[] = $cond;
				}
				// on commence par rechercher un type de condition
				if(preg_match('/^(\p{L})([:<>!])(.*)/u', $query, $match)) { // jeton "condition:"
					$cond = array(mb_strtolower($match[1]), $match[2]);
					$query = $match[3];
				} else {
					$cond = array("", ":");
				}
				$etat=2;
			} else {
				if( preg_match('/^"([^"]*)"(.*)/u', $query, $match) // jeton "texte libre entre guillements"
				 || preg_match('/^([\p{L}\p{N}\-]+)(.*)/u', $query, $match) // jeton "texte autorisé sans guillements"
				) {
					if(($etat == 2 && count($cond)==2) || $etat == 3) {
						$cond[] = $match[1];
						$query = $match[2];
						$etat = 2;
					} else {
						// erreur
						$query = $match[2];
						$etat = 4;
					}
				} else if( preg_match('/^\|(.*)/u', $query, $match) ) { // jeton "|"
					if(($cond[1] == ':' || $cond[1] == '!') && (($etat == 2 && count($cond)>2) || $etat == 3)) {
						$query = $match[1];
						$etat = 3;
					} else {
						// erreur
						$query = $match[1];
						$etat = 4;
					}
				} else if( preg_match('/^ (.*)/u', $query, $match) ) { // jeton " "
					$query = $match[1];
					$etat=1;
				} else {
					// erreur
					$query = substr($query, 1);
					$etat = 4;
				}
			}
		}
		if(isset($cond) && $etat != 4 && count($cond)>2) {
			$list[] = $cond;
		}
		return $list;
	}
	
	private function get_search_rows($conditions, $sortorder)
	{
		$i=0;
		$faction_codes = array(
			'h' => "Haas-Bioroid",
			'w' => "Weyland Consortium",
			'a' => "Anarch",
			's' => "Shaper",
			'c' => "Criminal",
			'j' => "Jinteki",
			'n' => "NBN",
			'-' => "Neutral",
		);
		$side_codes = array(
			'r' => 'Runner',
			'c' => 'Corp',
		);
	
		// construction de la requete sql
		$qb = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Card')->createQueryBuilder('c');
		$qb->leftJoin('c.pack', 'p')
			->leftJoin('p.cycle', 'y')
			->leftJoin('c.type', 't')
			->leftJoin('c.faction', 'f')
			->leftJoin('c.side', 's');

		foreach($conditions as $condition)
		{
			$type = array_shift($condition);
			$operator = array_shift($condition);
			switch($type)
			{
				case '': // title or index
					$or = array();
					foreach($condition as $arg) {
						$code = preg_match('/^\d\d\d\d\d$/u', $arg);
						$acronym = preg_match('/^[A-Z]{2,}$/', $arg);
						if($code) {
							$or[] = "(c.code = ?$i)";
							$qb->setParameter($i++, $arg);
						} else if($acronym) {
							$or[] = "(BINARY(c.title) like ?$i)";
							$qb->setParameter($i++, "%$arg%");
							$like = implode('% ', str_split($arg));
							$or[] = "(REPLACE(c.title, '-', ' ') like ?$i)";
							$qb->setParameter($i++, "$like%");
						} else {
							$or[] = "(c.title like ?$i)";
							$qb->setParameter($i++, "%$arg%");
						}
					}
					$qb->andWhere(implode(" or ", $or));
					break;
				case 'x': // text
					$or = array();
					foreach($condition as $arg) {
						switch($operator) {
							case ':': $or[] = "(c.text like ?$i)"; break;
							case '!': $or[] = "(c.text not like ?$i)"; break;
						}
						$qb->setParameter($i++, "%$arg%");
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 'a': // flavor
					$or = array();
					foreach($condition as $arg) {
						switch($operator) {
							case ':': $or[] = "(c.flavor like ?$i)"; break;
							case '!': $or[] = "(c.flavor not like ?$i)"; break;
						}
						$qb->setParameter($i++, "%$arg%");
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 'e': // extension (pack)
					$or = array();
					foreach($condition as $arg) {
						switch($operator) {
							case ':': $or[] = "(p.code = ?$i)"; break;
							case '!': $or[] = "(p.code != ?$i)"; break;
						}
						$qb->setParameter($i++, $arg);
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 'c': // cycle (cycle)
					$or = array();
					foreach($condition as $arg) {
						switch($operator) {
							case ':': $or[] = "(y.number = ?$i)"; break;
							case '!': $or[] = "(y.number != ?$i)"; break;
						}
						$qb->setParameter($i++, $arg);
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 't': // type
					$or = array();
					foreach($condition as $arg) {
						switch($operator) {
							case ':': $or[] = "(t.name = ?$i)"; break;
							case '!': $or[] = "(t.name != ?$i)"; break;
						}
						$qb->setParameter($i++, $arg);
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 'f': // faction
					$or = array();
					foreach($condition as $arg) {
						if(array_key_exists($arg, $faction_codes)) {
							switch($operator) {
								case ':': $or[] = "(f.name = ?$i)"; break;
								case '!': $or[] = "(f.name != ?$i)"; break;
							}
							$qb->setParameter($i++, $faction_codes[$arg]);
						}
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 's': // subtype (keywords)
					$or = array();
					foreach($condition as $arg) {
						switch($operator) {
							case ':': 
								$or[] = "((c.keywords = ?$i) or (c.keywords like ?".($i+1).") or (c.keywords like ?".($i+2).") or (c.keywords like ?".($i+3)."))"; 
								$qb->setParameter($i++, "$arg");
								$qb->setParameter($i++, "$arg %");
								$qb->setParameter($i++, "% $arg");
								$qb->setParameter($i++, "% $arg %");
								break;
							case '!': 
								$or[] = "(c.keywords is null or ((c.keywords != ?$i) and (c.keywords not like ?".($i+1).") and (c.keywords not like ?".($i+2).") and (c.keywords not like ?".($i+3).")))"; 
								$qb->setParameter($i++, "$arg");
								$qb->setParameter($i++, "$arg %");
								$qb->setParameter($i++, "% $arg");
								$qb->setParameter($i++, "% $arg %");
								break;
						}
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 'd': // side
					$or = array();
					foreach($condition as $arg) {
						if(array_key_exists($arg, $side_codes)) {
							switch($operator) {
								case ':': $or[] = "(s.name = ?$i)"; break;
								case '!': $or[] = "(s.name != ?$i)"; break;
							}
							$qb->setParameter($i++, $side_codes[$arg]);
						}
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 'i': // illustrator
					$or = array();
					foreach($condition as $arg) {
						switch($operator) {
							case ':': $or[] = "(c.illustrator = ?$i)"; break;
							case '!': $or[] = "(c.illustrator != ?$i)"; break;
						}
						$qb->setParameter($i++, $arg);
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 'o': // cost or advancementcost
					$or = array();
					foreach($condition as $arg) {
						switch($operator) {
							case ':': $or[] = "(c.cost = ?$i or c.advancementCost = ?$i)"; break;
							case '!': $or[] = "(c.cost != ?$i or c.advancementCost != ?$i)"; break;
							case '<': $or[] = "(c.cost < ?$i or c.advancementCost < ?$i)"; break;
							case '>': $or[] = "(c.cost > ?$i or c.advancementCost > ?$i)"; break;
						}
						$qb->setParameter($i++, $arg);
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 'n': // influence
					$or = array();
					foreach($condition as $arg) {
						switch($operator) {
							case ':': $or[] = "(c.factionCost = ?$i)"; break;
							case '!': $or[] = "(c.factionCost != ?$i)"; break;
							case '<': $or[] = "(c.factionCost < ?$i)"; break;
							case '>': $or[] = "(c.factionCost > ?$i)"; break;
						}
						$qb->setParameter($i++, $arg);
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 'p': // power (strength) or agendapoints or trashcost
					$or = array();
					foreach($condition as $arg) {
						switch($operator) {
							case ':': $or[] = "(c.strength = ?$i or c.agendaPoints = ?$i or c.trashCost = ?$i)"; break;
							case '!': $or[] = "(c.strength != ?$i or c.agendaPoints != ?$i or c.trashCost != ?$i)"; break;
							case '<': $or[] = "(c.strength < ?$i or c.agendaPoints < ?$i or c.trashCost < ?$i)"; break;
							case '>': $or[] = "(c.strength > ?$i or c.agendaPoints > ?$i or c.trashCost > ?$i)"; break;
						}
						$qb->setParameter($i++, $arg);
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 'y': // quantity
					$or = array();
					foreach($condition as $arg) {
						switch($operator) {
							case ':': $or[] = "(c.quantity = ?$i)"; break;
							case '!': $or[] = "(c.quantity != ?$i)"; break;
							case '<': $or[] = "(c.quantity < ?$i)"; break;
							case '>': $or[] = "(c.quantity > ?$i)"; break;
						}
						$qb->setParameter($i++, $arg);
					}
					$qb->andWhere(implode($operator == '!' ? " and " : " or ", $or));
					break;
				case 'r': // release
					$or = array();
					foreach($condition as $arg) {
						switch($operator) {
							case '<': $or[] = "(p.released <= ?$i)"; break;
							case '>': $or[] = "(p.released > ?$i or p.released IS NULL)"; break;
						}
						if($arg == "now") $qb->setParameter($i++, new \DateTime());
						else $qb->setParameter($i++, new \DateTime($arg));
					}
					$qb->andWhere(implode(" or ", $or));
					break;
			}
		}
		
		if(!$i) {
			return;
		}
		switch($sortorder) {
			case 'set': $qb->orderBy('c.code'); break;
			case 'faction': $qb->orderBy('c.side', 'DESC')->addOrderBy('c.faction')->addOrderBy('c.type'); break;
			case 'type': $qb->orderBy('c.side', 'DESC')->addOrderBy('c.type')->addOrderBy('c.faction'); break;
			case 'cost': $qb->orderBy('c.type')->addOrderBy('c.cost')->addOrderBy('c.advancementCost'); break;
			case 'strength': $qb->orderBy('c.type')->addOrderBy('c.strength')->addOrderBy('c.agendaPoints')->addOrderBy('c.trashCost'); break;
		}
		$qb->addOrderBy('c.title');
		$qb->addOrderBy('c.code');
		$query = $qb->getQuery();
		$rows = $query->getResult();
		
		for($i=0; $i<count($rows); $i++)
		{
			while(isset($rows[$i+1]) && $rows[$i]->getTitle() == $rows[$i+1]->getTitle())
			{
				$rows[$i] = $rows[$i+1];
				array_splice($rows, $i+1, 1);
			}
		}
		
		return $rows;
	}
	
	private function replaceSymbols($text)
	{
		$symbols = array("Subroutine", "Credits", "Trash", "Click", "Recurring Credits", "Memory Unit", "Link", "Unique");
		foreach($symbols as $symbol)
		{
			$text = str_replace("[$symbol]", '<span class="sprite '.mb_strtolower(str_replace(' ','_',$symbol)).'"></span>', $text);
		}
		return $text;
	}
	
	private function findATitle($conditions) {
		$title = "";
		if(count($conditions) == 1 && $conditions[0][1] == ":") {
			if($conditions[0][0] == "e") {
				$pack = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Pack')->findOneBy(array("code" => $conditions[0][2]));
				if($pack) $title = $pack->getName($this->getRequest()->getLocale());
			}
			if($conditions[0][0] == "c") {
				$cycle = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Cycle')->findOneBy(array("code" => $conditions[0][2]));
				if($cycle) $title = $cycle->getName($this->getRequest()->getLocale());
			}
		} 
		return $title;
	}
	
	public function displayAction($q, $view="card", $sort, $page=1, $title="", $mode="full", $meta="", $locale=null, $locales=null)
	{
		static $availability = array();

		if(empty($locale)) $locale = $this->getRequest()->getLocale();
		$this->getRequest()->setLocale($locale);
		
		$response = new Response();
		$response->setPublic();
		$response->setMaxAge(600);

		$cards = array();
		$first = 0;
		$last = 0;
		$pagination = '';
		
		$pagesizes = array(
			'list' => 200,
			'spoiler' => 200,
			'card' => 20,
			'scan' => 20,
			'short' => 1000,
		);
		
		if(!array_key_exists($view, $pagesizes)) 
		{
			$view = 'list';
		}
		
		$conditions = $this->syntax($q);

		$this->validateConditions($conditions);

		// reconstruction de la bonne chaine de recherche pour affichage
		$q = $this->buildQueryFromConditions($conditions);
		$last_modified = null;
		if($q && $rows = $this->get_search_rows($conditions, $sort))
		{
			if(count($rows) == 1) 
			{
				$view = "card";
			}
			
			if($title == "") $title = $this->findATitle($conditions);
			
			
			// calcul de la pagination
			$nb_per_page = $pagesizes[$view];
			$first = $nb_per_page * ($page - 1);
			if($first > count($rows)) {
				$page = 1;
				$first = 0;
			}
			$last = $first + $nb_per_page;
			
			for($rowindex = $first; $rowindex < $last && $rowindex < count($rows); $rowindex++) {
				if(empty($last_modified) || $last_modified < $rows[$rowindex]->getTs()) $last_modified = $rows[$rowindex]->getTs();
			}
			$response->setLastModified($last_modified);
			if ($response->isNotModified($this->getRequest())) {
				return $response;
			}
			// data à passer à la view
			for($rowindex = $first; $rowindex < $last && $rowindex < count($rows); $rowindex++) {
				$card = $rows[$rowindex];
				$pack = $card->getPack();
				$cardinfo = $this->getCardInfo($card, false);
				if(empty($availability[$pack->getCode()])) {
					$availability[$pack->getCode()] = false;
					if($pack->getReleased() && $pack->getReleased() <= new \DateTime()) $availability[$pack->getCode()] = true;
				}
				$cardinfo['available'] = $availability[$pack->getCode()];
				if($view == "spoiler" || $view == "card") {
					$cardinfo['text'] = implode(array_map(function ($l) { return "<p>$l</p>"; }, explode("\r\n", $cardinfo['text'])));
					$cardinfo['text'] = $this->replaceSymbols($cardinfo['text']);
					$cardinfo['flavor'] = $this->replaceSymbols($cardinfo['flavor']);
					if($view == "card") {
						$cardinfo['rulings'] = array();//$this->getCardRulings($cardinfo['id']);
						$cardinfo['alternatives'] = $this->getCardAlternatives($card);
					}
				}
				$cards[] = $cardinfo;
			}

			$first += 1;

			// si on a des cartes on affiche une bande de navigation/pagination
			if(count($rows)) {
				if(count($rows) == 1) {
					$pagination = $this->setnavigation($card, $q, $view, $sort);
				} else {
					$pagination = $this->pagination($nb_per_page, count($rows), $first, $q, $view, $sort);
				}
			}
			
			// si on est en vue "short" on casse la liste par tri
			if(count($cards) && $view == "short") {
				
				$sortfields = array(
					'set' => 'setname',
					'name' => 'title',
					'faction' => 'faction',
					'type' => 'type',
					'cost' => 'cost',
					'strength' => 'strength',
				);
				
				$brokenlist = array();
				for($i=0; $i<count($cards); $i++) {
					$val = $cards[$i][$sortfields[$sort]];
					if($sort == "name") $val = substr($val, 0, 1);
					if(!isset($brokenlist[$val])) $brokenlist[$val] = array();
					array_push($brokenlist[$val], $cards[$i]);
				}
				$cards = $brokenlist;
			}
		}
		
		$searchbar = $this->renderView('NetrunnerdbCardsBundle:Search:searchbar.html.twig', array(
			"q" => $q, 
			"view" => $view, 
			"sort" => $sort,
		));
		
		if(empty($title)) {
			$title = $q;
		}

		$mode_templates = array(
			"full" => 'NetrunnerdbCardsBundle::main.html.twig',
			"fragment" => 'NetrunnerdbCardsBundle::fragment.html.twig',
			"embed" => 'NetrunnerdbCardsBundle::embed.html.twig',
		);
		if(empty($mode_templates[$mode])) {
			$mode = 'full';
		}

		$response->headers->set('RequestUri', preg_replace('/[\&\?]mode=fragment/', '', $this->getRequest()->getRequestUri()));
		$response->headers->set('PageTitle', rawurlencode($title));
		$response->headers->set('SearchString', rawurlencode($q));
		
		// attention si $s="short", $cards est un tableau à 2 niveaux au lieu de 1 seul
		return $this->render('NetrunnerdbCardsBundle:Search:display-'.$view.'.html.twig', array(
			"view" => $view, 
			"sort" => $sort, 
			"cards" => $cards, 
			"first"=> $first, 
			"last" => $last, 
			"searchbar" => $searchbar,
			"pagination" => $pagination,
			"title" => $title,
			"mode" => $mode,
			"metadescription" => $meta,
			"display_mode_template" => $mode_templates[$mode],
			"locales" => $locales,
		), $response);
	}
	
	public function setnavigation($card, $q, $view, $sort)
	{
		$locale = $this->getRequest()->getLocale();
		$em = $this->getDoctrine();
		$prev = $em->getRepository('NetrunnerdbCardsBundle:Card')->findOneBy(array("pack" => $card->getPack(), "number" => $card->getNumber()-1));
		$next = $em->getRepository('NetrunnerdbCardsBundle:Card')->findOneBy(array("pack" => $card->getPack(), "number" => $card->getNumber()+1));
		return $this->renderView('NetrunnerdbCardsBundle:Search:setnavigation.html.twig', array(
			"prevtitle" => $prev ? $prev->getTitle($locale) : "",
			"prevhref" => $prev ? $this->get('router')->generate('netrunnerdb_netrunner_cards_zoom', array('card_code' => $prev->getCode(), "_locale" => $locale)) : "",
			"nexttitle" => $next ? $next->getTitle($locale) : "",
			"nexthref" => $next ? $this->get('router')->generate('netrunnerdb_netrunner_cards_zoom', array('card_code' => $next->getCode(), "_locale" => $locale)) : "",
			"settitle" => $card->getPack()->getName(),
			"sethref" => $this->get('router')->generate('netrunnerdb_netrunner_cards_list', array('pack_code' => $card->getPack()->getCode(), "_locale" => $locale)),
			"_locale" => $locale,
		));
	}

	public function paginationItem($q = null, $v, $s, $ps, $pi, $total)
	{
		$locale = $this->getRequest()->getLocale();
		return $this->renderView('NetrunnerdbCardsBundle:Search:paginationitem.html.twig', array(
			"href" => $q == null ? "" : $this->get('router')->generate('netrunnerdb_netrunner_cards_find', array('q' => $q, 'view' => $v, 'sort' => $s, 'page' => $pi, '_locale' => $locale)),
			"ps" => $ps,
			"pi" => $pi,
			"s" => $ps*($pi-1)+1,
			"e" => min($ps*$pi, $total),
		));
	}
	
	public function pagination($pagesize, $total, $current, $q, $view, $sort)
	{
		if($total < $pagesize) {
			$pagesize = $total;
		}
	
		$pagecount = ceil($total / $pagesize);
		$pageindex = ceil($current / $pagesize); #1-based
		
		$startofpage = ($pageindex - 1) * $pagesize + 1;
		$endofpage = $startofpage + $pagesize;
		
		$first = "";
		if($pageindex > 2) {
			$first = $this->paginationItem($q, $view, $sort, $pagesize, 1, $total);
		}

		$prev = "";
		if($pageindex > 1) {
			$prev = $this->paginationItem($q, $view, $sort, $pagesize, $pageindex - 1, $total);
		}
		
		$current = $this->paginationItem(null, $view, $sort, $pagesize, $pageindex, $total);

		$next = "";
		if($pageindex < $pagecount) {
			$next = $this->paginationItem($q, $view, $sort, $pagesize, $pageindex + 1, $total);
		}
		
		$last = "";
		if($pageindex < $pagecount - 1) {
			$last = $this->paginationItem($q, $view, $sort, $pagesize, $pagecount, $total);
		}
		
		return $this->renderView('NetrunnerdbCardsBundle:Search:pagination.html.twig', array(
			"first" => $first,
			"prev" => $prev,
			"current" => $current,
			"next" => $next,
			"last" => $last,
			"count" => $total,
			"ellipsisbefore" => $pageindex > 3,
			"ellipsisafter" => $pageindex < $pagecount - 2,
		));
	}

	private function buildQueryFromConditions($conditions)
	{
		// reconstruction de la bonne chaine de recherche pour affichage
		return implode(" ", array_map(
				function ($l) {
					return ($l[0] ? $l[0].$l[1] : "")
					. implode("|", array_map(
							function ($s) {
								return preg_match("/^[\p{L}\p{N}\-]+$/u", $s) ?$s : "\"$s\"";
							},
							array_slice($l, 2)
					));
				},
				$conditions
		));
	}
	
	private function validateConditions(&$conditions)
	{
		// suppression des conditions invalides
		$canDoNumeric = array('o', 'n', 'p', 'r', 'y');
		$numeric = array('<', '>');
		$factions = array('h','w','a','s','c','j','n','-');
		foreach($conditions as $i => $l)
		{
			if(in_array($l[1], $numeric) && !in_array($l[0], $canDoNumeric)) unset($conditions[$i]);
			if($l[0] == 'f')
			{
				$conditions[$i][2] = substr($l[2],0,1);
				if(!in_array($conditions[$i][2], $factions)) unset($conditions[$i]);
			}
		}
	}
	
	public function apisearchAction($query)
	{
		$response = new Response();
		$response->setPublic();
		$response->setMaxAge(600);
		
		$jsonp = $this->getRequest()->query->get('jsonp');
		$locale = $this->getRequest()->query->get('_locale');
		if(isset($locale)) $this->getRequest()->setLocale($locale);
		
		$conditions = $this->syntax($query);
		$this->validateConditions($conditions);
		$query = $this->buildQueryFromConditions($conditions);
	
		$cards = array();
		$last_modified = null;
		if($query && $rows = $this->get_search_rows($conditions, "set"))
		{
			for($rowindex = 0; $rowindex < count($rows); $rowindex++) {
				if(empty($last_modified) || $last_modified < $rows[$rowindex]->getTs()) $last_modified = $rows[$rowindex]->getTs();
			}
			$response->setLastModified($last_modified);
			if ($response->isNotModified($this->getRequest())) {
				return $response;
			}
			for($rowindex = 0; $rowindex < count($rows); $rowindex++) {
				$card = $this->getCardInfo($rows[$rowindex], true, "en");
				$cards[] = $card;
			}
		}

		$content = json_encode($cards);
		if(isset($jsonp))
		{
			$content = "$jsonp($content)";
		}
	
		$response->headers->set('Content-Type', 'application/javascript');
		$response->setContent($content);
		return $response;
	}

	public function apisetAction($pack_code)
	{
		$response = new Response();
		$response->setPublic();
		$response->setMaxAge(600);
	
		$locale = $this->getRequest()->query->get('_locale');
		if(isset($locale)) $this->getRequest()->setLocale($locale);
	
		$format = $this->getRequest()->getRequestFormat();
		
		$pack = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Pack')->findOneBy(array('code' => $pack_code));
		if(!$pack) die();
		
		$conditions = $this->syntax("e:$pack_code");
		$this->validateConditions($conditions);
		$query = $this->buildQueryFromConditions($conditions);
	
		$cards = array();
		$last_modified = null;
		if($query && $rows = $this->get_search_rows($conditions, "set"))
		{
			for($rowindex = 0; $rowindex < count($rows); $rowindex++) {
				if(empty($last_modified) || $last_modified < $rows[$rowindex]->getTs()) $last_modified = $rows[$rowindex]->getTs();
			}
			$response->setLastModified($last_modified);
			if ($response->isNotModified($this->getRequest())) {
				return $response;
			}
			for($rowindex = 0; $rowindex < count($rows); $rowindex++) {
				$card = $this->getCardInfo($rows[$rowindex], true, "en");
				$cards[] = $card;
			}
		}

		if($format == "json") {
			$response->headers->set('Content-Type', 'application/javascript');
			$response->setContent(json_encode($cards));
		} else if($format == "xml") {
			$cardsxml = array();
			foreach($cards as $card) {
				
				if(!isset($card['subtype'])) $card['subtype'] = "";
				if($card['uniqueness']) $card['subtype'] .= empty($card['subtype']) ? "Unique" : " - Unique";
				$card['subtype'] = str_replace(' - ','-',$card['subtype']);
				
				if(preg_match('/(.*): (.*)/', $card['title'], $matches)) {
					$card['title'] = $matches[1];
					$card['subtitle'] = $matches[2];
				} else {
					$card['subtitle'] = "";
				}
			
				if(!isset($card['cost'])) {
					if(isset($card['advancementcost'])) $card['cost'] = $card['advancementcost'];
					if(isset($card['baselink'])) $card['cost'] = $card['baselink'];
					else $card['cost'] = 0;
				}
				
				if(!isset($card['strength'])) {
					if(isset($card['agendapoints'])) $card['strength'] = $card['agendapoints'];
					else if(isset($card['trash'])) $card['strength'] = $card['trash'];
					else if(isset($card['influencelimit'])) $card['strength'] = $card['influencelimit'];
					else if($card['type_code'] == "program") $card['strength'] = '-'; 
					else $card['strength'] = '';
				}
				
				if(!isset($card['memoryunits'])) {
					if(isset($card['minimumdecksize'])) $card['memoryunits'] = $card['minimumdecksize'];
					else $card['memoryunits'] = '';
				}
				
				if(!isset($card['factioncost'])) {
					$card['factioncost'] = '';
				}
				
				if(!isset($card['flavor'])) {
					$card['flavor'] = '';
				}
				
				if($card['faction'] == "Weyland Consortium") {
					$card['faction'] = "The Weyland Consortium";
				}
				
				$card['text'] = str_replace("<strong>", '', $card['text']);
				$card['text'] = str_replace("</strong>", '', $card['text']);
				$card['text'] = str_replace("<sup>", '', $card['text']);
				$card['text'] = str_replace("</sup>", '', $card['text']);
				$card['text'] = str_replace("&ndash;", ' -', $card['text']);
				$card['text'] = htmlspecialchars($card['text'], ENT_QUOTES | ENT_XML1);
				$card['text'] = str_replace("\n", '&#xD;&#xA;', $card['text']);
				
				$card['flavor'] = htmlspecialchars($card['flavor'], ENT_QUOTES | ENT_XML1);
				$card['flavor'] = str_replace("\n", '&#xD;&#xA;', $card['flavor']);
				
				$cardsxml[] = $card;
			}
			
			$response->headers->set('Content-Type', 'application/xml');
			$response->setContent($this->renderView('NetrunnerdbCardsBundle::apiset.xml.twig', array(
				"name" => $pack->getName(),
				"cards" => $cardsxml,
			)));
			
		}
		return $response;
	}
	
	
}
