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

    public function viewAction()
    {
        return $this->render('ARLouvreBundle:Resa:view.html.twig');
    }

    public function searchAction()
    {
        return $this->render('ARLouvreBundle:Resa:search.html.twig');
    }
}