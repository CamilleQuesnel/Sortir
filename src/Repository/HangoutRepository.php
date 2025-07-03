<?php

namespace App\Repository;

use App\DTO\HangoutFilterDTO;
use App\Entity\Hangout;
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

    public function findByFilters(HangoutFilterDTO $filters, $user)
    {

        $qb = $this->createQueryBuilder('h');

            //test campus
            if ($filters->getCampus() !== null) {
                $qb->andWhere('h.campus = :campus')
                    ->setParameter('campus', $filters->getCampus());
            }

//            ->leftJoin('h.campus', 'c')
//            ->leftJoin('h.participants', 'p')
//            ->where('1=1'); // base pour ajouter des conditions
//
//        if ($filters->getCampus()) {
//            $qb->andWhere('h.campus = :campus')
//                ->setParameter('campus', $filters->getCampus());
//        }
//
//        if ($filters->getOutputNameContains()) {
//            $qb->andWhere('h.name LIKE :name')
//                ->setParameter('name', '%' . $filters->getOutputNameContains() . '%');
//        }
//
//        if ($filters->getDateFrom()) {
//            $qb->andWhere('h.dateStart >= :dateFrom')
//                ->setParameter('dateFrom', $filters->getDateFrom());
//        }
//
//        if ($filters->getDateTo()) {
//            $qb->andWhere('h.dateStart <= :dateTo')
//                ->setParameter('dateTo', $filters->getDateTo());
//        }
//
//        if ($filters->isPast()) {
//            $qb->andWhere('h.dateStart < :now')
//                ->setParameter('now', new \DateTime());
//        }
//
//        if ($filters->isOrganizer()) {
//            $qb->andWhere('h.organizer = :user')
//                ->setParameter('user', $user);
//        }
//
//        if ($filters->isRegistered()) {
//            $qb->andWhere(':user MEMBER OF h.participants')
//                ->setParameter('user', $user);
//        }
//
//        if ($filters->isNotRegistered()) {
//            $qb->andWhere(':user NOT MEMBER OF h.participants')
//                ->setParameter('user', $user);
//        }

        return $qb->getQuery()->getResult();
    }

}
