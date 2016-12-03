<?php

namespace CardBundle\Controller;

use AppBundle\Controller\AppController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Request;

class CardController extends AppController
{
    public function indexAction()
    {
        return $this->render('CardBundle:Default:index.html.twig');
    }
}
