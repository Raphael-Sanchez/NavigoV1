<?php

namespace CardBundle\Controller;

use AppBundle\Controller\AppController;
use InvoiceBundle\Entity\Invoice;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\EventListener\ValidateRequestListener;

class CardController extends AppController
{

    public function cardSubscriptionAction()
    {
        if($this->isAuthorize('ROLE_USER'))
        {
            $user = $_SESSION['user'];
            $cardNumber = $user['cardNumber'];
            $cardEntity = $this->getDoctrine()->getManager()->getRepository('CardBundle:Card')->findOneBy(array("cardNumber" => $cardNumber));
            $endCardValidity = 'can_not_subscribe';

            if (isset($cardEntity) and $cardEntity->getEndValidity() != NULL)
            {
                $actualDate = date_create();
                $actualDate->format('Y-m-d H:i:s');
                $dateMore3 = $actualDate->add(new \DateInterval('P3D'));
                $endValidity = $cardEntity->getEndValidity();

                if($endValidity <= $dateMore3)
                {
                    $endCardValidity = 'can_subscribe';
                }
            }
            elseif (isset($cardEntity) and $cardEntity->getEndValidity() == NULL)
            {
                $endCardValidity = 'can_subscribe';
            }

            return $this->render('CardBundle:Form:card_subscription.html.twig', array('user' => $user, 'endCardValidity' => $endCardValidity));
        }
        else
        {
            return $this->redirectToRoute("login_user");
        }
    }

    public function ifCardFormSubscriptionIsValidAction(Request $request)
    {
        $dataForm = $request->request->all();
        $user = $_SESSION['user'];

        if (isset($dataForm) and count($dataForm) > 0)
        {
            $durationSubscription = $dataForm['select'];
            $numberCreditCard = $dataForm['bank-cardnumber'];
            $expMonth = $dataForm['exp-month'];
            $expYear = $dataForm['exp-year'];
            $today = getdate();
            $actualYear = $today['year'];

            $maxYear = $actualYear + 3;
            $minYear = $actualYear - 3;
            $regexMonth = "/^0[1-9]$|^1[0-2]$/";
            $regexCreditCard = "/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3})\d{11})$/";
            $errors = [];

            if(!preg_match($regexCreditCard, $numberCreditCard))
            {
                $errors['errorCardNumber'] = 'Numéro de carte invalide';
            }

            if(!preg_match($regexMonth, $expMonth))
            {
                $errors['errorMonth'] = 'Mois saisi invalide';
            }

            if($expYear < $minYear or $expYear > $maxYear)
            {
                $errors['errorYear'] = 'Année saisie invalide';
            }

            if (count($errors) > 0)
            {
                $cardNumber = $user['cardNumber'];
                $cardEntity = $this->getDoctrine()->getManager()->getRepository('CardBundle:Card')->findOneBy(array("cardNumber" => $cardNumber));
                $endCardValidity = $cardEntity->getEndValidity();
                return $this->render('CardBundle:Form:card_subscription.html.twig', array('user' => $user, 'errors' => $errors, 'endCardValidity' => $endCardValidity));
            }
            else
            {
                $cardNumber = $user['cardNumber'];
                $cardEntity = $this->getDoctrine()->getManager()->getRepository('CardBundle:Card')->findOneBy(array("cardNumber" => $cardNumber));

                switch ($durationSubscription) {
                    case 'one-week':
                        $dateInterval = 'P7D';
                        break;
                    case 'one-month':
                        $dateInterval = 'P1M';
                        break;
                    case 'one-year':
                        $dateInterval = 'P1Y';
                        break;
                }

                if ($cardEntity->getEndValidity() != NULL)
                {
                    $endValidity = $cardEntity->getEndValidity();
                    $endValidity->add(new \DateInterval($dateInterval));
                    $newDate = clone $endValidity;
                    $cardEntity->setEndValidity($newDate);
                }
                else
                {
                    $actualDate = date_create();
                    $actualDate->format('Y-m-d H:i:s');
                    $newDate = $actualDate->add(new \DateInterval($dateInterval));
                    $cardEntity->setEndValidity($newDate);
                }

                $cardEntity->setLastSubscription($dateInterval);

                $strDate = date('d-m-Y', date_create()->getTimestamp());
                $newInvoice = new Invoice();
                $newInvoice->setCard($cardEntity);
                $newInvoice->setPaymentDate($strDate);
                $newInvoice->setEndValiditySubscription($newDate);
                $newInvoice->setSubscriptionType($dateInterval);
                $cardEntity->addInvoices($newInvoice);

                $em = $this->getDoctrine()->getManager();
                $em->flush();

                return $this->redirectToRoute("user_homepage");
            }

        }

    }


}
