<?php
/**
 * Created by PhpStorm.
 * User: alexis
 * Date: 17/10/2016
 * Time: 09:25
 */

use AR\LouvreBundle\Entity\Billet;
use AR\LouvreBundle\Services\OutilsBillets\AROutilsBillets;

describe('test du service outils billet', function(){

    beforeAll(function(){

        //on configure et appel le service outilsBillets qui sera utilisé dans ces tests

        //paramètres de tarification
        $ageMaxGratuit =  3 ;
        $ageMaxEnfant = 11 ;
        $tarifEnfant =  8 ;
        $ageMinSenior = 60 ;
        $tarifSenior = 12 ;
        $tarifNormal = 16 ;
        $tarifReduit = 10 ;

        //serice outils billets
        $this->outilsBillets = new AROutilsBillets(
            $ageMaxGratuit,
            $ageMaxEnfant,
            $tarifEnfant,
            $ageMinSenior,
            $tarifSenior,
            $tarifNormal,
            $tarifReduit
        );
    });

    it('vérifier que la date de naissance est bien calculée', function(){

        //personne de 62 ans
        $dateNaissance = new DateTime('1954-10-14');
        expect(
            $this->outilsBillets->calculAge($dateNaissance)
        )->toBe(62);
    });

    it('vérifie que le prix du billet est bien calculé', function(){

        $billet = new Billet();

        //enfant de 3 ans
        $billet->setDateNaissance(new DateTime('2013-10-14'));
        $this->outilsBillets->calculPrix($billet);
        expect(
            $billet->getPrix()
        )->toBe(0);

        //enfant de 11 ans
        $billet->setDateNaissance(new DateTime('2005-10-14'));
        $this->outilsBillets->calculPrix($billet);
        expect(
            $billet->getPrix()
        )->toBe(8);

        //senior de 62 ans
        $billet->setDateNaissance(new DateTime('1954-10-14'));
        $this->outilsBillets->calculPrix($billet);
        expect(
            $billet->getPrix()
        )->toBe(12);
    });

    it('on vérifie le test d\' un billet adulte ou non', function(){

        $billet = new Billet();

        //c'est un enfant le test doit retourner false
        $billet->setDateNaissance(new DateTime('2013-10-14'));
        expect(
            $this->outilsBillets->isAdulte($billet)
        )->toBe(false);

        //c'est un adulte (> 11 ans) le test doit retourner true
        $billet->setDateNaissance(new DateTime('2002-10-14'));
        expect(
            $this->outilsBillets->isAdulte($billet)
        )->toBe(true);
    });
});