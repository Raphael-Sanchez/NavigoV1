<?php

namespace InvoiceBundle\Controller;

use AppBundle\Controller\AppController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class InvoiceController extends AppController
{
    public function showAction($cardNumber)
    {
        $user = $_SESSION['user'];
        if($this->isAuthorize('ROLE_USER') and $user['cardNumber'] == $cardNumber)
        {
            $cardEntity = $this->getDoctrine()->getManager()->getRepository('CardBundle:Card')->findOneBy(array("cardNumber" => $cardNumber));
            $cardId = $cardEntity->getId();
            $invoiceEntity = $this->getDoctrine()->getManager()->getRepository('InvoiceBundle:Invoice')->findBy(array("card" => $cardId));
            $invoices = $invoiceEntity;

            return $this->render("InvoiceBundle:Default:invoices.html.twig", array('invoices' => $invoices));
        }

        return $this->redirectToRoute("login_user");
    }
}
