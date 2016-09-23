<?php

namespace AR\LouvreBundle\Controller;

use AR\LouvreBundle\Entity\Billet;
use AR\LouvreBundle\Form\listeBilletsType;
use Symfony\Component\HttpFoundation\Request;
use AR\LouvreBundle\Entity\Reservation;
use AR\LouvreBundle\Form\ReservationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ResaController extends Controller
{

    //controleur pour l'initialisation de la réservation : choix date , type de bilelt, nb billets
    /**
     * @param Request $request
     * @param $resaCode
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function initialiserReservationAction(Request $request, $resaCode)
    {

        $em = $this->getDoctrine()->getManager();

        $resa = null;

        //si il s'agit d'une réservation en cours on récupère les données
        if($resaCode !== null){
            $resa = $em->getRepository('ARLouvreBundle:Reservation')->findOneBy(array(
                'resaCode' => $resaCode
            ));
        }

        // si pas de réservation en cours, ou résa non trouvée
        if ($resa === null){
            // initialisation d'une réservation, avec la date du jour et email vide
            // un identifiant unique lui est affecté via le construction de la reservation
            $resa = new Reservation();
            $resa->setDateresa(new \DateTime());
            $resa->setEmail('');
        }

        //TODO validation de la date du jour en fonction des dispo ?

        // création du formulaire associé à cette réservation + requête
        $form = $this->createForm(ReservationType::class, $resa);
        $form->handleRequest($request);

        // action lors de la soumission du formulaire
        if($form->isSubmitted() && $form->isValid()){

            //TODO ici on validera si il reste assez de places, et si la demande est valide pour le jour même

            // une fois la résa validée, on la persiste pour la récupérer à l'étape suivante avec son id
            $em->persist($resa);
            $em->flush();

            //après validation, transfert vers l'étape suivante avec les paramètres de la réservation
            return $this->redirectToRoute('louvre_resa_completer', array(
                'resaCode' => $resa->getResaCode()
            ));
        }

        // pas de soumission, génération de la vue avec le formulaire
        return $this->render('ARLouvreBundle:Resa:initialiserResa.html.twig', array(
            'form' => $form->createView()
        ));
    }


    //controleur pour la secondé étape : on complète chaque billet
    /**
     * @param Request $request
     * @param $resaCode
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function completerReservationAction(Request $request, $resaCode)
    {

        $em = $this->getDoctrine()->getManager();


        // on recupère la réservation en cours avec son id
        $resa = $em->getRepository('ARLouvreBundle:Reservation')->findOneBy(array(
            'resaCode' => $resaCode
        ));

        //si la réservation ²a un email non vide c'est qu'il s'agit d'une réservation finalisée
        // on ne doit pas pouvoir la modifier -> retour à la première étape
        // on retourne également à la première étape si la réservatio n'existe pas
        if($resa === null || $resa->getEmail() !== '' ){
            return $this->redirectToRoute('louvre_resa_initialiser');
        }

        //TODO il faudra trouver une autre méthode pour ne pas stocler les réservations non finalisées??
        //on supprime la réservation en cours de la base de données afin de ne pas avoir de réservation non finalisée
        //si l'utilisateur ne finalise pas
        /*
        $em->remove($resa);
        $em->flush();
        */

        //on ajoute le nombre de billets voulus à la réservation
        for($i = 0 ; $i < $resa->getNbBillets() ; $i++){
            $billet = new Billet();
            $billet->setReservation($resa);
            $resa->addBillet($billet);
        }

        //génération du formulaire associé, et association à la requête
        $form = $this->get('form.factory')->create(listeBilletsType::class, $resa);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            //TODO validation à effectuer, et calcul du tarif à implenter dans les entités

            //on met à jour le nombre de billets de la réservation
            $resa->setNbBillets(count($resa->getNbBillets()));

            foreach ($resa->getBillets() as $billet)
            {
                $em->persist($billet);
            }

            //pas besoin de persister la réservation, elle est déjà suivie par Doctrine
            $em->flush();

            return $this->redirectToRoute('louvre_payment_checkout', array(
                'resaCode' => $resa->getResaCode()
            ));
        }

        return $this->render('ARLouvreBundle:Resa:completerResa.html.twig', array(
            'resa' => $resa,
            'form' => $form->createView()
        ));
    }


    public function voirReservationAction()
    {
        return $this->render('ARLouvreBundle:Resa:voirResa.html.twig');
    }


    public function rechercherReservationAction()
    {
        return $this->render('ARLouvreBundle:Resa:rechercherResa.html.twig');
    }
}