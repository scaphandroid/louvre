<?php

namespace AR\LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PaymentController extends Controller
{
    public function checkoutAction($resaCode)
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

        dump($form->createView());

        return $this->render('ARLouvreBundle:Payment:checkout.html.twig', array(
            'resa' => $resa,
            'form' => $form->createView()
        ));
    }
}
