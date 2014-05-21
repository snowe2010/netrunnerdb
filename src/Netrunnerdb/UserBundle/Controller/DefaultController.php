<?php

namespace Netrunnerdb\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('NetrunnerdbUserBundle:Default:index.html.twig', array('name' => $name));
    }
    
    public function profileAction()
    {
    	$user = $this->getUser();
    	
    	$factions = $this->get('doctrine')->getRepository('NetrunnerdbCardsBundle:Faction')->findAll();
    	foreach($factions as $i => $faction) {
    		$factions[$i]->localizedName = $faction->getName($this->getRequest()->getLocale());
    	}
    	
    	return $this->render('NetrunnerdbUserBundle:Default:profile.html.twig', array(
    			'user'=> $user, 'factions' => $factions));
    }
    
    public function saveProfileAction()
    {
    	/* @var $user \Netrunnerdb\UserBundle\Entity\User */
    	$user = $this->getUser();
    	$request = $this->getRequest();
    	
    	$resume = filter_var($request->get('resume'), FILTER_SANITIZE_STRING);
    	$faction_code = filter_var($request->get('user_faction_code'), FILTER_SANITIZE_STRING);
    	$notifAuthor = $request->get('notif_author') ? TRUE : FALSE;
    	$notifCommenter = $request->get('notif_commenter') ? TRUE : FALSE;
    	$notifMention = $request->get('notif_mention') ? TRUE : FALSE;
    	 
    	$user->setFaction($faction_code);
    	$user->setResume($resume);
    	$user->setNotifAuthor($notifAuthor);
    	$user->setNotifCommenter($notifCommenter);
    	$user->setNotifMention($notifMention);
    	
    	$this->get('doctrine')->getManager()->flush();
    	
        $this->get('session')
            ->getFlashBag()
            ->set('notice', "Successfully saved your profile.");
		
    	return $this->redirect($this->generateUrl('user_profile'));
    }
}
