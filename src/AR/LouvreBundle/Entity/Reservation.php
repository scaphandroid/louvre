<?php

namespace AR\LouvreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateresa", type="date")
     */
    private $dateresa;


    /**
     * @var boolean
     *
     * @ORM\Column(name="demijournee", type="boolean")
     */
    private $demijournee ;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_billets", type="integer")
     */
    private $nbBillets = 1;
    //TODO empêcher <1

    /**
     * @var string
     *
     * @ORM\Column(name="resa_code", type="string")
     */
    private $resaCode;

    /**
     * @ORM\OneToMany(targetEntity="AR\LouvreBundle\Entity\Billet", mappedBy="reservation")
     */
    private $billets;

    /**
     * @var int
     */
    private $prixTotal = 0;

    public function __construct(){
        $this->resaCode = md5(uniqid());
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
}
