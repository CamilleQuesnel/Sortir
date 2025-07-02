<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Spot;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SpotFixtures extends Fixture implements DependentFixtureInterface
{
    public const SPOT_REFERENCE_PREFIX = 'spot_';

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 150; $i++) {
            $spot = new Spot();
            $spot->setName('Lieu ' . $faker->word());
            $spot->setAddress($faker->address());
            $spot->setLatitude($faker->latitude());
            $spot->setLongitude($faker->longitude());

            // Associer à une City existante (par index aléatoire de 0 à 4)
            $cityIndex = rand(0, 4);
            $spot->setCity($this->getReference(CityFixtures::CITY_REFERENCE_PREFIX . $cityIndex, CITY::class));

            $manager->persist($spot);

            // Référence pour HangoutFixtures
            $this->addReference(self::SPOT_REFERENCE_PREFIX . $i, $spot);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CityFixtures::class,
        ];
    }
}
