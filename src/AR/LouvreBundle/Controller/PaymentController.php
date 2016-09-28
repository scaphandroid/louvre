<?php

namespace AR\LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends Controller
{
    public function checkoutAction($resaCode, Request $request)
    {

        $outilsResa = $this->get('service_container')->get('ar_louvre.outilsresa');

        //on récupère la réservation en cours
        $resa = $outilsResa->getResa($resaCode);

        //TODO il faudra intégrer ICI le retour à l'étape précédente (voir branche retour)

        //si la réservation n'existe pas ou est déjà validée on retourne à l'initialisation
        if($resa === null || $resa->getEmail() !== '' ){
            return $this->redirectToRoute('louvre_resa_initialiser');
        }

        //on calcul le prix total de cette réservation
        $outilsResa->calculPrixTotal($resa);

        $form = $this->get('form.factory')->create('AR\LouvreBundle\Form\ResaMailType', $resa);

        dump($resa);

        //traitement du paiement avec le service stripe
        if($request->isMethod('POST'))
        {
            $stripeClient = $this->get('service_container')->get('ar_louvre.stripeclient');

            $stripeClient->charge($request, $resa);

            return $this->redirectToRoute('louvre_resa_voir');
        }

        return $this->render('ARLouvreBundle:Payment:checkout.html.twig', array(
            'resa' => $resa,
            'form' => $form->createView(),
            'public_key' => $this->getParameter('stripe_public_key')
        ));
    }
}
