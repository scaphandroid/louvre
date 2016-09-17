<?php

namespace AR\LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ResaController extends Controller
{
    public function initialiserReservationAction()
    {
        return $this->render('ARLouvreBundle:Resa:initialiserResa.html.twig');
    }

    public function completerReservationAction()
    {
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