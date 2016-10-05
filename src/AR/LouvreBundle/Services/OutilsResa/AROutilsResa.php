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


    public function __construct
    (
        \Doctrine\ORM\EntityManager $em,
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
        //TODO placer heure limite journée dans les paramètres
    }


    /**
     * Récupère la réservation en cours via son code
     * ou crée une nouvelle réservation si il n'y en a pas
     * et si la création d'une nouvelle réservation est autorisée par le controlleur
     *
     *
     * @param $resaCode
     * @param boolean $nouvelleResaAcceptee
     * @return Reservation
     */
    public function initResa($resaCode, $nouvelleResaAcceptee)
    {

        $resa = null;

        if ($resaCode !== null )
        {
            $resa = $this->em->getRepository('ARLouvreBundle:Reservation')->findOneBy(array(
                'resaCode' => $resaCode
            ));
        }

        // si le controlleur permet la création d'une nouvelle réservation
        if ($resa === null && $nouvelleResaAcceptee)
        {
            $resa = new Reservation();
        }
        //si le controlleur ne permet pas la création d'une nouvelle réservation (2e et 3e étape)
        //ou si il s'agit d'une réservation déjà validée (email remplit)
        //on retourne un null
        elseif($resa === null || $resa->getEmail() !== '')
        {
            return null;
        }

        //on ajoute d'éventuels billets présents en session
        $this->getBilletInSession($resa);

        return $resa;
    }

    /**
     * Vérifie si la révervation est valide (jour demandé, disponibilité..),
     * la persiste dans ce cas,
     * enregistre des messages d'information  dans le flash bag en cas d'erreur
     *
     * @param Reservation $resa
     * @return bool
     */
    public function validResa(Reservation $resa)
    {

        $reservationValide = true;
        $dateResa = $resa->getDateresa();
        $dateCourante = new DateTime();

        //TODO penser à l'heure de fermeture pour le jour meme également !

        //TODO vérifier également qu'on ne veut pas un billet pour une journée précédente !

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

        //On récupère le nbre de billets disponible pour la date du jour
        $nbBilletsReserves = $this->em
            ->getRepository('ARLouvreBundle:Reservation')
            ->sumBilletsReserves($resa->getDateresa(), $resa->getResaCode())
        ;
        $billetsDispo = $this->nbBilletsMaxParJour - $nbBilletsReserves;

        // les conditions suivantes controlent la disponibilité
        // on enregistre un message d'erreur selon le cas
        $nbBilletsDemandes = $resa->getNbBillets();
        //controle si on en demande pas moins de 1 billet
        if ($nbBilletsDemandes < 1)
        {
            $this->session->getFlashBag()->add('erreurDispo', "On ne peut réserver moins de 1 billet..");
            $reservationValide = false;
        }
        //controle si on ne demande pas plus de billets que la limite par réservation
        elseif ($nbBilletsDemandes > $this->nbBilletMaxParResa)
        {
            $this->session->getFlashBag()->add('erreurDispo', "Désolé, on ne peut réserver plus de ".$this->nbBilletMaxParResa." billets à la fois.");
            $reservationValide = false;
        }
        //si plus aucun billet n'est disponible
        elseif( $billetsDispo < 1){
            $this->session->getFlashBag()->add('erreurDispo', "Désolé, il n'y a plus de billet disponible à la date demandée!");
            $reservationValide = false;
        }
        //si il reste moins de billets que le nbre demandé on indique le nombre de billets restants
        elseif( $billetsDispo < $nbBilletsDemandes)
        {
            $this->session->getFlashBag()->add('erreurDispo', "Désolé, seulement ".$billetsDispo." billet(s) disponibles à la date demandée!");
            $reservationValide = false;
        }

        //si la réservation n'est pas valide, on s'arrête ici
        if(!$reservationValide)
        {
            return false;
        }

        //si la réservation est valide
        //on persiste la réservation , afin d'être à jour au niveau des disponibilités
        // et de la récupérer à l'étape suivante
        //on enregistre un message d'erreur en cas d'échec
        try
        {
            //si la réservation a des billets on les enregistre dans la session
            //mais on ne les persiste pas à cette étape
            //on les détache donc de la réservation
            if($resa->getBillets()[0] !== null)
            {
                $billetsEnCours = array();
                foreach ($resa->getBillets() as $billet)
                {
                    //on enregistre le prix du billet
                    $this->outilsBillets->calculPrix($billet);
                    array_push($billetsEnCours, $billet);
                    $resa->removeBillet($billet);
                }
                $this->session->set('billets', $billetsEnCours);
            }
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


    /**
     * @param Reservation $resa
     */
    public function getBilletInSession(Reservation $resa)
    {

        $billetsEnSession = $this->session->get('billets');

        //on vérifie si on a des billets en session
        // et ,par sécurité, si il correspondent bien à la réservation en cours
        if(
            $billetsEnSession[0] !== null
            &&
            $billetsEnSession[0]->getReservation()->getResaCode() === $resa->getResaCode() )
        {
            //si c'est le cas on les ajoute à la réservation en cours
            foreach ($billetsEnSession as $billet)
            {
                $resa->addBillet($billet);
            }
        }
    }

    /**
     * ajoute ou supprime des billets à une réservation
     * suivant le nbBillets de la réservation
     *
     * @param $resa
     */
    public function updateBillets(\AR\LouvreBundle\Entity\Reservation $resa)
    {

        //on compare le nbre de billets déjà présents dans la réservation à son nbBillets voulu
        $nbBilletsEnregistres = count($resa->getBillets());
        $nbBilletsAAjouter = $resa->getNbBillets() - $nbBilletsEnregistres;

        //si la réservation contient autant de billets que son nbBillets, on a pas besoin d'en ajouter
        if($nbBilletsAAjouter === 0)
        {
            return;
        }
        //si la réservation contient moins de billet que désiré, on ajoute le nb de billets restants
        if($nbBilletsAAjouter > 0)
        {
            for($i = 0 ; $i < $nbBilletsAAjouter ; $i++){
                $billet = new Billet();
                $billet->setReservation($resa);
                $resa->addBillet($billet);
            }
        }
        //si la réservation contient plus de billets, on supprime les billets en trop en partant du dernier
        if($nbBilletsAAjouter < 0)
        {

            for($j = 0 ; $j < -$nbBilletsAAjouter ; $j++ )
            {
                $resa->removeBillet($resa->getBillets()[$nbBilletsEnregistres - (1 + $j)]);
            }
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
        $newResa = new Reservation();

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