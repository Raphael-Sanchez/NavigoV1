<?php

namespace UserBundle\Form;

use Doctrine\DBAL\Types\StringType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use CardBundle\Form\CardType;


class LoginUserType extends AbstractType
{

    private $dataClass;

    public function __construct()
    {
        $this->dataClass = 'UserBundle\Entity\User';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('card', 'CardBundle\Form\CardType', array(
                "required"      => true,
            ))
            ->add('password', PasswordType::class, array(
                "required"      => true,
                "label"         => 'Mot de passe',
            ))
            ->add('passwordCheck', PasswordType::class, array(
                "required"      => true,
                "label"         => 'Confirmation mot de passe',
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
        ));
    }
}