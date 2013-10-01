<?php

namespace Netrunnerdb\CardsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Netrunnerdb\CardsBundle\Entity\Cards;
use Netrunnerdb\CardsBundle\Entity\Sets;
use Netrunnerdb\CardsBundle\Entity\Supersets;
use Netrunnerdb\CardsBundle\Entity\Types;
use Netrunnerdb\CardsBundle\Entity\Subtypes;
use Netrunnerdb\CardsBundle\Entity\Changelog;

class AdminController extends Controller
{
	
	public function menuAction()
	{
		return new Response('Hello world');
	}

	public function editcardAction($index)
	{
		
		$card = $this->getDoctrine()->getRepository('NetrunnerdbCardsBundle:Cards')->findOneBy(array("index" => $index));
		
		static $normalizer;
		if(!$normalizer) $normalizer = new GetSetMethodNormalizer();
		$cardinfo = $normalizer->normalize($card);
		
		var_dump($cardinfo);
		return new Response('Hello world');
	}
	
		

}
