<?php

namespace AR\LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PaymentController extends Controller
{
    public function checkoutAction($resaCode)
    {

        $outilsResa = $this->get('service_container')->get('ar_louvre.outilsresa');

        $resa = $outilsResa->getResa($resaCode);

        if($resa === null || $resa->getEmail() !== '' ){
            return $this->redirectToRoute('louvre_resa_initialiser');
        }

         dump($resa);

        return $this->render('ARLouvreBundle:Payment:checkout.html.twig');
    }
}
