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
}
