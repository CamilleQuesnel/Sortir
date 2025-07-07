<?php

namespace App\DataFixtures;
use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CityFixtures extends Fixture
{
    public const CITY_REFERENCE_PREFIX = 'city_';

    public function load(ObjectManager $manager): void
    {
        $cities = CampusFixtures::CAMPUSNAMES;

//        $faker = Factory::create('fr_FR');
//        $json = file_get_contents('https://geo.api.gouv.fr/communes?fields=nom,codesPostaux&limit=20');
//        $cities = json_decode($json, true);
        $i=0;
        foreach ($cities as $c) {
            $city = new City();
            $city->setName($c['nom']);
            $city->setZipCode($c['codesPostaux']);

            $manager->persist($city);

            // Référence pour SpotFixtures
            $this->addReference(self::CITY_REFERENCE_PREFIX . $i, $city);
            $i++;
        }

        $manager->flush();
    }
}
