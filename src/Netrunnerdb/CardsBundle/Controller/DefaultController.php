<?php

namespace Netrunnerdb\CardsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Netrunnerdb\CardsBundle\Entity\Card;
use Netrunnerdb\CardsBundle\Entity\Pack;
use Netrunnerdb\CardsBundle\Entity\Cycle;
use Netrunnerdb\CardsBundle\Entity\Changelog;

class DefaultController extends Controller
{
	public function searchAction()
	{
		$dbh = $this->get('doctrine')->getConnection();
	
		$list_packs = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Pack')->findBy(array(), array("released" => "ASC", "number" => "ASC"));
		$packs = array();
		foreach($list_packs as $pack) {
			$packs[] = array(
					"name" => $pack->getName($this->getRequest()->getLocale()),
					"code" => $pack->getCode(),
			);
		}
	
		$list_cycles = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Cycle')->findBy(array(), array("number" => "ASC"));
		$cycles = array();
		foreach($list_cycles as $cycle) {
			$cycles[] = array(
					"name" => $cycle->getName($this->getRequest()->getLocale()),
					"code" => $cycle->getCode(),
			);
		}
	
		$list_types = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Type')->findBy(array(), array("name" => "ASC"));
		$types = array_map(function ($type) {
			return $type->getName();
		}, $list_types);
	
		$list_keywords = $dbh->executeQuery("SELECT DISTINCT c.keywords FROM card c WHERE c.keywords != ''")->fetchAll();
		$keywords = array();
		foreach($list_keywords as $keyword) {
			$subs = explode(' - ', $keyword["keywords"]);
			foreach($subs as $sub) {
				$keywords[$sub] = 1;
			}
		}
		$keywords = array_keys($keywords);
		sort($keywords);
	
		$list_illustrators = $dbh->executeQuery("SELECT DISTINCT c.illustrator FROM card c WHERE c.illustrator != '' ORDER BY c.illustrator")->fetchAll();
		$illustrators = array_map(function ($elt) {
			return $elt["illustrator"];
		}, $list_illustrators);
	
		$response = new Response();
		$response->setPrivate();
	
		return $this->render('NetrunnerdbCardsBundle:Search:searchform.html.twig', array(
				"packs" => $packs,
				"cycles" => $cycles,
				"types" => $types,
				"keywords" => $keywords,
				"illustrators" => $illustrators,
				"allsets" => $this->allsets(),
				'locales' => $this->renderView('NetrunnerdbCardsBundle:Default:langs.html.twig'),
		), $response);
	}
	
	private function replaceSymbols($text)
	{
		$text = str_replace('[Subroutine]', '<span class="icon icon-subroutine"></span>', $text);
		$text = str_replace('[Credits]', '<span class="icon icon-credit"></span>', $text);
		$text = str_replace('[Trash]', '<span class="icon icon-trash"></span>', $text);
		$text = str_replace('[Click]', '<span class="icon icon-click"></span>', $text);
		$text = str_replace('[Recurring Credits]', '<span class="icon icon-recurring-credit"></span>', $text);
		$text = str_replace('[Memory Unit]', '<span class="icon icon-mu"></span>', $text);
		$text = str_replace('[Link]', '<span class="icon icon-link"></span>', $text);
		return $text;
	}
	
	function rulesAction()
	{
		static $sources;
		if(!$sources) $sources = array(1 => "Latest FAQ", 2 => "Direct answer");
		
		$response = new Response();
		$response->setPrivate();
		
//		$list_rulings = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Rulings')->findBy(array(), array("created" => "ASC"));
		$rulings_faq = array();
		$rulings_mail = array();
/*		foreach($list_rulings as $ruling) {
			$r = array(
				"question" => $ruling->getQuestion(),
				"answer" => $ruling->getAnswer(),
				"created" => $ruling->getCreated()->format('Y-m-d')
			);
			if($ruling->getSource() == 1) {
				$rulings_faq[] = $r;
			} else {
				$rulings_mail[] = $r;
			}
		}
*/
		$page = $this->replaceSymbols($this->renderView('NetrunnerdbCardsBundle:Default:rules.html.twig', array(
			"faq" => $rulings_faq,
			"mail" => $rulings_mail,
		)));
		$response->setContent($page);
		return $response;
	}
	
	function aboutAction()
	{
		
		$response = new Response();
		$response->setPrivate();
		
		return $this->render('NetrunnerdbCardsBundle:Default:about.html.twig', array(
		), $response);
	}

	function apidocAction()
	{
		
		$response = new Response();
		$response->setPrivate();
		
		return $this->render('NetrunnerdbCardsBundle:Default:apidoc.html.twig', array(
		), $response);
	}

	private function allsetsdata()
	{
		$list_cycles = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Cycle')->findBy(array(), array("number" => "ASC"));
		$cycles = array();
		foreach($list_cycles as $cycle) {
			$packs = array();
			$sreal=0; $smax = 0;
			foreach($cycle->getPacks() as $pack) {
				$real = count($pack->getCards());
				$sreal += $real;
				$max = $pack->getSize();
				$smax += $max;
				$packs[] = array(
						"name" => $pack->getName($this->getRequest()->getLocale()),
						"code" => $pack->getCode(),
				        "cyclenumber" => $cycle->getNumber(),
						"available" => $pack->getReleased() ? $pack->getReleased()->format('Y-m-d') : '',
						"known" => intval($real),
						"total" => $max,
						"url" => $this->get('router')->generate('netrunnerdb_netrunner_cards_list', array('pack_code' => $pack->getCode()), true),
						"search" => "e:".$pack->getCode(),
						"packs" => '',
				);
			}
			if(count($packs) == 1 && $packs[0]["name"] == $cycle->getName($this->getRequest()->getLocale())) {
				$cycles[] = $packs[0];
			} 
			else {
				$cycles[] = array(
						"name" => $cycle->getName($this->getRequest()->getLocale()),
						"code" => $cycle->getCode(),
				        "cyclenumber" => $cycle->getNumber(),
						"known" => intval($sreal),
						"total" => $smax,
						"url" => $this->generateUrl('netrunnerdb_netrunner_cards_cycle', array('cycle_code' => $cycle->getCode()), true),
						"search" => 'c:'.$cycle->getCode(),
						"packs" => $packs,
				);
			}
		}
		return $cycles;
	}

	private function allsetsnocycledata()
	{
		$list_packs = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Pack')->findBy(array(), array("released" => "ASC", "number" => "ASC"));
		$packs = array();
		$sreal=0; $smax = 0;
		foreach($list_packs as $pack) {
			$real = count($pack->getCards());
			$sreal += $real;
			$max = $pack->getSize();
			$smax += $max;
			$packs[] = array(
					"name" => $pack->getName($this->getRequest()->getLocale()),
					"code" => $pack->getCode(),
					"number" => $pack->getNumber(),
					"cyclenumber" => $pack->getCycle()->getNumber(),
					"available" => $pack->getReleased() ? $pack->getReleased()->format('Y-m-d') : '',
					"known" => intval($real),
					"total" => $max,
					"url" => $this->get('router')->generate('netrunnerdb_netrunner_cards_list', array('pack_code' => $pack->getCode()), true),
			);
		}
		return $packs;
	}
	
	private function allsets() 
	{
		return $this->renderView('NetrunnerdbCardsBundle:Default:allsets.html.twig', array(
			"data" => $this->allsetsdata(),
		));
	}
	
	public function apisetsAction()
	{
		$response = new Response();
		$response->setPublic();
		$response->setMaxAge(600);
		$response->headers->add(array('Access-Control-Allow-Origin' => '*'));
		
		$jsonp = $this->getRequest()->query->get('jsonp');
		$locale = $this->getRequest()->query->get('_locale');
		if(isset($locale)) $this->getRequest()->setLocale($locale);
		
		$data = $this->allsetsnocycledata();
		
		$content = json_encode($data);
		if(isset($jsonp))
		{
			$content = "$jsonp($content)";
			$response->headers->set('Content-Type', 'application/javascript');
		} else
		{
			$response->headers->set('Content-Type', 'application/json');
		}
		$response->setContent($content);
		return $response;
	}
	
	public function ffgAction()
	{
	    if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
	        throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
	    }
	    
	    // http://www.fantasyflightgames.com/ffg_content/organized-play/2013/private-security-force.png
	    $em = $this->get('doctrine')->getManager();
	    
	    $fs = new \Symfony\Component\Filesystem\Filesystem();
	    
	    $old = array();
	    $new = array();
	    $fails = array();
	    
	    $base = 'http://www.fantasyflightgames.com/ffg_content/android-netrunner';
	    $root = $this->get('kernel')->getRootDir()."/../ffg";
	    
	    $segments = array(
	    	'core' => 'core-set-cards',
	        'genesis' => 'genesis-cycle/cards',
	        'creation-and-control' => 'deluxe-expansions/creation-and-control',
	        'spin' => 'spin-cycle/cards',
	        'honor-and-profit' => 'deluxe-expansions/honor-and-profit/cards',
	        'lunar' => 'lunar-cycle/cards',
	    );
	    
	    $corrections = array(
	    	'astroscript-pilot-program' => 'autoscript-pilot-program',
	            'haas-bioroid-stronger-together' => 'haas-bioroid-adn02',
	            'ash-2x3zb9cy' => 'ash',
	    );
	    
	    $cycles = $em->getRepository('NetrunnerdbCardsBundle:Cycle')->findBy(array(), array('number' => 'asc'));
	    /* @var $cycle Cycle */
	    foreach ($cycles as $cycle) {
	    	if(!isset($segments[$cycle->getCode()])) {
	            continue;
	        }
	        $segment = $segments[$cycle->getCode()];
	        
	        $packs = $cycle->getPacks();
	        /* @var $pack Pack */
	        foreach($packs as $pack) {
	            $cards = $pack->getCards();
	            /* @var $card Card */
	            foreach($cards as $card) {
	                $filepath = $root."/".$card->getCode().".png";
	                $imgpath = "/ffg/".$card->getCode().".png";
	                if(file_exists($filepath)) {
	                    $old[] = $imgpath;
	                    continue;
	                }
	                
	                $ffg = $title = $card->getTitle();
	                $ffg = str_replace(' ', '-', $ffg);
	                $ffg = str_replace('.', '-', $ffg);
	                $ffg = str_replace('\'', '', $ffg);
	                if($cycle->getCode() === 'core' || $card->getSide()->getName() === 'Runner' || $card->getKeywords() === 'Division') {
	                    $ffg = preg_replace('/:.*/', '', $ffg);
	                } else {
	                    $ffg = str_replace(':', '', $ffg);
	                }
	                $ffg = strtolower($ffg);
	                $ffg = iconv('UTF-8', 'ASCII//TRANSLIT', $ffg);
	                $ffg = preg_replace('/[^a-z0-9\-]/', '', $ffg);
	                
	                if(isset($corrections[$ffg])) {
	                    $ffg = $corrections[$ffg];
	                }
	                
	                $url = "$base/$segment/$ffg.png";
	                
	                $ch = curl_init($url);
	                curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	                if($response = curl_exec($ch)) {
	                    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	                    if($content_type === "image/png") {
	                        $fs->dumpFile($filepath, $response);
	                        $new[] = $imgpath;
	                        continue;
	                    }
	                }
	                
	                $fails[] = $url;
	            }
	        }
	    }
	    print "<h2>Fails</h2>";
	    foreach($fails as $fail) { print "<p>$fail</p>"; }
	    print "<h2>New</h2>";
	    foreach($new as $img) { print "<p><img src='$imgpath'></p>"; }
	    print "<h2>Old</h2>";
	    foreach($old as $img) { print "<p><img src='$imgpath'></p>"; }
	    return new Response('');
	}
}
