<?php

namespace Netrunnerdb\CardsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Netrunnerdb\CardsBundle\Entity\Card;

class ExcelController extends Controller
{
    public function formAction()
    {
        return $this->render('NetrunnerdbCardsBundle:Excel:form.html.twig');
    }
    
    public function uploadAction(Request $request)
    {
        $locale = $request->get('locale');
        
        /* @var $uploadedFile \Symfony\Component\HttpFoundation\File\UploadedFile */
        $uploadedFile = $request->files->get('upfile');
        $inputFileName = $uploadedFile->getPathname();
        $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($inputFileName);
        $objWorksheet  = $objPHPExcel->getActiveSheet();

        $cards = array();
        $firstRow = true;
        foreach($objWorksheet ->getRowIterator() as $row)
        {
            // dismiss first row (titles)
            if($firstRow)
            {
                $firstRow = false;
                continue;
            }
            
            $card = array('code' => '', 'title' => '', 'keywords' => '', 'text' => '', 'flavor' => '');
            $cellIterator = $row->getCellIterator();
            foreach ($cellIterator as $cell) {
                $c = $cell->getColumn();
                // A:code // E:name // H:keywords // I:text // V:flavor
                switch($c)
                {
                	case 'A': $card['code'] = $cell->getValue(); break;
                	case 'B': $card['pack'] = $cell->getValue(); break;
                	case 'C': $card['number'] = $cell->getValue(); break;
                	case 'D': $card['uniqueness'] = $cell->getValue(); break;
                	case 'E': $card['title'] = $cell->getValue(); break;
                	case 'F': $card['cost'] = $cell->getValue(); break;
                	case 'G': $card['type'] = $cell->getValue(); break;
                	case 'H': $card['keywords'] = $cell->getValue(); break;
                	case 'I': $card['text'] = str_replace("\n", "\r\n", $cell->getValue()); break;
                	case 'J': $card['side'] = $cell->getValue(); break;
                	case 'K': $card['faction'] = $cell->getValue(); break;
                	case 'L': $card['factionCost'] = $cell->getValue(); break;
                	case 'M': $card['strength'] = $cell->getValue(); break;
                	case 'N': $card['trashCost'] = $cell->getValue(); break;
                	case 'O': $card['memoryUnits'] = $cell->getValue(); break;
                	case 'P': $card['advancementCost'] = $cell->getValue(); break;
                	case 'Q': $card['agendaPoints'] = $cell->getValue(); break;
                	case 'R': $card['minimumDeckSize'] = $cell->getValue(); break;
                	case 'S': $card['influenceLimit'] = $cell->getValue(); break;
                	case 'T': $card['baseLink'] = $cell->getValue(); break;
                	case 'U': $card['illustrator'] = $cell->getValue(); break;
                	case 'V': $card['flavor'] = $cell->getValue(); break;
                	case 'W': $card['quantity'] = $cell->getValue(); break;
                	case 'X': $card['limited'] = $cell->getValue(); break;
                }
                
            }
            if(count($card) && !empty($card['code'])) $cards[] = $card;
        }
        
        $this->get('session')->set('trad_upload_data', $cards);
        $this->get('session')->set('trad_upload_locale', $locale);
        
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('Netrunnerdb\CardsBundle\Entity\Card');
        
        foreach($cards as $i => $card)
        {
            $cards[$i]['warning'] = TRUE;
            
            /* @var $dbcard \Netrunnerdb\CardsBundle\Entity\Card */
            $dbcard = $repo->findOneBy(array('code' => $card['code']));
            
            $cards[$i]['oldtitle'] = $dbcard ? $dbcard->getTitle($locale, true) : '';
            $cards[$i]['oldkeywords'] = $dbcard ? $dbcard->getKeywords($locale, true) : '';
            $cards[$i]['oldtext'] = $dbcard ? $dbcard->getText($locale, true) : '';
            $cards[$i]['oldflavor'] = $dbcard ? $dbcard->getFlavor($locale, true) : '';
            
            $cards[$i]['warning'] = ($cards[$i]['oldtitle'] && $cards[$i]['oldtitle'] != $cards[$i]['title']) ||
            ($cards[$i]['oldkeywords'] && $cards[$i]['oldkeywords'] != $cards[$i]['keywords']) ||
            ($cards[$i]['oldtext'] && $cards[$i]['oldtext'] != $cards[$i]['text']) ||
            ($cards[$i]['oldflavor'] && $cards[$i]['oldflavor'] != $cards[$i]['flavor']);
        }
        
        return $this->render('NetrunnerdbCardsBundle:Excel:confirm.html.twig', array(
            'locale' => $locale,
        	'cards' => $cards
        ));
    }

    public function confirmAction()
    {
        $cards = $this->get('session')->get('trad_upload_data');
        $locale = $this->get('session')->get('trad_upload_locale');
        
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->get('doctrine')->getManager();
        $repo = $em->getRepository('Netrunnerdb\CardsBundle\Entity\Card');
        
        $loc = $locale != "en" ? ucfirst($locale) : "";
        
        foreach($cards as $i => $card)
        {
            /* @var $dbcard \Netrunnerdb\CardsBundle\Entity\Card */
            $dbcard = $repo->findOneBy(array('code' => $card['code']));
            if(!$dbcard) {
                $dbcard = new Card();
                $dbcard->setTs(new \DateTime());
            }
            
            $card['pack'] = $em->getRepository('NetrunnerdbCardsBundle:Pack')->findOneBy(array("name$loc" => $card['pack']));
            $card['type'] = $em->getRepository('NetrunnerdbCardsBundle:Type')->findOneBy(array("name$loc" => $card['type']));
            $card['side'] = $em->getRepository('NetrunnerdbCardsBundle:Side')->findOneBy(array("name$loc" => $card['side']));
            $card['faction'] = $em->getRepository('NetrunnerdbCardsBundle:Faction')->findOneBy(array("name$loc" => $card['faction']));
            
            foreach($card as $key => $value) {
                $func = 'set'.ucfirst($key);
                $dbcard->$func($value, $locale);
            }
            
            $em->persist($dbcard);
        }
        $em->flush();
        
        return new Response('OK');
    }
    
}