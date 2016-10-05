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

        // récupération d'une éventuelle réservation en cours
        // si pas de réservation en cours, création d'une nouvelle réservation
        $resa = $outilsResa->initResa($resaCode, true);

        // création du formulaire associé à cette réservation + requête
        $form = $this->createForm(ReservationType::class, $resa);
        $form->handleRequest($request);

        // action lors de la soumission du formulaire
        if($form->isSubmitted() && $form->isValid()){

            if($outilsResa->validResa($resa))
            {
                //après validation, transfert vers l'étape suivante avec les paramètres de la résa
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

        // on recupère la réservation en cours,
        // l'argument false indique qu'il n'est pas possible de créer une nouvelle réservation à cette étape
        $resa = $outilsResa->initResa($resaCode, false);

        //si la réservatio n'est pas valide ou trouvée, initResa aura retourné null
        //on renvoie alors à l'étape d'initialisation
        if($resa === null)
        {
           return $this->redirectToRoute('louvre_resa_initialiser');
        }

        //on ajoute le nombre de billets voulus à la réservation
        $outilsResa->addBillets($resa);

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
        //pour test
        session_destroy();
        return $this->render('ARLouvreBundle:Resa:rechercherResa.html.twig');
    }
}