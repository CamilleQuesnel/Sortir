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
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $city = new City();
            $city->setName($faker->city());
            $city->setZipCode($faker->postcode());

            $manager->persist($city);

            // Référence pour SpotFixtures
            $this->addReference(self::CITY_REFERENCE_PREFIX . $i, $city);
        }

        $manager->flush();
    }
}
