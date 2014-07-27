<?php

namespace Netrunnerdb\CardsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TradController extends Controller
{
    public function uploadFormAction()
    {
        return $this->render('NetrunnerdbCardsBundle:Trad:form.html.twig');
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
                	case 'E': $card['title'] = $cell->getValue(); break;
                	case 'H': $card['keywords'] = $cell->getValue(); break;
                	case 'I': $card['text'] = str_replace("\n", "\r\n", $cell->getValue()); break;
                	case 'U': $card['illustrator'] = $cell->getValue(); break;
                	case 'V': $card['flavor'] = $cell->getValue(); break;
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
            /* @var $dbcard \Netrunnerdb\CardsBundle\Entity\Card */
            $dbcard = $repo->findOneBy(array('code' => $card['code']));
            if(!$dbcard) continue;
            $cards[$i]['oldtitle'] = $dbcard->getTitle($locale, true);
            $cards[$i]['oldkeywords'] = $dbcard->getKeywords($locale, true);
            $cards[$i]['oldtext'] = $dbcard->getText($locale, true);
            $cards[$i]['oldillustrator'] = $dbcard->getIllustrator();
            $cards[$i]['oldflavor'] = $dbcard->getFlavor($locale, true);
            
            $cards[$i]['warning'] = ($cards[$i]['oldtitle'] && $cards[$i]['oldtitle'] != $cards[$i]['title']) ||
            ($cards[$i]['oldkeywords'] && $cards[$i]['oldkeywords'] != $cards[$i]['keywords']) ||
            ($cards[$i]['oldtext'] && $cards[$i]['oldtext'] != $cards[$i]['text']) ||
            ($cards[$i]['oldillustrator'] && $cards[$i]['oldillustrator'] != $cards[$i]['illustrator']) ||
            ($cards[$i]['oldflavor'] && $cards[$i]['oldflavor'] != $cards[$i]['flavor']);
        }
        
        return $this->render('NetrunnerdbCardsBundle:Trad:confirm.html.twig', array(
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
        
        foreach($cards as $i => $card)
        {
            /* @var $dbcard \Netrunnerdb\CardsBundle\Entity\Card */
            $dbcard = $repo->findOneBy(array('code' => $card['code']));
            if(!$dbcard) continue;
            $dbcard->setTitle($card['title'], $locale);
            $dbcard->setKeywords($card['keywords'], $locale);
            $dbcard->setText($card['text'], $locale);
            $dbcard->setIllustrator($card['illustrator']);
            $dbcard->setFlavor($card['flavor'], $locale);
        }
        $em->flush();
        
        return new Response('OK');
    }
    
}