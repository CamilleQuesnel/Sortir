<?php

namespace App\Tests\Services;

use App\Entity\Hangout;
use App\Entity\Status;
use App\Repository\StatusRepository;
use App\Services\StatusService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class StatusServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private StatusRepository $statusRepository;
    private StatusService $statusService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->statusRepository = $this->createMock(StatusRepository::class);
        $this->statusService = new StatusService($this->entityManager, $this->statusRepository);
    }

    public function testCreeHangoutIsRemovedWhenDateBeforeTomorrow(): void
    {
        $statusCree = (new Status())->setLabel('Crée');
        $hangout = (new Hangout())
            ->setStatus($statusCree)
            ->setStartingDate(new \DateTimeImmutable('+1 hour'))
            ->setRegistrationDeadline(new \DateTimeImmutable('-1 day'))
            ->setDuration(60);

        $this->statusRepository->method('findOneBy')->willReturn(new Status());

        $this->entityManager->expects($this->once())->method('remove')->with($hangout);
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->statusService->statusSort([$hangout]);
    }

    public function testRegistrationDeadlinePassedSetsStatusFermee(): void
    {
        $statusOuverte = (new Status())->setLabel('Ouverte');
        $statusFermee = (new Status())->setLabel('Fermée');
        $statusEnCours = (new Status())->setLabel('En cours');
        $statusPassee = (new Status())->setLabel('Passée');
        $statusArchivee = (new Status())->setLabel('Archivée');

        $this->statusRepository->method('findOneBy')->willReturnMap([
            [['label' => 'Fermée'], null, $statusFermee],
            [['label' => 'En cours'], null, $statusEnCours],
            [['label' => 'Passée'], null, $statusPassee],
            [['label' => 'Archivée'], null, $statusArchivee],
        ]);

        $hangout = (new Hangout())
            ->setStatus($statusOuverte)
            ->setStartingDate(new \DateTimeImmutable('+2 days'))
            ->setRegistrationDeadline(new \DateTimeImmutable('-1 day'))
            ->setDuration(60);

        $this->entityManager->expects($this->once())->method('persist')->with($hangout);
        $this->entityManager->expects($this->once())->method('flush');

        $this->statusService->statusSort([$hangout]);

        $this->assertSame('Fermée', $hangout->getStatus()->getLabel());
    }
}
