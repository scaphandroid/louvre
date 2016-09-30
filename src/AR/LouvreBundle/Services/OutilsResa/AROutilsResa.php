<?php

namespace AR\LouvreBundle\Services\OutilsResa;

//TODO moche !
use AR\LouvreBundle\Entity\Billet;
use AR\LouvreBundle\Entity\Reservation;

class AROutilsResa
{
    private $em;
    private $outilsBillets;
    private $templating;

    public function __construct(\Doctrine\ORM\EntityManager $em, \AR\LouvreBundle\Services\OutilsBillets\AROutilsBillets $outilsBillets, \Symfony\Bundle\TwigBundle\TwigEngine $templating)
    {
        $this->em = $em;
        $this->outilsBillets = $outilsBillets;
        $this->templating = $templating;
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

    public function createNewResaFromExisting(Reservation $resa)
    {
        $newResa = $this->setNewResa();

        //on copie les propriétés d'initialisation de la résa
        $newResa->setNbBillets($resa->getNbBillets());
        $newResa->setDateresa($resa->getDateresa());
        $newResa->setDemijournee($resa->getDemijournee());

        //on enregistre cette nouvelle réservation en bbd
        $this->em->persist($newResa);
        $this->em->flush();

        //on ajoute les billets de la résa précédente à la nouvelle résa
        foreach ($resa->getBillets() as $billet)
        {
            $newResa->addBillet($billet);
            $billet->setReservation($newResa);
        }
        return $newResa;
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
            $billet->setReservation($resa);
            $this->outilsBillets->calculPrix($billet);
            $this->em->persist($billet);
        }

        //pas besoin de persister la réservation, elle est déjà suivie par Doctrine
        $this->em->flush();
    }

    public function calculPrixTotal(Reservation $resa)
    {

        $prixTotal = 0;

        foreach($resa->getBillets() as $billet) {
            $prixTotal += $billet->getPrix();
        }

        if($resa->getDemijournee()){
            $prixTotal = $prixTotal / 2 ;
        }

        $resa->setPrixTotal($prixTotal);
    }

    public function recEmail(Reservation $resa, $email)
    {
        $resa->setEmail($email);
        $this->em->persist($resa);
        $this->em->flush();
    }

    public function sendCOnfirmationMail(Reservation $resa)
    {
        $body = $this->templating->render('ARLouvreBundle:Resa:mailConfirmation.html.twig', array(
            'resa' => $resa
        ));

        $message = \Swift_Message::newInstance()
            ->setSubject('Confirmation de votre réservation au Musée du Louvre')
            ->setFrom('tobedetermined@louvre.fr')
            ->setTo($resa->getEmail())
            ->setBody(
                $body,
                'text/html'
            )
        ;

        //TODO to be sent..
        dump($message);
    }
}