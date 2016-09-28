<?php

namespace AR\LouvreBundle\Services\StripeClient ;

use AR\LouvreBundle\Entity\Reservation;
use AR\LouvreBundle\Services\OutilsResa\AROutilsResa;
use Symfony\Component\HttpFoundation\Request;

class StripeClient
{

    private $outilsResa ;

    public function __construct($secretKey, AROutilsResa $outilsResa)
    {
        \Stripe\Stripe::setApiKey($secretKey);
        $this->outilsResa = $outilsResa;
    }

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

            $this->outilsResa->recEmail($resa, $stripeMail);

            return true;
        }
        catch (\Stripe\Error\Card $e)
        {
            return false;
            //TODO gérer les autres types d'erreurs ?
        }

    }

}