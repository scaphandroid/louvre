<?php

namespace AR\LouvreBundle\Controller;

use AR\LouvreBundle\Form\listeBilletsType;
use Symfony\Component\HttpFoundation\Request;
use AR\LouvreBundle\Form\ReservationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ResaController extends Controller
{

    /**
     * action pour l'initialisation de la réservation : choix date , type de bilelt, nb billets
     *
     * @param Request $request
     * @param $resaCode
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function initialiserReservationAction(Request $request, $resaCode)
    {

        //récupération du service outilsresa
        $outilsResa = $this->get('service_container')->get('ar_louvre.outilsresa');

        //récupération d'une éventuelle réservation en cours
        //TODO à supprimer puisqu'on utilisera la session implantée dans initResa
        $resa = $outilsResa->getResa($resaCode);

        // si pas de réservation en cours, ou résa non trouvée, création d'une nouvelle réservation
        if ($resa === null){
            $resa = $outilsResa->initResa();
        }

        // création du formulaire associé à cette réservation + requête
        $form = $this->createForm(ReservationType::class, $resa);
        $form->handleRequest($request);

        // action lors de la soumission du formulaire
        if($form->isSubmitted() && $form->isValid()){

            if($outilsResa->validResa($resa))
            {
                dump($resa);
                //après validation, transfert vers l'étape suivante avec les paramètres de la résa
                //TODO on utilisera la session pour récupérer la réservation
                return $this->redirectToRoute('louvre_resa_completer', array(
                    'resaCode' => $resa->getResaCode()
                ));
            }
        }

        // pas de soumission ou erreur, génération de la vue avec le formulaire
        return $this->render('ARLouvreBundle:Resa:initialiserResa.html.twig', array(
            'form' => $form->createView()
        ));
    }


    /**
     * action pour la secondé étape : on complète chaque billet
     *
     * @param Request $request
     * @param $resaCode
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function completerReservationAction(Request $request, $resaCode)
    {

        $outilsResa = $this->get('service_container')->get('ar_louvre.outilsresa');

        //pour test
        dump($outilsResa->initResa());

        // on recupère la réservation en cours
        $resa = $outilsResa->getResa($resaCode);

        //si la réservation ²a un email non vide c'est qu'il s'agit d'une réservation finalisée
        // on ne doit pas pouvoir la modifier -> retour à la première étape
        // on retourne également à la première étape si la réservatio n'existe pas
        if($resa === null || $resa->getEmail() !== '' ){
            return $this->redirectToRoute('louvre_resa_initialiser');
        }

        //si l'on vient de l'étape de payment c'est qu'au moins un billet est persisté
        if($resa->getBillets()[0] !== null && !$request->isMethod('POST'))
        {
            //dans ce cas on créé une nouvelle réservation à partir de celle en cours
            $resa = $outilsResa->createNewResaFromExisting($resa);
        }
        else
        {
            //sinon on ajoute le nombre de billets voulus à la réservation
            $outilsResa->addNewBillets($resa);
        }

        //génération du formulaire associé, et association à la requête
        $form = $this->get('form.factory')->create(listeBilletsType::class, $resa);
        $form->handleRequest($request);

        // action lors de la soumission du formulaire
        if($form->isSubmitted() && $form->isValid()){

            //TODO validation à effectuer

            //on met à jour le nombre de billets de la réservation
            $resa->setNbBillets(count($resa->getBillets()));

            //on persiste les billets
            $outilsResa->persistNewBilletsAndFlush($resa);

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