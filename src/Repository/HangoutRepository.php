<?php

namespace App\Repository;


use App\Entity\Hangout;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hangout>
 */
class HangoutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hangout::class);
    }

    public function findByFilters(?User $user, ?array $filters): array

    {
        $qb = $this->createQueryBuilder('h')
            ->leftJoin('h.campus', 'c')
            ->leftJoin('h.users', 'u')
            ->addSelect('c', 'u')
            ->orderBy('h.startingDate', 'ASC');

        if (!empty($filters['campus'])) {
            $qb->andWhere('h.campus = :campus')
                ->setParameter('campus', $filters['campus']);
        }

        if (!empty($filters['outputNameContains'])) {
            $qb->andWhere('h.name LIKE :name')
                ->setParameter('name', '%' . $filters['outputNameContains'] . '%');
        }

        if (!empty($filters['dateFrom'])) {
            $qb->andWhere('h.startingDate >= :dateFrom')
                ->setParameter('dateFrom', $filters['dateFrom']);
        }

        if (!empty($filters['dateTo'])) {
            $qb->andWhere('h.startingDate <= :dateTo')
                ->setParameter('dateTo', $filters['dateTo']);
        }

        if (!empty($filters['isPast'])) {
            $qb->andWhere('h.startingDate < :now')
                ->setParameter('now', new \DateTime());
        }

        if (!empty($filters['isOrganizer']) && $user) {
            $qb->andWhere('h.organizer = :user')
                ->setParameter('user', $user);
        }

        if (!empty($filters['isRegistered']) && $user) {
            $qb->andWhere(':user MEMBER OF h.users')
                ->setParameter('user', $user);
        }

        if (!empty($filters['isNotRegistered']) && $user) {
            $qb->andWhere(':user NOT MEMBER OF h.users')
                ->setParameter('user', $user);
        }
        return $qb->getQuery()->getResult();
    }


}
