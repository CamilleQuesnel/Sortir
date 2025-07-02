<?php

namespace App\DataFixtures;

use App\Entity\Hangout;
use App\Entity\Spot;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class HangoutFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 100; $i++) {
            $hangout = new Hangout();
            $hangout->setName($faker->catchPhrase());

            // Dates réalistes
            $startDate = $faker->dateTimeBetween('+1 days', '+1 month');
            $deadline = (clone $startDate)->modify('-2 days');
            $duration = (clone $startDate)->modify('+2 hours');

            $hangout->setStartingDate($startDate);
            $hangout->setRegistrationDeadline($deadline);
            $hangout->setDuration($duration);
            $hangout->setNbInscriptionsMax($faker->numberBetween(5, 20));
            $hangout->setDescription($faker->realText(100));

            // Organizer (aléatoire parmi les users)
            $organizerIndex = rand(0, 9);
            $organizer = $this->getReference(UserFixtures::USER_REFERENCE_PREFIX . $organizerIndex, User::class);
            $hangout->setOrganizer($organizer);

            // Campus = même que l'organisateur
            $hangout->setCampus($organizer->getCampus());

            // Status (aléatoire)
            $statusCount = count(StatusFixtures::$statuss);
            $statusIndex = rand(0, $statusCount - 1);
            $statusRef = $campusReference = StatusFixtures::STATUS_REFERENCE_PREFIX . $statusIndex;
            $statusEntity = $this->getReference($statusRef,Status::class);
            $hangout->setStatus($statusEntity);

            // Spot (aléatoire)
            $spotIndex = rand(0, 4);
            $hangout->setSpot($this->getReference(SpotFixtures::SPOT_REFERENCE_PREFIX . $spotIndex,Spot::class));

            // Participants (entre 1 et 5)
            $participants = $faker->randomElements(range(0, 9), rand(1, 5));
            foreach ($participants as $participantIndex) {
                $user = $this->getReference(UserFixtures::USER_REFERENCE_PREFIX . $participantIndex,User::class);
                $hangout->addUser($user); // M2M
            }

            $manager->persist($hangout);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            SpotFixtures::class,
            StatusFixtures::class,
        ];
    }
}
