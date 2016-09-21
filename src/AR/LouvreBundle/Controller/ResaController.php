<?php

namespace AR\LouvreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AR\LouvreBundle\Entity\Reservation;
use AR\LouvreBundle\Form\ReservationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ResaController extends Controller
{
    public function initialiserReservationAction(Request $request)
    {

        $resa = new Reservation();

        //TODO validation de la date en fonction des dispos, et récupération dates disponibles ?
        $resa->setDateresa(new \DateTime());

        $form = $this->createForm(ReservationType::class, $resa);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            //TODO ici on validera si il reste assez de places ?

        }

        return $this->render('ARLouvreBundle:Resa:initialiserResa.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function completerReservationAction($resa)
    {

        var_dump($resa);

        return $this->render('ARLouvreBundle:Resa:completerResa.html.twig');
    }

    public function voirReservationAction()
    {
        return $this->render('ARLouvreBundle:Resa:voirResa.html.twig');
    }

    public function rechercherReservationAction()
    {
        return $this->render('ARLouvreBundle:Resa:rechercherResa.html.twig');
    }
}