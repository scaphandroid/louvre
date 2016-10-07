<?php

namespace AR\LouvreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reservation
 *
 * @ORM\Table(name="reservation")
 * @ORM\Entity(repositoryClass="AR\LouvreBundle\Repository\ReservationRepository")
 */
class Reservation
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datecreation", type="datetime")
     * @Assert\DateTime()
     * @Assert\GreaterThanOrEqual("today")
     */
    private $datecreation;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\Email()
     */
    private $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateresa", type="date")
     * @Assert\DateTime()
     * @Assert\GreaterThanOrEqual(
     *      "today",
     *      message = "merci ne pas choisir une date antérieure à celle du jour.."
     * )
     */
    private $dateresa;


    /**
     * @var boolean
     *
     * @ORM\Column(name="demijournee", type="boolean")
     * @Assert\Type(type="boolean")
     */
    private $demijournee ;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_billets", type="integer")
     *
     * @ASSERT\Range(
     *     min = 1,
     *     max = 20
     * )
     */
    private $nbBillets = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="resa_code", type="string")
     */
    private $resaCode;

    /**
     * @ORM\OneToMany(targetEntity="AR\LouvreBundle\Entity\Billet", mappedBy="reservation")
     * @Assert\Valid
     */
    private $billets;

    /**
     * @var int
     *
     */
    private $prixTotal = 0;

    /**
     * A la création d'une nouvelle réservation,
     * on crée son code,
     * on prend la date du jour comme date de réservation par défaut
     * et on enregistre le datetime de création de la réservation
     * on met un champ d'email vide
     *
     * Reservation constructor.
     */
    public function __construct(){
        $this->dateresa = new \DateTime();
        $this->datecreation = new \DateTime();
        //génération du code de réservation
        //4 chiffre, 4 lettres
        $str = "ABCDEFGHIJKLMNOPQRSTUVWYZ";
        $str = str_split(str_shuffle($str), 4)[0];
        $this->resaCode = rand(1000,9999).$str;
        $this->email = '' ;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Reservation
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set dateresa
     *
     * @param \DateTime $dateresa
     *
     * @return Reservation
     */
    public function setDateresa($dateresa)
    {
        $this->dateresa = $dateresa;

        return $this;
    }

    /**
     * Get dateresa
     *
     * @return \DateTime
     */
    public function getDateresa()
    {
        return $this->dateresa;
    }

    /**
     * Set demijournee
     *
     * @param boolean $demijournee
     *
     * @return Reservation
     */
    public function setDemijournee($demijournee)
    {
        $this->demijournee = $demijournee;

        return $this;
    }

    /**
     * Get demijournee
     *
     * @return boolean
     */
    public function getDemijournee()
    {
        return $this->demijournee;
    }

    /**
     * Set nbBillets
     *
     * @param integer $nbBillets
     *
     * @return Reservation
     */
    public function setNbBillets($nbBillets)
    {
        $this->nbBillets = $nbBillets;

        return $this;
    }

    /**
     * Get nbBillets
     *
     * @return integer
     */
    public function getNbBillets()
    {
        return $this->nbBillets;
    }

    /**
     * Set resaCode
     *
     * @param string $resaCode
     *
     * @return Reservation
     */
    public function setResaCode($resaCode)
    {
        $this->resaCode = $resaCode;

        return $this;
    }

    /**
     * Get resaCode
     *
     * @return string
     */
    public function getResaCode()
    {
        return $this->resaCode;
    }

    /**
     * Add billet
     *
     * @param \AR\LouvreBundle\Entity\Billet $billet
     *
     * @return Reservation
     */
    public function addBillet(\AR\LouvreBundle\Entity\Billet $billet)
    {
        $this->billets[] = $billet;

        return $this;
    }

    /**
     * Remove billet
     *
     * @param \AR\LouvreBundle\Entity\Billet $billet
     */
    public function removeBillet(\AR\LouvreBundle\Entity\Billet $billet)
    {
        $this->billets->removeElement($billet);
    }

    /**
     * Get billets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBillets()
    {
        return $this->billets;
    }

    public function setPrixTotal($prix)
    {
        $this->prixTotal = $prix;
    }

    public function getPrixTotal()
    {
        return $this->prixTotal;
    }

    /**
     * Set datecreation
     *
     * @param \DateTime $datecreation
     *
     * @return Reservation
     */
    public function setDatecreation($datecreation)
    {
        $this->datecreation = $datecreation;

        return $this;
    }

    /**
     * Get datecreation
     *
     * @return \DateTime
     */
    public function getDatecreation()
    {
        return $this->datecreation;
    }
}
