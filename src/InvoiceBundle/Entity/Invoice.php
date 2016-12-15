<?php

namespace InvoiceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="invoice")
 */
class Invoice
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="CardBundle\Entity\Card", inversedBy="invoices")
     * @ORM\JoinColumn(name="card_id", referencedColumnName="id")
     */
    protected $card;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $paymentDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endValiditySubscription", type="datetime", nullable=false)
     */
    protected $endValiditySubscription;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $subscriptionType;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @param mixed $paymentDate
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;
    }

    /**
     * @return mixed
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param mixed $card
     */
    public function setCard($card)
    {
        $this->card = $card;
    }

    /**
     * @return \DateTime
     */
    public function getEndValiditySubscription()
    {
        return $this->endValiditySubscription;
    }

    /**
     * @param \DateTime $endValiditySubscription
     */
    public function setEndValiditySubscription($endValiditySubscription)
    {
        $this->endValiditySubscription = $endValiditySubscription;
    }

    /**
     * @return string
     */
    public function getSubscriptionType()
    {
        return $this->subscriptionType;
    }

    /**
     * @param string $subscriptionType
     */
    public function setSubscriptionType($subscriptionType)
    {
        $this->subscriptionType = $subscriptionType;
    }

}