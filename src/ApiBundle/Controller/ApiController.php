<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\DependencyInjection\Dump\Container;

class ApiController extends Controller
{

    public function isCardValidAction($cardNumber)
    {
        $sql = "SELECT `endValidity` FROM `card` WHERE `card_number` = '$cardNumber'";
        $em = $this->getDoctrine()->getManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        if(isset($result[0]) and $result[0]['endValidity'] != NULL)
        {

            $endValidityClient = $result[0]['endValidity'];
            $format = 'Y-m-d H:i:s';
            $dateEndValidityClient = \DateTime::createFromFormat($format, $endValidityClient);

            $actualDate = date_create();
            $actualDate->format('Y-m-d H:i:s');

            if ($dateEndValidityClient > $actualDate)
            {
                return true;
            }
            else
            {
                return false;
            }

        }
        else
        {
            return false;
        }
    }

}
