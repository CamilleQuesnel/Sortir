<?php

namespace App\DataFixtures;
use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Random\RandomException;

class CityFixtures extends Fixture
{
    public const CITY_REFERENCE_PREFIX = 'city_';

    /**
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        $citiesFromCampus = CampusFixtures::CAMPUSNAMES;

        // Récupérer les villes des spots
        $spotPlaces = SpotFixtures::PLACES;
        $cityNamesFromSpots = array_keys($spotPlaces);

//        $faker = Factory::create('fr_FR');
//        $json = file_get_contents('https://geo.api.gouv.fr/communes?fields=nom,codesPostaux&limit=20');
//        $cities = json_decode($json, true);
        // Transformer les villes des campus pour qu'elles aient la même structure
        $normalizedCampusCities = [];
        foreach ($citiesFromCampus as $c) {
            $normalizedCampusCities[$c['nom']] = $c['codesPostaux'];
        }

        // Ajouter les villes des spots si pas déjà présentes
        foreach ($cityNamesFromSpots as $cityName) {
            if (!isset($normalizedCampusCities[$cityName])) {
                // Tu choisis un code postal fictif pour celles-là (ou un par défaut)
                $normalizedCampusCities[$cityName] = '99999';
            }
        }

        // Création des entités City
        $i = 0;
        foreach ($normalizedCampusCities as $cityName => $zipCode) {
            $city = new City();
            $city->setName($cityName);
            $city->setZipCode($zipCode);
            $city->setIsActive((bool) random_int(0, 1));

            $manager->persist($city);
            $this->addReference(self::CITY_REFERENCE_PREFIX . strtolower($city->getName()), $city);

            $i++;
        }

        $manager->flush();
    }
}
