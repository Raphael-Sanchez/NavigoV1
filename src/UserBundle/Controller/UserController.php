<?php

namespace UserBundle\Controller;

use AppBundle\Controller\AppController;
use CardBundle\Form\PhotoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use UserBundle\Entity\User;
use CardBundle\Entity\Card;
use UserBundle\Form\UserType;
use UserBundle\Form\LoginUserType;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Controller\SecurityController;
use Symfony\Component\HttpFoundation\Session\Session;


class UserController extends AppController
{
    public function indexAction()
    {

        if($this->isAuthorize('ROLE_USER'))
        {
            $user = $_SESSION['user'];
            $cardNumber = $user['cardNumber'];
            $cardEntity = $this->getDoctrine()->getManager()->getRepository('CardBundle:Card')->findBy(array("cardNumber" => $cardNumber));
            $cardEndValidityFormat = null;

            if ($cardEntity[0]->getEndValidity() != NULL)
            {
                $cardEndValidity = $cardEntity[0]->getEndValidity();
                $cardEndValidityFormat = $cardEndValidity->format('l-t-F-Y');
            }

            return $this->render('UserBundle:Default:index.html.twig', array('user' => $user, 'cardEndValidity' => $cardEndValidityFormat));
        }
        else
        {
            return $this->redirectToRoute("login_user");
        }
    }

    public function registerUserAction(Request $request)
    {

        if($this->isAuthorize('ROLE_USER'))
        {
            return $this->redirectToRoute("user_homepage");
        }
        else
        {
            $user = new User();

            $form = $this->createForm(UserType::class, $user);
            $form->add('submit', SubmitType::class, array(
                'label' => 'Envoyer',
            ));

            $form->handleRequest($request);
            $formData = $form->getData();

            if ($form->isSubmitted() && $form->isValid()) {

                if(count($this->ifRegisterFormIsValid($formData)) == 0)
                {
                    $cardNumberOfForm = $formData->getCard()->getCardNumber();
                    $cardEntity = $this->getDoctrine()->getManager()->getRepository('CardBundle:Card')->findBy(array("cardNumber" => $cardNumberOfForm));

                    $user->setLastName(strtoupper($formData->getLastName()));
                    $user->setFirstName(ucfirst($formData->getFirstName()));
                    $user->setRole('ROLE_USER');
                    $user->setPasswordCheck('NULL');
                    $options = [
                        'cost' => 8,
                        'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
                    ];
                    $passwordHash = password_hash($formData->getPassword(), PASSWORD_BCRYPT, $options);
                    $user->setPasswordHash('NULL');
                    $user->setPassword($passwordHash);
                    $user->setCard($cardEntity[0]);

//                    $cardEntity[0]->setUser($user);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();

                    return $this->connectUser($user);
                }
                else
                {
                    $errors = $this->ifRegisterFormIsValid($formData);
                    return $this->render("UserBundle:Form:register_user.html.twig", array('form' => $form->createView(), 'errors' => $errors));
                }
            }

            return $this->render("UserBundle:Form:register_user.html.twig", array('form' => $form->createView()));
        }

    }

    public function loginUserAction(Request $request)
    {
        if($this->isAuthorize('ROLE_USER'))
        {
            return $this->redirectToRoute("user_homepage");
        }
        else
        {
            $user = new User();
            $form = $this->createForm(LoginUserType::class, $user);
            $form->add('submit', SubmitType::class, array(
                'label' => 'Envoyer',
            ));

            $form->handleRequest($request);
            $formData = $form->getData();

            if ($form->isSubmitted() && $form->isValid()) {
                if (count($this->ifLoginFormIsValid($formData)) == 0) {
                    $card = $this->getDoctrine()->getManager()->getRepository('CardBundle:Card')->findBy(array("cardNumber" => $formData->getCard()->getCardNumber()));
                    $userByCard = $card[0]->getUser();
                    return $this->connectUser($userByCard);
                } else {
                    $errors = $this->ifLoginFormIsValid($formData);
                    return $this->render("UserBundle:Form:login_user.html.twig", array('form' => $form->createView(), 'errors' => $errors));
                }
            }

            return $this->render("UserBundle:Form:login_user.html.twig", array('form' => $form->createView()));
        }
    }

    public function ifRegisterFormIsValid($formData)
    {
        $lastName = $formData->getLastName();
        $firstName = $formData->getFirstName();
        $card = $formData->getCard();
        $password = $formData->getPassword();
        $passwordCheck = $formData->getPasswordCheck();

        $rexHumanName = '/^[a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð ,.\'-]+$/u';
//        $rexNumberPosit = '/^\d+$/';
        $pwdComplexity = '/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/';

        $errors = [];

        if (!preg_match($rexHumanName, $lastName) || strlen($lastName) < 2 || empty($lastName))
        {
            $errors['lastNameError'] = 'Le nom saisi semble incorrect, veuillez réitérer';
        }

        if (!preg_match($rexHumanName, $firstName) || strlen($firstName) < 2 || empty($firstName))
        {
            $errors['firstNameError'] = 'Le prénom saisi semble incorrect, veuillez réitérer';
        }

        if (!isset($card) || strlen($card->getCardNumber()) != 13)
        {
            $errors['cardNumberError'] = 'Code à 13 caractères de votre pass navigo incorrect, veuillez réitérer';
        }
        else
        {
            if($this->isCardExist($card->getCardNumber()))
            {
                if($this->isCardNumberIsAssociedWithUser($card))
                {
                    $errors['cardNumberError'] = 'Ce numéro de pass navigo est déjà associé à un compte veuillez vous connecter !';
                }
            }
            else
            {
                $errors['cardNumberError'] = 'Ce numéro de pass navigo n\'existe pas!';
            }
        }

        if ($password == $passwordCheck)
        {
            if (empty($password) || empty($passwordCheck))
            {
                $errors['passwordError'] = 'Le champ mot de passe et la confirmation doivent être remplis';
            }
            elseif (!preg_match($pwdComplexity, $password))
            {
                $errors['passwordError'] = 'Le mot de passe doit faire minimum 8 caractères, contenir au minimum 1 majuscule, 1 minuscule, 1 nombre et ne doit pas de caractères spéciaux';
            }
        }
        else
        {
            $errors['passwordError'] = 'Le mot de passe n\'est pas identique';
        }

        if(count($errors) > 0)
        {
            $response = $errors;
            return $response;
        }
        else
        {
            $response = $errors;
            return $response;
        }

    }

    protected function ifLoginFormIsValid($formData)
    {

        $cardNumber = $formData->getCard()->getCardNumber();
        $password = $formData->getPassword();
        $passwordCheck = $formData->getPasswordCheck();

        $errors = [];
        $pwdComplexity = '/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/';
        $cardValid = false;
        $passwordValid = false;


        if (!isset($cardNumber) || strlen($cardNumber) != 13)
        {
            $errors['cardNumberError'] = 'Code à 13 caractères de votre pass navigo incorrect, veuillez réitérer';
        }
        else
        {
            $cardValid = true;
        }

        if ($password == $passwordCheck)
        {
            if (empty($password) || empty($passwordCheck))
            {
                $errors['passwordError'] = 'Le champ mot de passe et la confirmation doivent être remplis';
            }
            elseif (!preg_match($pwdComplexity, $password))
            {
                $errors['passwordError'] = 'Le mot de passe doit faire minimum 8 caractères, contenir au minimum 1 majuscule, 1 minuscule, 1 nombre et ne doit pas de caractères spéciaux';
            }

            $passwordValid = true;
        }
        else
        {
            $errors['passwordError'] = 'Le mot de passe n\'est pas identique';
        }

        if ($passwordValid && $cardValid)
        {
            if($this->isCardExist($cardNumber))
            {
                $cardEntity = $this->getDoctrine()->getManager()->getRepository('CardBundle:Card')->findBy(array("cardNumber" => $cardNumber));

                if($cardEntity[0]->getUser())
                {
                    $userPassword = $cardEntity[0]->getUser()->getPassword();

                    if(!password_verify($password, $userPassword))
                    {
                        $errors['passwordError'] = 'Erreur de mot de passe';
                    }
                }
                else
                {
                    $errors['cardNumberError'] = 'Désolé ce compte n\'existe pas, aucun utilisateur associé à la carte';
                }

            }
            else
            {
                $errors['cardNumberError'] = 'Cette carte n\'existe pas, veuillez vérifier la saisie';
            }
        }

        if(count($errors) > 0)
        {
            $response = $errors;
            return $response;
        }
        else
        {
            $response = $errors;
            return $response;
        }

    }

    public function isCardNumberIsAssociedWithUser($card)
    {
        $cardNumberOfForm = $card->getCardNumber();
        $cardNumber = $this->getDoctrine()->getManager()->getRepository('CardBundle:Card')->findBy(array("cardNumber" => $cardNumberOfForm));

        if(!empty($cardNumber))
        {
            if($cardNumber[0]->getUser() == NULL)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }

    private function connectUser($user)
    {
        session_destroy();
        session_start();

        $user = array(
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'cardNumber' => $user->getCard()->getCardNumber(),
            'photo' => $user->getCard()->getPhoto(),
            'role' => $user->getRole()
        );

        $_SESSION['user'] = $user;

        return $this->redirectToRoute("user_homepage");
    }

    public function templateNavigoAction(Request $request)
    {
        if($this->isAuthorize('ROLE_USER'))
        {
            $user = $_SESSION['user'];
            $cardEntity = $this->getDoctrine()->getManager()->getRepository('CardBundle:Card')->findBy(array("cardNumber" => $user['cardNumber']));
            $cardPhoto = null;

            $card = new Card();
            $form = $this->createForm(PhotoType::class, $card);
            $form->add('submit', SubmitType::class, array(
                'label' => 'Envoyer',
            ));
            $form->handleRequest($request);
            $formData = $form->getData();

            if ($form->isSubmitted() && $form->isValid())
            {
                $photo = $formData->getPhoto();
                $photoName = md5(uniqid()).'.'.$photo->guessExtension();

                $photo->move(
                    $this->getParameter('users_photo_directory'),
                    $photoName
                );

                $cardEntity[0]->setPhoto($photoName);

                $em = $this->getDoctrine()->getManager();
                $em->persist($cardEntity[0]);
                $em->flush();

                $cardPhoto = $cardEntity[0]->getPhoto();
                return $this->render('UserBundle:Default:template_navigo.html.twig', array('user' => $user, 'cardPhoto' => $cardPhoto));
            }
            else if ($cardEntity[0]->getPhoto() == null)
            {
                return $this->render("UserBundle:Default:template_navigo.html.twig", array('user' => $user, 'cardPhoto' => $cardPhoto, 'form' => $form->createView()));
            }

            $cardPhoto = $cardEntity[0]->getPhoto();
            return $this->render('UserBundle:Default:template_navigo.html.twig', array('user' => $user, 'cardPhoto' => $cardPhoto));

        }
        else
        {
            return $this->redirectToRoute("login_user");
        }
    }

}