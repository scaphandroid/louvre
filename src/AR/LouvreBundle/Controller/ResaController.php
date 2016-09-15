<?php

namespace AR\LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ResaController extends Controller
{
    public function initAction()
    {
        return $this->render('ARLouvreBundle:Resa:init.html.twig');
    }

    public function fillAction()
    {
        return $this->render('ARLouvreBundle:Resa:fill.html.twig');
    }

    public function editAction()
    {
        return $this->render('ARLouvreBundle:Resa:edit.html.twig');
    }
}