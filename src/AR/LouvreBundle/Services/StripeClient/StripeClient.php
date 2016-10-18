<?php

namespace AR\LouvreBundle\Services\StripeClient ;

use AR\LouvreBundle\Entity\Reservation;
use AR\LouvreBundle\Services\OutilsResa\AROutilsResa;
use Symfony\Component\HttpFoundation\Request;

class StripeClient
{

    private $outilsResa ;
    private $session;

    public function __construct($secretKey, AROutilsResa $outilsResa, \Symfony\Component\HttpFoundation\Session\Session $session)
    {
        \Stripe\Stripe::setApiKey($secretKey);
        $this->outilsResa = $outilsResa;
        $this->session = $session;
    }

    /**
     * procède au paiement de la réservation via stripe
     * si le paiement est réussi on enregistre dans la réservation l'email donné dans stripe
     *
     * @param Request $request
     * @param Reservation $resa
     * @return bool
     */
    public function charge(Request $request, Reservation $resa)
    {

        $token = $request->request->get('stripeToken');

        try
        {
            \Stripe\Charge::create(array(
                "amount" => $resa->getPrixTotal() * 100,
                "currency" => "eur",
                "source" => $token,
                "description" => "Réservation musée du Louvre"
            ));

            $stripeMail = \Stripe\Token::retrieve($token)->email;

            $resa->setEmail($stripeMail);

            return true;
        }
        catch (\Stripe\Error\Card $e)
        {

            $this->session->getFlashBag()->add('erreurPayment', 'Le paiement a échoué ! Merci de réessayer');
            return false;
        }

    }

}