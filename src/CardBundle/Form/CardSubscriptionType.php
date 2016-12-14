<?php

namespace CardBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CardSubscriptionType extends AbstractType
{

    private $dataClass;

    public function __construct()
    {
        $this->dataClass = 'CardBundle\Entity\CardSubscription';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cardNumber', ChoiceType::class, array(
                'choices'  => array(
                    'Abonnement 1 semaine, 22,15€' => 'week',
                    'Abonnement 1 mois, 73€' => 'month',
                    'Abonnement 1 an, 803€' => 'annual',
                ),
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