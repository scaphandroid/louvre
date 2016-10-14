<?php
/**
 * Created by PhpStorm.
 * User: alexis
 * Date: 11/10/2016
 * Time: 20:34
 */

use AR\LouvreBundle\Entity\Billet;
use AR\LouvreBundle\Entity\Reservation;
use AR\LouvreBundle\Services\OutilsBillets\AROutilsBillets;

describe('Demo', function(){

    it('should addition 1+1', function(){
        expect(1+1)->toBe(2);
    });
});

describe('tests d\'une réservation', function(){
    it('on crée une réservation et on lui ajoute un billet', function(){
        $resa = new Reservation();
        $resa->setNbBillets(1);
        expect(
            $resa->getNbBillets()
        )->toBe(1);
    });
});

describe('test outils billet', function(){

    it('vérifie que le prix du billet est bien calculé', function(){

        //paramètres de tarification
        $ageMaxGratuit =  3 ;
        $ageMaxEnfant = 11 ;
        $tarifEnfant =  8 ;
        $ageMinSenior = 60 ;
        $tarifSenior = 12 ;
        $tarifNormal = 16 ;
        $tarifReduit = 10 ;

        //serice outils billets
        $outilsBillets = new AROutilsBillets(
            $ageMaxGratuit,
            $ageMaxEnfant,
            $tarifEnfant,
            $ageMinSenior,
            $tarifSenior,
            $tarifNormal,
            $tarifReduit
        );

        $billet = new Billet();

        //enfant de 3 ans
        $billet->setDateNaissance(new DateTime('2013-10-14'));
        $outilsBillets->calculPrix($billet);
        expect(
           $billet->getPrix()
        )->toBe(0);

        //enfant de 11 ans
        $billet->setDateNaissance(new DateTime('2005-10-14'));
        $outilsBillets->calculPrix($billet);
        expect(
            $billet->getPrix()
        )->toBe(8);
    });
});


describe('test calcul du prix total', function(){

    it('on vérifie que les prix des billets s\'additionnent', function(){

        //on crée trois billets avec différents prix
        $billet1 = new Billet;
        $billet2 = new Billet;
        $billet3 = new Billet;

        //on assigne un prix à chaque billet
        $billet1 -> setPrix(12);
        $billet2->setPrix(16);
        $billet3 -> setPrix(16);

        //on assigne ces billets à une réservation
        $resa = new Reservation();
        $resa->addBillet($billet1);
        $resa->addBillet($billet2);
        $resa->addBillet($billet3);

        $outilsResa = new \AR\LouvreBundle\Services\OutilsResa\AROutilsResa();

        expect(
            $outilsResa->calculPrixTotal($resa)
        )->toBe(48);
    });
});
