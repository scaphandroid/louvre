<?php
/**
 * Created by PhpStorm.
 * User: alexis
 * Date: 17/10/2016
 * Time: 09:30
 */

use AR\LouvreBundle\Entity\Billet;
use AR\LouvreBundle\Entity\Reservation;
use AR\LouvreBundle\Services\OutilsResa\AROutilsResa;

describe('test calcul du prix total', function(){

    it('on vérifie que les prix des billets s\'additionnent', function(){

        //on crée trois billets avec différents prix
        $billet1 = new Billet();
        $billet2 = new Billet();
        $billet3 = new Billet();

        //on assigne un prix à chaque billet
        $billet1 -> setPrix(12);
        $billet2->setPrix(16);
        $billet3 -> setPrix(16);

        //on assigne ces billets à une réservation
        $resa = new Reservation();
        $resa->addBillet($billet1);
        $resa->addBillet($billet2);
        $resa->addBillet($billet3);

        $outilsResa = new AROutilsResa();

        expect(
            $outilsResa->calculPrixTotal($resa)
        )->toBe(48);
    });
});
