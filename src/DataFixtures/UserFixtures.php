<?php

namespace App\DataFixtures;

use App\DataFixtures\CampusFixtures;
use App\Entity\Campus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const USER_REFERENCE_PREFIX = 'user_';

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setPseudo($faker->unique()->userName());
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setMail($faker->unique()->email());
            $user->setPhoneNumber($faker->phoneNumber());
            $user->setImage(null); // ou une URL image random avec $faker->imageUrl()
            $user->setAdmin($i === 0); // Le 1er est admin
            $user->setActive($faker->boolean());

            // Hasher le mot de passe
            $password = $this->hasher->hashPassword($user, 'password');
            $user->setPassword($password);

            // Rôle
            $user->setRoles($i === 0 ? ['ROLE_ADMIN'] : ['ROLE_USER']);

            // Campus au hasard
            $campusCount = count(CampusFixtures::$campusNames);
            $campusIndex = rand(0, $campusCount - 1);
            $campusReference = CampusFixtures::CAMPUS_REFERENCE_PREFIX . $campusIndex;

            $campusEntity = $this->getReference($campusReference,Campus::class);

            $user->setCampus($campusEntity);


            $manager->persist($user);

            // Référence pour Hangout
            $this->addReference(self::USER_REFERENCE_PREFIX . $i, $user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
        ];
    }
}
