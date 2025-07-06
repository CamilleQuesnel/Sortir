<?php

namespace App\Services;

use App\Entity\User;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CsvImporter
{
    private EntityManagerInterface $entityManager;
    private CampusRepository $campusRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, CampusRepository $campusRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->campusRepository = $campusRepository;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @throws InvalidArgument
     * @throws UnavailableStream
     * @throws SyntaxError
     * @throws Exception
     */
    public function import(string $filePath): array
    {
        // Load CSV file
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        $csv->setDelimiter(';');
        $statement = new Statement();
        $records = $statement->process($csv);



        $data = [];
        foreach ($records as $record) {
            $user = new User();
            $user->setPseudo($record['pseudo']);
            $user->setPassword($this->passwordHasher->hashPassword($user, "password"));
            $user->setLastName($record['lastName']);
            $user->setFirstName($record['firstName']);
            $user->setPhoneNumber($record['phoneNumber']);
            $user->setActive(1);
            $user->setCampus($this->campusRepository->findOneBy(["name" => "Rennes"]));
            $user->setMail($record['mail']);
            $user->setAdmin(0);
            $user->setRoles(["ROLE_USER"]);

            $this->entityManager->persist($user);
            $data[] = $record;
        }

        $this->entityManager->flush();

        return $data;
    }
}
