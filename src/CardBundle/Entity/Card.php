<?php

namespace CardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="card")
 */
class Card
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    protected $cardNumber;

    /**
     * @ORM\OneToOne(targetEntity="UserBundle\Entity\User", mappedBy="card")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startValidity", type="datetime", nullable=true)
     */
    protected $startValidity;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endValidity", type="datetime", nullable=true)
     */
    protected $endValidity;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lastSubscription;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\File(
     *     mimeTypes={ "image/jpeg" },
     *     maxSize = "5M",
     *     maxSizeMessage = "Taille maximale de 5mo",
     *     mimeTypesMessage = "Uploader au format image svp"
     * )
     */
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceBundle\Entity\Invoice", mappedBy="card", cascade={"persist"})
     */
    public $invoices;


    public function __construct()
    {
        $this->invoices = new ArrayCollection();
    }

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
     * @return string
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * @param string $cardNumber
     */
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;
    }

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \DateTime
     */
    public function getStartValidity()
    {
        return $this->startValidity;
    }

    /**
     * @param \DateTime $startValidity
     */
    public function setStartValidity($startValidity)
    {
        $this->startValidity = $startValidity;
    }

    /**
     * @return \DateTime
     */
    public function getEndValidity()
    {
        return $this->endValidity;
    }

    /**
     * @param \DateTime $endValidity
     */
    public function setEndValidity($endValidity)
    {
        $this->endValidity = $endValidity;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    /**
     * @return mixed
     */
    public function getLastSubscription()
    {
        return $this->lastSubscription;
    }

    /**
     * @param mixed $lastSubscription
     */
    public function setLastSubscription($lastSubscription)
    {
        $this->lastSubscription = $lastSubscription;
    }

    /**
     * @return mixed
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * @param mixed $invoices
     */
    public function setInvoices($invoices)
    {
        $this->invoices = $invoices;
    }

    /**
     * @param $invoices
     */
    public function addInvoices($invoice)
    {
        $this->invoices[] = $invoice;
        return $this;
    }

}