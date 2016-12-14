<?php

namespace AdminBundle\Controller;

use AppBundle\Controller\AppController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AppController
{
    public function indexAction()
    {
        if($this->isAuthorize('ROLE_ADMIN'))
        {
            return $this->render('AdminBundle:Default:index.html.twig');
        }
        else
        {
            return $this->redirectToRoute("login_user");
        }
    }

    public function searchAndFilterAction(Request $request)
    {
        $dataForm = $request->request->all();
        $param = $dataForm['search'];

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('UserBundle:User')->searchUser($param);

        return $this->render('AdminBundle:Default:index.html.twig', array('users' => $users));
    }
}
