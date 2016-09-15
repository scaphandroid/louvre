<?php

namespace AR\LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ARLouvreBundle:Default:index.html.twig');
    }
}
