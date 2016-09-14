<?php

namespace LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LouvreBundle:Default:index.html.twig');
    }
}
