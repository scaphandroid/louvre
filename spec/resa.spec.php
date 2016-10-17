<?php
/**
 * Created by PhpStorm.
 * User: alexis
 * Date: 11/10/2016
 * Time: 20:34
 */

use AR\LouvreBundle\Entity\Reservation;

describe('tests d\'une réservation', function(){

    it('on crée une réservation et on lui ajoute un billet', function(){
        $resa = new Reservation();
        $resa->setNbBillets(1);
        expect(
            $resa->getNbBillets()
        )->toBe(1);
    });

    /*
    it('on essaye de créer une réservation pour un jour antérieur à aujourd\'hui', function(){
        $resa = new Reservation();
        $today = new DateTime();
        $resa->setDateresa(new DateTime('2015-10-09'));
        expect(
            $resa->getDateresa()->format('Y')
        )->toBe($today->format('Y'));
    });
    */
});
