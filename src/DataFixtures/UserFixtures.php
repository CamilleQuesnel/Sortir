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
        $uploadDir = dirname(__DIR__, 2). '/public/upload/images';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true); // true = crée les sous-dossiers si besoin
        }
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            // Appel API randomuser
            $json = file_get_contents('https://randomuser.me/api/');
            $data = json_decode($json, true);
            // Récupération de l'image (grande version)
            $imageUrl = $data['results'][0]['picture']['large']; // taille large/medium/thumbnail

            // récuperation des infos utiles pour le projet
            $speudo=$data['results'][0]['login']['username'];
            $firstName=$data['results'][0]['name']['first'];
            $lastName=$data['results'][0]['name']['last'];
            $mail=$data['results'][0]['email'];
            $phoneNumber=$data['results'][0]['phone'];
            $image =basename($imageUrl);

            $imageData = file_get_contents($imageUrl);
            file_put_contents($uploadDir . '/' . $image, $imageData);



            if ($i == 0) {
                $user->setPseudo('Seb');
                $user->setFirstName('Sébastien');
                $user->setLastName('Fischer');
                $user->setMail('sebastienfischer@hotmail.fr');
                $user->setPhoneNumber('0768983096');
                $user->setImage('../Admin.jpeg');
                $user->setAdmin(1); // Le 1er est admin
                $user->setActive(1);
            }elseif ($i == 1) {
                $user->setPseudo('Camille');
                $user->setFirstName('Camille');
                $user->setLastName('Quesnel');
                $user->setMail('camille@Quesnel.admin');
                $user->setPhoneNumber('0102030405');
                $user->setImage('../AdminC.png');
                $user->setAdmin(1); // Le 2eme aussi est admin
                $user->setActive(1);
            }
            elseif ($i == 2) {
                $user->setPseudo('user');
                $user->setFirstName('User');
                $user->setLastName('User');
                $user->setMail('User@user.user');
                $user->setPhoneNumber('0102030405');
                $user->setImage('../user.jpg');
                $user->setAdmin(0);
                $user->setActive(1);
            } else {
                $user->setPseudo($speudo);
                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $user->setMail($mail);
                $user->setPhoneNumber($phoneNumber);
                $user->setImage($image);
                $user->setAdmin(0);
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
