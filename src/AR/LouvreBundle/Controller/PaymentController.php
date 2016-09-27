<?php

namespace AR\LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PaymentController extends Controller
{
    public function checkoutAction($resaCode)
    {
        $outilsResa = $this->get('service_container')->get('ar_louvre.outilsresa');

        // on recupère la réservation en cours
        $resa = $outilsResa->getResa($resaCode);

        //si la réservation a un email non vide c'est qu'il s'agit d'une réservation finalisée
        // on ne doit pas pouvoir la modifier -> retour à la première étape
        // on retourne également à la première étape si la réservatio n'existe pas
        if($resa === null || $resa->getEmail() !== '' ){
            return $this->redirectToRoute('louvre_resa_initialiser');
        }

         dump($resa);

        return $this->render('ARLouvreBundle:Payment:checkout.html.twig', array(
            'resa' => $resa
        ));
    }
}
