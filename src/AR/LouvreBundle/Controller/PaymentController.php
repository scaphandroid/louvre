<?php

namespace AR\LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PaymentController extends Controller
{
    public function checkoutAction($resaCode)
    {
        $em = $this->getDoctrine()->getManager();

        $resa = $em->getRepository('AR\LouvreBundle\Entity\Reservation')->findOneBy(array(
            'resaCode' => $resaCode
        ));

        if($resa === null || $resa->getEmail() !== '' ){
            return $this->redirectToRoute('louvre_resa_initialiser');
        }

         dump($resa);

        return $this->render('ARLouvreBundle:Payment:checkout.html.twig');
    }
}
