<?php

namespace AR\LouvreBundle\Services\OutilsResa;


class AROutilsResa
{
    private $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function recupererResa($resaCode){


    }
}