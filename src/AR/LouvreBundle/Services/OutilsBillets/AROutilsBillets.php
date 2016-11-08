<?php
/**
 * Created by PhpStorm.
 * User: alexis
 * Date: 25/09/2016
 * Time: 18:56
 */

namespace AR\LouvreBundle\Services\OutilsBillets;


use AR\LouvreBundle\Entity\Billet;

class AROutilsBillets
{

    //ces valeurs sont stockées dans les paramètres et récupérées via le constructeur
    private $ageMaxGratuit;
    private $ageMaxEnfant;
    private $tarifEnfant;
    private $ageMinSenior;
    private $tarifSenior;
    private $tarifNormal;
    private $tarifReduit;

    /**
     * AROutilsBillets constructor.
     * @param $ageMaxGratuit
     * @param $ageMaxEnfant
     * @param $tarifEnfant
     * @param $ageMinSenior
     * @param $tarifSenior
     * @param $tarifNormal
     * @param $tarifReduit
     */
    public function __construct($ageMaxGratuit, $ageMaxEnfant, $tarifEnfant, $ageMinSenior, $tarifSenior, $tarifNormal, $tarifReduit)
    {
        $this->ageMaxGratuit = $ageMaxGratuit;
        $this->ageMaxEnfant = $ageMaxEnfant;
        $this->tarifEnfant = $tarifEnfant;
        $this->ageMinSenior = $ageMinSenior;
        $this->tarifSenior = $tarifSenior;
        $this->tarifNormal = $tarifNormal;
        $this->tarifReduit = $tarifReduit;
    }

    /**
     * retourne le tarif du billet en fonction de la date de naissance
     *
     *
     * @param Billet $billet
     * @return boolean
     * @internal param $dateNaissance
     */
    public function calculPrix(Billet $billet){

        $dateNaissance = $billet->getDateNaissance();

        $age = $this->calculAge($dateNaissance);

        if ( $age <= $this->ageMaxGratuit ){
            $prix = 0;
        }
        elseif ( $age <= $this->ageMaxEnfant )
        {
            $prix = $this->tarifEnfant;
        }
        elseif ( $billet->getTarifReduit() )
        {
            $prix = $this->tarifReduit;
        }
        elseif( $age >= $this->ageMinSenior)
        {
            $prix = $this->tarifSenior;
        }
        else
        {
            $prix = $this->tarifNormal;
        }

        $billet->setPrix($prix);

        return true;
    }

    /**
     * retourne l'age en fonction de la date de naissance en datetime
     *
     * @param datetime $dateNaissance
     * @return int $age
     */
    public function calculAge($dateNaissance){

        $age = idate('Y') - $dateNaissance->format('Y');

        return $age;
    }

    public function isAdulte(Billet $billet)
    {
        if($this->calculAge($billet->getDateNaissance()) > $this->ageMaxEnfant )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}