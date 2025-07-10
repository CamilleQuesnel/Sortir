<?php

namespace App\Services;

use App\Entity\Hangout;
use App\Repository\StatusRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class StatusService
{
    private EntityManagerInterface $entityManager;
    private StatusRepository $statusRepository;


    public function __construct(EntityManagerInterface $entityManager, StatusRepository $statusRepository)
    {
        $this->entityManager = $entityManager;
        $this->statusRepository = $statusRepository;
    }

    public function statusSort(array $hangouts): void
    {
        $today = new DateTimeImmutable();
        $tomorrow = $today->modify('+1 day');
        $oneMonthAgo = $today->modify('-1 month');
        $statusFermee = $this->statusRepository->findOneBy(['label' => 'Fermée']);
        $statusEnCours = $this->statusRepository->findOneBy(['label' => 'En cours']);
        $statusPassee = $this->statusRepository->findOneBy(['label' => 'Passée']);
        $statusArchivee = $this->statusRepository->findOneBy(['label' => 'Archivée']);

        foreach ($hangouts as $sortie) {
            $label = $sortie->getStatus()->getLabel();
            $dateHangout = $sortie->getStartingDate();
            $dateInscription = $sortie->getRegistrationDeadline();
            $duration = $sortie->getDuration();

            // Ignorer les "Annulée" ou déjà "Archivée"
            if ($label === 'Annulée' || $label === 'Archivée') {
                continue;
            }
            // Supprimer les "Crée" dont la date est inf demain
            if ($label === 'Crée' && $dateHangout < $tomorrow) {
                $this->entityManager->remove($sortie);
                continue;
            }
            // Passage en "Fermée" si la date limite d'inscription est dépassée
            if ($dateInscription < $today) {
                $sortie->setStatus($statusFermee);
            }
            // Passage en "En Cours" si la date limite de sortie est dépassée
            if ($dateHangout < $today->modify("-{$duration} minutes")) {
                $sortie->setStatus($statusEnCours);
            }
            // Passage en "Passée" si la date de début est antérieure à aujourd'hui
            if ($dateHangout < $today) {
                $sortie->setStatus($statusPassee);
            }
            // Passage en "Archivée" si la date est plus vieille qu’un mois
            if ($dateHangout < $oneMonthAgo) {
                $sortie->setStatus($statusArchivee);
            }
            $this->entityManager->persist($sortie);
        }
        $this->entityManager->flush();
    }
}
