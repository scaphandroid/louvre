<?php
/**
 * Created by PhpStorm.
 * User: alexis
 * Date: 11/10/2016
 * Time: 20:34
 */

describe('Demo', function(){

    it('should addition 1+1', function(){
        expect(1+1)->toBe(2);
    });
});

describe('tests d\'une réservation', function(){
    it('on crée une réservation et on lui ajoute un billet', function(){
        $resa = new \AR\LouvreBundle\Entity\Reservation();
        $resa->setNbBillets(1);
        expect(
            $resa->getNbBillets()
        )->toBe(1);
    });
});

describe('test calcul du prix total', function(){

    it('on vérifie que les prix des billets s\'additionnent', function(){

        //on crée trois billets avec différents prix
        $billet1 = new \AR\LouvreBundle\Entity\Billet();
        $billet2 = new \AR\LouvreBundle\Entity\Billet();
        $billet3 = new \AR\LouvreBundle\Entity\Billet();

        //on assigne un prix à chaque billet
        $billet1 -> setPrix(12);
        $billet2->setPrix(16);
        $billet3 -> setPrix(16);

        //on assigne ces billets à une réservation
        $resa = new \AR\LouvreBundle\Entity\Reservation();
        $resa->addBillet($billet1);
        $resa->addBillet($billet2);
        $resa->addBillet($billet3);

        allow('em')->toBeOk();
        allow('outilsBillets')->toBeOk();
        allow('mailer')->toBeOk();
        allow('session')->toBeOk();
        allow('heureLimiteDemiJournee')->toBe(14);
        allow('nbBilletsMaxParJour')->toBe(1000);

        $outilsResa = new \AR\LouvreBundle\Services\OutilsResa\AROutilsResa();

        expect(
            $outilsResa->calculPrixTotal($resa)
        )->toBe(48);
    });
});