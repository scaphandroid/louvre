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

        //traitement du paiement avec le service stripe
        if($request->isMethod('POST'))
        {
            $stripeClient = $this->get('service_container')->get('ar_louvre.stripeclient');

            //si le paiement est réussit on redirige vers le recap
            //et on envoie le mail de confirmation
            //TODO message de succès à ajouter
            if($stripeClient->charge($request, $resa))
            {
                $outilsResa->sendCOnfirmationMail($resa);
                return $this->redirectToRoute('louvre_resa_voir');
            }
            else
            {
                //en cas d'échec on invite à recommencer
                //TODO message à ajouter
                return $this->render('ARLouvreBundle:Payment:checkout.html.twig', array(
                    'resa' => $resa,
                    'public_key' => $this->getParameter('stripe_public_key')
                ));
            }
        }

        return $this->render('ARLouvreBundle:Payment:checkout.html.twig', array(
            'resa' => $resa,
            'public_key' => $this->getParameter('stripe_public_key')
        ));
    }
}
