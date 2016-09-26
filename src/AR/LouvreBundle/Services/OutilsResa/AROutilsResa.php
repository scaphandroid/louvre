<?php

namespace AR\LouvreBundle\Services\OutilsResa;

//TODO moche !
use AR\LouvreBundle\Entity\Billet;
use AR\LouvreBundle\Entity\Reservation;

class AROutilsResa
{
    private $em;
    private $outilsBillets;

    public function __construct(\Doctrine\ORM\EntityManager $em, \AR\LouvreBundle\Services\OutilsBillets\AROutilsBillets $outilsBillets)
    {
        $this->em = $em;
        $this->outilsBillets = $outilsBillets;
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

    /**
     * ajoute de nouveaux billets à une réservation
     * suivant le nbBillets de la réservation
     *
     * @param $resa
     */
    public function addNewBillets(\AR\LouvreBundle\Entity\Reservation $resa)
    {

        for($i = 0 ; $i < $resa->getNbBillets() ; $i++){
            $billet = new Billet();
            $billet->setReservation($resa);
            $resa->addBillet($billet);
        }
    }

    public function persistNewBilletsAndFlush(\AR\LouvreBundle\Entity\Reservation $resa)
    {

        foreach ($resa->getBillets() as $billet)
        {
            $billet->setPrix($this->outilsBillets->calculPrix($billet->getDateNaissance()));
            $this->em->persist($billet);
        }

        //pas besoin de persister la réservation, elle est déjà suivie par Doctrine
        $this->em->flush();
    }
}