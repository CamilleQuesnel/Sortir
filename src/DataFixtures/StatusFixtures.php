<?php

namespace App\DataFixtures;
use App\Entity\Status;
use App\Enum\EnumStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StatusFixtures extends Fixture
{
    public const STATUS_REFERENCE_PREFIX = 'status_';

    public static array $statuss = [
        'Créée',
        'Ouverte',
        'Fermée',
        'En cours',
        'Passée',
        'Annulée',
        'Archivée'
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (static::$statuss as $key => $label) {
            $status = new Status();
            $status->setLabel($label);

            $manager->persist($status);
            $this->addReference(self::STATUS_REFERENCE_PREFIX . $key, $status);
        }

        $manager->flush();
    }
}


