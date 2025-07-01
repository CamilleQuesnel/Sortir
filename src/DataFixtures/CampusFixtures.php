<?php

namespace App\DataFixtures;
use App\Entity\Campus;
use App\Enum\EnumCampus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    const CAMPUS_REFERENCE_PREFIX = 'campus_';
    public static array $campusNames = [
        'Nantes',
        'Rennes',
        'Quimper',
        'Niort',
        'En ligne'
    ];

    public function load(ObjectManager $manager): void
    {

        foreach (static::$campusNames as $key => $name) {
            $campus = new Campus();
            $campus->setName($name);
            $manager->persist($campus);
            $this->addReference(self::CAMPUS_REFERENCE_PREFIX . $key, $campus);

        }

        $manager->flush();
    }
}
