<?php

namespace AR\LouvreBundle\Services\OutilsResa;


use AR\LouvreBundle\Entity\Reservation;

class AROutilsResa
{
    private $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * retourne une réservation en fonction de son code
     * si la réservation n'existe pas, retourne une nouvelle réservation
     *
     * @param $resaCode
     * @return \AR\LouvreBundle\Entity\Reservation|null|object
     */
    public function getResa($resaCode)
    {

        $resa = null;

        if ($resaCode !== null )
        {
            $resa = $this->em->getRepository('ARLouvreBundle:Reservation')->findOneBy(array(
                'resaCode' => $resaCode
            ));
        }

        return $resa;
    }

    /**
     * initialisation d'une réservation, avec la date du jour et email vide
     * un identifiant unique lui est affecté via le construction de la reservation
     *
     * @return Reservation
     */
    public function setNewResa()
    {

        $resa = new Reservation();
        $resa->setDateresa(new \DateTime());
        $resa->setEmail('');

        return $resa;
    }

    /**
     * @param Reservation $resa
     * @return bool
     */
    public function persistAndFlushResa(\AR\LouvreBundle\Entity\Reservation $resa)
    {

        //TODO gestion des erreurs
        $this->em->persist($resa);
        $this->em->flush();

        return true;
    }
}