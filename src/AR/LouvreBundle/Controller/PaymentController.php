<?php

namespace AR\LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends Controller
{
    public function checkoutAction($resaCode, Request $request)
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

        //on calcul le prix total de cette réservation
        $outilsResa->calculPrixTotal($resa);

        //traitement du paiement avec le service stripe
        if($request->isMethod('POST'))
        {

            //on utilise le service stripeClient pour le paiement
            //si le paiement est réussit on finalise la réservation
            //et on rédirige vers le recap
            //TODO message de succès à ajouter
            if($this->get('service_container')->get('ar_louvre.stripeclient')->charge($request, $resa))
            {
                $outilsResa->finalizeReservation($resa);
                //TODO ajouter le code de réservation
                return $this->redirectToRoute('louvre_resa_voir');
            }
        }

        return $this->render('ARLouvreBundle:Payment:checkout.html.twig', array(
            'resa' => $resa,
            'public_key' => $this->getParameter('stripe_public_key')
        ));
    }
}
