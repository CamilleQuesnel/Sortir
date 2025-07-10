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

        // Liste de tous les spots disponibles
        $allSpots = [];
        for ($j = 0; $j < 20; $j++) {
            /** @var Spot $spot */
            $spot = $this->getReference(SpotFixtures::SPOT_REFERENCE_PREFIX . $j, Spot::class);

            $allSpots[] = $spot;
        }

        for ($i = 0; $i < 50; $i++) {
            $hangout = new Hangout();
            $hangout->setName($faker->catchPhrase());

            // Dates réalistes
            $startDate = $faker->dateTimeBetween('-2 month', '+2 month');
            $deadline = (clone $startDate)->modify('-2 days');
            $duration = rand(60, 480); // entre 1h et 8h en minutes

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
            $statusIndex =  rand(0,1);
            $statusRef = StatusFixtures::STATUS_REFERENCE_PREFIX . $statusIndex;
            $statusEntity = $this->getReference($statusRef, Status::class);

            $hangout->setStatus($statusEntity);

            // Spot : 30% avec ville inactive, 70% normal
            $spot = null;
            if (random_int(1, 100) <= 30) {
                // Spots avec villes désactivées
                $inactiveSpots = array_filter($allSpots, fn(Spot $s) => !$s->getCity()->isActive());
                if (!empty($inactiveSpots)) {
                    $spot = $inactiveSpots[array_rand($inactiveSpots)];
                }
            }

            // Fallback : spot aléatoire
            if (!$spot) {
                $spot = $allSpots[array_rand($allSpots)];
            }
            $hangout->setSpot($spot);

            // Participants (entre 1 et 5)
            $participants = $faker->randomElements(range(0, 9), rand(1, 5));
            foreach ($participants as $participantIndex) {
                $user = $this->getReference(UserFixtures::USER_REFERENCE_PREFIX . $participantIndex, User::class);
                $hangout->addUser($user);
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
