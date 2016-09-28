<?php

namespace AR\LouvreBundle\Services\StripeClient ;

use AR\LouvreBundle\Entity\Reservation;
use Symfony\Component\HttpFoundation\Request;

class StripeClient
{

    public function __construct($secretKey)
    {
        \Stripe\Stripe::setApiKey($secretKey);
    }

    public function charge(Request $request, Reservation $resa)
    {

        $token = $request->request->get('stripeToken');

        \Stripe\Charge::create(array(
            "amount" => $resa->getPrixTotal() * 100,
            "currency" => "eur",
            "source" => $token,
            "description" => "Votre réservation est validée"
        ));
    }

}