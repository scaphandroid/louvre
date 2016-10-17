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
    private $heureLimiteDemiJournee;
    private $nbBilletsMaxParJour;


    public function __construct
    (
        \Doctrine\ORM\EntityManager $em,
        \AR\LouvreBundle\Services\OutilsBillets\AROutilsBillets $outilsBillets,
        \Symfony\Bundle\TwigBundle\TwigEngine $templating,
        \Swift_Mailer $mailer,
        \Symfony\Component\HttpFoundation\Session\Session $session,
        $heureLimiteDemiJournee,
        $nbBilletsMaxParJour
    )
    {
        $this->em = $em;
        $this->outilsBillets = $outilsBillets;
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->session = $session;
        $this->heureLimiteDemiJournee = $heureLimiteDemiJournee;
        $this->nbBilletsMaxParJour = $nbBilletsMaxParJour;
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
     * Vérifie si la révervation est valide (jour demandé, disponibilité, billets.. via fonctions dédiées),
     * la persiste dans ce cas,
     * enregistre des messages d'information  dans le flash bag en cas d'erreur
     *
     * @param Reservation $resa
     * @return bool
     */
    public function validResa(Reservation $resa)
    {

        $reservationValide = false;

        //on commence par valider si la date demandée est cohérente (demi - journée etc)
        //et on vérifie la disponibilité à cette date
        if($this->verifDateValide($resa) && $this->verifDispo($resa))
        {
            $reservationValide = true;
        }

        //si la réservation a des billets on les enregistre dans la session
        //si la fonction invalide les billets la réservation n'est pas valide
        if($resa->getBillets()[0] !== null && !$this->sauveBilletsInSession($resa))
        {
            $reservationValide = false;
        }

        //si la réservation n'est pas valide, on s'arrête ici
        if(!$reservationValide)
        {
            return false;
        }

        // si la réservation est valide
        // on persiste la réservation , afin d'être à jour au niveau des disponibilités
        // et de la récupérer à l'étape suivante
        // en cas d'échec on enregistre un message d'erreur
        try
        {
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


    public function verifDateValide(Reservation $resa)
    {

        $dateResa = $resa->getDateresa();
        $dateCourante = new DateTime("now", new \DateTimeZone('Europe/Paris'));
        $dateResaJoursMois = $dateResa->format('dm');

        //vérifie si on ne selectionne pas un jour de fermetuure
        //TODO mettre les dates dans des paramètres ?
        if( $dateResaJoursMois === "0105"
            || $dateResaJoursMois === "0111"
            || $dateResaJoursMois === "2512")
        {
            $this->session->getFlashBag()->add('erreurJournée', "Le musée n'est pas ouvert à cette date.");
            return false;
        }

        //vérifie si on ne veut pas une réservation journée
        //pour le jour même passé l'heure limite
        //enregistre un message d'erreur si ce n'est pas le cas
        if(!$resa->getDemijournee()
            && $dateResa->format('Ymd') === $dateCourante->format('Ymd')
            && $dateCourante->format('H') >= $this->heureLimiteDemiJournee)
        {
            $this->session->getFlashBag()->add('erreurJournée', 'Vous ne pouvez sélectionner une réservation journée pour le jour même après 14h!');
            return false;
        }
        else
        {
            return true;
        }
    }

    public function verifDispo(Reservation $resa)
    {

        $dispoOk = true;

        //On récupère le nbre de billets disponible pour la date du jour
        $nbBilletsReserves = $this->em
            ->getRepository('ARLouvreBundle:Reservation')
            ->sumBilletsReserves($resa->getDateresa(), $resa->getResaCode())
        ;
        $billetsDispo = $this->nbBilletsMaxParJour - $nbBilletsReserves;

        //(on vérifie si le nb de billets demandés est valide (>1 et < à la limite fixée par résa dans les entités)

        // les conditions suivantes controlent la disponibilité
        //si plus aucun billet n'est disponible
        if( $billetsDispo < 1){
            $this->session->getFlashBag()->add('erreurDispo', "Désolé, il n'y a plus de billet disponible à la date demandée!");
            $dispoOk = false;
        }
        //si il reste moins de billets que le nbre demandé on indique le nombre de billets restants
        elseif( $billetsDispo < $resa->getNbBillets())
        {
            $this->session->getFlashBag()->add('erreurDispo', "Désolé, seulement ".$billetsDispo." billet(s) disponibles à la date demandée!");
            $dispoOk = false;
        }

        return $dispoOk;
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
            count($billetsEnSession) > 0
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
     * enreigstre les billets en cours dans la session
     * et les détache de la réservation
     * (car les billets ne doivent être persistés qu'à la finalisation)
     *
     * @param Reservation $resa
     * @return bool
     */
    public function sauveBilletsInSession(Reservation $resa)
    {

        $billetsEnCours = array();
        $auMoinsUnBilletAdulte = false;
        foreach ($resa->getBillets() as $billet)
        {
            //on enregistre le prix du billet
            $this->outilsBillets->calculPrix($billet);
            if($this->outilsBillets->isAdulte($billet))
            {
                $auMoinsUnBilletAdulte = true;
            }
            array_push($billetsEnCours, $billet);
            $resa->removeBillet($billet);
        }
        //on enregistre les billets en session si il y a au moins un billet adulte, erreur sinon
        if($auMoinsUnBilletAdulte)
        {
            $this->session->set('billets', $billetsEnCours);
            return true;
        }
        else
        {
            $this->session->getFlashBag()->add('erreurBillet', "Désolé, les enfants de moins de 12 ans doivent être accompagnés.");
            return false;
        }
    }

    /**
     * ajoute ou supprime des billets à une réservation
     * suivant le nbBillets de la réservation
     *
     * @param $resa
     */
    public function updateBillets(Reservation $resa)
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
     * retourne le prix total d'une réservation
     * en additionnant le prix de chaque billet
     * le prix sera divisé par deux si il s'agit d'une réservation demi-journée
     *
     * @param Reservation $resa
     */
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

    /**
     * stocke définitivement la réservation et ses billets en bdd
     * déclenche l'envoie du mail de confirmation
     * et enregistre le message de succès
     *
     * @param Reservation $resa
     */
    public function finalizeReservation(Reservation $resa)
    {
        //TODO prendre en compte de possibles erreurs ?

        //on persiste les billets et la réservation
        foreach ($resa->getBillets() as $billet)
        {
            $billet->setReservation($resa);
            $this->em->persist($billet);
        }
        $this->em->persist($resa);
        $this->em->flush();

        //on supprime les billets en session
        $this->session->remove('billets');

        //on envoie le mail de confirmation
        $this->sendCOnfirmationMail($resa);

        //on enregistre le message de succès
        $this->session->getFlashBag()->add('succes', 'Votre réservation est confirmée, un email de confirmation vient de vous être envoyé à '.$resa->getEmail().', il tiendra lieu de billet.');
    }

    /**
     * Prépare et envoie le message de confirmation d'une réservation
     *
     * @param Reservation $resa
     */
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

        $this->mailer->send($message);
    }
}