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
});
