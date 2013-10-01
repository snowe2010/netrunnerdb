<?php

namespace Netrunnerdb\SocialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('NetrunnerdbSocialBundle:Default:index.html.twig', array('name' => $name));
    }
}
