<?php

namespace AR\LouvreBundle\Services\OutilsResa;

use AR\LouvreBundle\Entity\Reservation;
use AR\LouvreBundle\Entity\Billet;
use DateTime;
use Symfony\Component\Config\Definition\Exception\Exception;


class AROutilsResa
{
    private $em;
    private $outilsBillets;
    private $templating;
    private $mailer;
    private $session;
    private $nbBilletMaxParResa;
    private $nbBilletsMaxParJour;


    public function __construct(\Doctrine\ORM\EntityManager $em,
                                \AR\LouvreBundle\Services\OutilsBillets\AROutilsBillets $outilsBillets,
                                \Symfony\Bundle\TwigBundle\TwigEngine $templating,
                                \Swift_Mailer $mailer,
                                \Symfony\Component\HttpFoundation\Session\Session $session,
                                $nbBilletsMaxParResa,
                                $nbBilletsMaxParJour
    )
    {

        $this->em = $em;
        $this->outilsBillets = $outilsBillets;
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->session = $session;
        $this->nbBilletMaxParResa = $nbBilletsMaxParResa;
        $this->nbBilletsMaxParJour = $nbBilletsMaxParJour;
        //TODO nécessaire ??????
        //$this->session->start();
        //TODO placer heure limite journée dans les paramètres
    }

    /**
     * Récupère la réservation en cours dans la session
     * ou crée une nouvelle réservation en session si il n'y en a pas
     *
     * @return \AR\LouvreBundle\Entity\Reservation
     */
    public function initResa()
    {

        $resa = $this->session->get('resa');

        if ($this->session->get('resa') === null)
        {
            $resa = new Reservation();
        }

        return $resa;
    }

    public function validResa(Reservation $resa)
    {

        $reservationValide = true;
        $dateResa = $resa->getDateresa();
        $dateCourante = new DateTime();

        //TODO penser à l'heure de fermeture pour le jour meme également !

        //vérifie si on ne veut pas une réservation journée
        //pour le jour même passé 14h
        //enregistre un message d'erreur si ce n'est pas le cas
        if(!$resa->getDemijournee()
            && $dateResa->format('Ymd') === $dateCourante->format('Ymd')
            && $dateCourante->format('H') >= 14)
        {
            $this->session->getFlashBag()->add('erreurJournée', 'Vous ne pouvez sélectionner une réservation journée pour le jour même après 14h!');
            $reservationValide = false;
        }

        // controle si on ne demande pas plus de billets qu'autorisés par réservation ou moins de 1 billet
        // controle la disponibilité
        // enregistre un message d'erreur selon le cas
        $billetsDispo = $this->getDispo($resa->getDateresa());
        $nbBilletsDemandes = $resa->getNbBillets();
        if ($nbBilletsDemandes < 1)
        {
            $this->session->getFlashBag()->add('erreurDispo', "On ne peut réserver moins de 1 billet..");
            $reservationValide = false;
        }
        elseif ($nbBilletsDemandes > $this->nbBilletMaxParResa)
        {
            $this->session->getFlashBag()->add('erreurDispo', "Désolé, on ne peut réserver plus de ".$this->nbBilletMaxParResa." billets à la fois.");
            $reservationValide = false;
        }
        elseif( $billetsDispo < 1){
            $this->session->getFlashBag()->add('erreurDispo', "Désolé, il n'y a plus de billet disponible à la date demandée!");
            $reservationValide = false;
        }
        elseif( $billetsDispo < $nbBilletsDemandes)
        {
            $this->session->getFlashBag()->add('erreurDispo', "Désolé, seulement ".$billetsDispo." billet(s) disponibles à la date demandée!");
            $reservationValide = false;
        }

        //on persiste la réservation , afin d'être à jour au niveau des disponibilités
        //on l'enregistre en session pour la récupérer à l'étape suivante
        //on enregistre un message d'erreur en cas d'échec
        try
        {
            $this->session->set('resa', $resa);
            $this->em->persist($resa);
            $this->em->flush();
        }
        catch(Exception $e)
        {
            $this->session->getFlashBag()->add('erreurInterne', "Une erreur interne s'est produite, merci de réessayer.");
            $reservationValide = false;
        }

        return $reservationValide;
    }

    public function getDispo(DateTime $date)
    {


        return 1000;
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
            //$billet->setReservation($resa);
            $resa->addBillet($billet);
        }
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

    public function createNewResaFromExisting(\AR\LouvreBundle\Entity\Reservation $resa)
    {
        $newResa = $this->initResa();

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
     * @param \AR\LouvreBundle\Entity\Reservation $resa
     * @return bool
     */
    public function persistAndFlushResa(\AR\LouvreBundle\Entity\Reservation $resa)
    {

        //TODO gestion des erreurs
        $this->em->persist($resa);
        $this->em->flush();

        return true;
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

    public function calculPrixTotal(\AR\LouvreBundle\Entity\Reservation $resa)
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

    public function recEmail(\AR\LouvreBundle\Entity\Reservation $resa, $email)
    {
        $resa->setEmail($email);
        $this->em->persist($resa);
        $this->em->flush();
    }

    public function sendCOnfirmationMail(\AR\LouvreBundle\Entity\Reservation $resa)
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

        $this->mailer->send($message);
    }
}