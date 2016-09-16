<?php

namespace AR\LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PaymentController extends Controller
{
    public function checkoutAction()
    {
        return $this->render('ARLouvreBundle:Payment:checkout.html.twig');
    }
}
