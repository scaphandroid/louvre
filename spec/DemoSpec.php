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