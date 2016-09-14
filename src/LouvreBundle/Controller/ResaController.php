<?php

namespace LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ResaController extends Controller
{
    public function initAction()
    {
        return $this->render('LouvreBundle:Resa:init.html.twig');
    }

    public function fillAction()
    {
        return $this->render('LouvreBundle:Resa:fill.html.twig');
    }

    public function editAction()
    {
        return $this->render('LouvreBundle:Resa:edit.html.twig');
    }
}