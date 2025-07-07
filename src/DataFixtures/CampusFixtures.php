<?php

namespace App\DataFixtures;
use App\Entity\Campus;
use App\Enum\EnumCampus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    const CAMPUS_REFERENCE_PREFIX = 'campus_';
    public const CAMPUSNAMES = [
        [
            'nom' => 'Nantes',
            'codesPostaux' => '44000'
        ],
        [
            'nom' => 'Rennes',
            'codesPostaux' => '35000'
        ],
        [
            'nom' => 'Quimper',
            'codesPostaux' => '29000'
        ],
        [
            'nom' => 'Niort',
            'codesPostaux' => '79000'
        ],
        [
            'nom' => 'En ligne',
            'codesPostaux' => '00000'
        ]
    ];

    public function load(ObjectManager $manager): void
    {

        foreach (self::CAMPUSNAMES as $key => $data) {
            $campus = new Campus();
            $campus->setName($data['nom']);
            $manager->persist($campus);
            $this->addReference(self::CAMPUS_REFERENCE_PREFIX . $key, $campus);

        }

        $manager->flush();
    }
}
