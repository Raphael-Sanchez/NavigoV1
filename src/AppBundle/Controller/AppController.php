<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AppController extends Controller
{
    public function homeAction(Request $request)
    {
        // Si l'user a le role et qu'il est connecté je le renvoie vers la user home et non la homepage
        if($this->isAuthorize('ROLE_USER'))
        {
            return $this->redirectToRoute("user_homepage");
        }
        else
        {
            return $this->render('AppBundle:Default:home.html.twig');
        }
    }

    public function accessDeniedAction()
    {
        var_dump('acces refuse retourner erreur 403');
        die();
        return $this->render('AppBundle:Default:403.html.twig');
    }

    protected function isAuthorize($needPermission = 'noNeedAuthentication')
    {
        if (isset($_SESSION))
        {
            if(is_array($_SESSION) and isset($_SESSION['user']) and !empty($_SESSION['user']))
            {
                $user = $_SESSION['user'];

                // Vérification du role necessaire pour autoriser l'acces
                if ($needPermission == 'noNeedAuthentication')
                {
                    return true;
                }

                if (isset($user) and is_array($user) and !empty($user))
                {
                    if ($user['role'] != $needPermission)
                    {
                        //return $this->accessDeniedAction();
                        return false;
                    }

                    if ($user['role'] == $needPermission)
                    {
                        return true;
                    }
                }
            }
            else
            {
                // L'utilisateur n'a pas l'authorisation je renvoie false, (mettre flashbag)
                return false;
            }
        }
    }

    public function logoutAction()
    {
        session_destroy();
        return $this->redirectToRoute("app_homepage");
    }

    public function isCardExist($cardNumber)
    {
        $card = $this->getDoctrine()->getManager()->getRepository('CardBundle:Card')->findBy(array("cardNumber" => $cardNumber));

        if(empty($card))
        {
            return false;
        }

        return true;
    }

    public function checkCardValidityAction($cardNumber)
    {
        if($this->isCardExist($cardNumber))
        {
            $cardEntity = $this->getDoctrine()->getManager()->getRepository('CardBundle:Card')->findBy(array("cardNumber" => $cardNumber));
            if($cardEntity[0]->getEndValidity() != NULL)
            {
                if($cardEntity[0]->getEndValidity())
                {
                    $actualDate = date_create();
                    $actualDate->format('Y-m-d H:i:s');

                    $endValidityDate = $cardEntity[0]->getEndValidity();
                    $endValidityDate->format('Y-m-d H:i:s');

                    if ($endValidityDate > $actualDate)
                    {
                        $response = true;
                        return new JsonResponse(array('response' => $response, 'endValidity' => $endValidityDate));
                    }
                    else
                    {
                        $response = false;
                        return new JsonResponse(array('response' => $response, 'expiredDate' => $endValidityDate));
                    }
                }
            }
            else
            {
                $error = 'Aucun abonnement sur votre carte !';
                return new JsonResponse(array('error' => $error));
            }

        }
        else
        {
            $error = 'Numéro de pass navigo invalide !';
            return new JsonResponse(array('error' => $error));
        }

    }
}
