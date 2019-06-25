<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\Country;

class Datas{

    // Retrieve doctrine from the constructor
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * return country list
     */
    public function getCountries()
    {
        // Tableau des pays
        $repo = $this->doctrine->getRepository(Country::class);
        return $repo->findAll();
    }
}