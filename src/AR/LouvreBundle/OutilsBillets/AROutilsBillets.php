<?php
/**
 * Created by PhpStorm.
 * User: alexis
 * Date: 25/09/2016
 * Time: 18:56
 */

namespace AR\LouvreBundle\OutilsBillets;


class AROutilsBillets
{

    /**
     * retourne le tarif du billet en fonction de la date de naissance
     *
     *
     * @param $dateNaissance
     * @return double $prix
     */
    public function calculPrix($dateNaissance){

        $age = $this->calculAge($dateNaissance);

        //TODO sans doute pas le bon endroit pour stocker ces infos..
        //les tarifs,et age limites
        $ageMaxGratuit = 3;
        $ageMaxEnfant = 12;
        $tarifEnfant = 8;
        $ageMinSenior = 60;
        $tarifSenior = 12;
        $tarifNormal = 16;

        if ( $age <= $ageMaxGratuit ){
            $prix = 0;
        }
        elseif ( $age <= $ageMaxEnfant )
        {
            $prix = $tarifEnfant;
        }
        elseif( $age >= $ageMinSenior)
        {
            $prix = $tarifSenior;
        }
        else
        {
            $prix = $tarifNormal;
        }

        return $prix;
    }

    /**
     * retourne l'age en fonction de la date de naissance en datetime
     *
     * @param $dateNaissance
     * @return int $age
     */
    public function calculAge($dateNaissance){

        $age = idate('Y') - $dateNaissance->format('Y');

        return $age;
    }

}