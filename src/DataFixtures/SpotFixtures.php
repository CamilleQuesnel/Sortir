<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Spot;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SpotFixtures extends Fixture implements DependentFixtureInterface
{
    public const SPOT_REFERENCE_PREFIX = 'spot_';

    public const PLACES = [
        'Nantes' => [
            ['name' => 'Le Bar de Nantes', 'address' => '12 Rue Crébillon', 'latitude' => 47.2151, 'longitude' => -1.5536],
            ['name' => 'Bowling Nantes', 'address' => '20 Boulevard Jules Verne', 'latitude' => 47.2097, 'longitude' => -1.5563],
            ['name' => 'Le Café de la Paix', 'address' => '5 Place Royale', 'latitude' => 47.2144, 'longitude' => -1.5550],
            ['name' => 'Bar Le Saint-Félix', 'address' => '2 Rue de la Fosse', 'latitude' => 47.2130, 'longitude' => -1.5530],
            ['name' => 'Bowling Le Palace', 'address' => '8 Quai de la Fosse', 'latitude' => 47.2137, 'longitude' => -1.5534],
        ],
        'Rennes' => [
            ['name' => 'Le Bar des Lices', 'address' => '3 Place des Lices', 'latitude' => 48.1123, 'longitude' => -1.6757],
            ['name' => 'Bowling Rennes', 'address' => '12 Rue de la Donelière', 'latitude' => 48.1089, 'longitude' => -1.6765],
            ['name' => 'Le Café Breton', 'address' => '15 Rue Saint-Malo', 'latitude' => 48.1135, 'longitude' => -1.6760],
            ['name' => 'Bar Le Cercle', 'address' => '6 Rue Saint-Georges', 'latitude' => 48.1127, 'longitude' => -1.6789],
            ['name' => 'Bowling La Patinoire', 'address' => '9 Rue de Châtillon', 'latitude' => 48.1112, 'longitude' => -1.6792],
        ],
        'Quimper' => [
            ['name' => 'Le Bar du Centre', 'address' => '10 Rue Kéréon', 'latitude' => 47.9968, 'longitude' => -4.0975],
            ['name' => 'Bowling Quimper', 'address' => '18 Rue de Brest', 'latitude' => 47.9951, 'longitude' => -4.0963],
            ['name' => 'Le Café de la Cathédrale', 'address' => '3 Place Saint-Corentin', 'latitude' => 47.9973, 'longitude' => -4.0957],
            ['name' => 'Bar Le Korrigan', 'address' => '7 Rue du Parc', 'latitude' => 47.9945, 'longitude' => -4.0980],
            ['name' => 'Bowling La Rue Neuve', 'address' => '22 Rue Neuve', 'latitude' => 47.9930, 'longitude' => -4.0934],
        ],
        'Niort' => [
            ['name' => 'Le Bar des Halles', 'address' => '5 Place des Halles', 'latitude' => 46.3240, 'longitude' => -0.4582],
            ['name' => 'Bowling Niort', 'address' => '11 Avenue de la Libération', 'latitude' => 46.3225, 'longitude' => -0.4605],
            ['name' => 'Le Café du Parc', 'address' => '2 Rue des Trois Rois', 'latitude' => 46.3257, 'longitude' => -0.4577],
            ['name' => 'Bar Le Central', 'address' => '8 Rue Victor Hugo', 'latitude' => 46.3235, 'longitude' => -0.4590],
            ['name' => 'Bowling du Centre', 'address' => '14 Rue des Chamois', 'latitude' => 46.3210, 'longitude' => -0.4620],
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        $i = 0;

        foreach (self::PLACES as $cityName => $cityPlaces) {
            $normalizedName = strtolower($cityName);
            /** @var City $city */
            $city = $this->getReference(CityFixtures::CITY_REFERENCE_PREFIX . $normalizedName, City::class);



            foreach ($cityPlaces as $placeData) {
                $spot = new Spot();
                $spot->setCity($city);
                $spot->setName($placeData['name']);
                $spot->setAddress($placeData['address']);
                $spot->setLatitude((string) $placeData['latitude']);
                $spot->setLongitude((string) $placeData['longitude']);

                $manager->persist($spot);
                $this->addReference(self::SPOT_REFERENCE_PREFIX . $i, $spot);
                $i++;
            }
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
