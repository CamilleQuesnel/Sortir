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

        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            if ($i == 0) {
                $user->setPseudo('Seb');
                $user->setFirstName('Sébastien');
                $user->setLastName('Fischer');
                $user->setMail('sebastienfischer@hotmail.fr');
                $user->setPhoneNumber('0768983096');
                $user->setImage(null); // ou une URL image random avec $faker->imageUrl()
                $user->setAdmin(1); // Le 1er est admin
                $user->setActive(1);
            }elseif ($i == 1) {
                $user->setPseudo('User');
                $user->setFirstName('User');
                $user->setLastName('User');
                $user->setMail('user@user.user');
                $user->setPhoneNumber('0102030405');
                $user->setImage(null); // ou une URL image random avec $faker->imageUrl()
                $user->setAdmin(0); // Le 1er est admin
                $user->setActive(1);
            } else {
                $user->setPseudo($faker->unique()->userName());
                $user->setFirstName($faker->firstName());
                $user->setLastName($faker->lastName());
                $user->setMail($faker->unique()->email());
                $user->setPhoneNumber($faker->phoneNumber());
                $user->setImage(null); // ou une URL image random avec $faker->imageUrl()
                $user->setAdmin(0); // Le 1er est admin
                $user->setActive($faker->boolean());
            }
            // Hasher le mot de passe
            $password = $this->hasher->hashPassword($user, 'password');
            $user->setPassword($password);

            // Rôle
            $user->setRoles($i === 0 ? ['ROLE_ADMIN'] : ['ROLE_USER']);

            // Campus au hasard
            $campusCount = count(CampusFixtures::$campusNames);
            $campusIndex = rand(0, $campusCount - 1);
            $campusReference = CampusFixtures::CAMPUS_REFERENCE_PREFIX . $campusIndex;

            $campusEntity = $this->getReference($campusReference, Campus::class);

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
