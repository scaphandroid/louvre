<?php

namespace AR\LouvreBundle\Services\StripeClient ;


class StripeClient
{

    public function __construct($secretKey)
    {
        \Stripe\Stripe::setApiKey($secretKey);
    }
}