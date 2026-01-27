<?php

namespace App\Repository;

use App\Entity\AccessRight;
use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccessRight>
 */
class AccessRightRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessRight::class);
    }

    /**
     * Check if a user has access to a specific lesson based on their access rights and purchase status.
     */
    public function lessonAccess(User $user, Lesson $lesson, array $validStatuses): bool
    {
        $count = (int) $this->createQueryBuilder('ar')
            ->select('COUNT(ar.id)')
            ->leftJoin('ar.purchase', 'p')
            ->andWhere('ar.user = :user')
            ->andWhere('ar.lesson = :lesson')
            ->andWhere('(p.id IS NULL OR p.status IN (:statuses))')
            ->setParameter('user', $user)
            ->setParameter('lesson', $lesson)
            ->setParameter('statuses', $validStatuses)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Check if a user has access to a specific cursus based on their access rights and purchase status.
     */
    public function cursusAccess(User $user, Cursus $cursus, array $validStatuses): bool
    {
        $count = (int) $this->createQueryBuilder('ar')
            ->select('COUNT(ar.id)')
            ->leftJoin('ar.purchase', 'p')
            ->andWhere('ar.user = :user')
            ->andWhere('ar.cursus = :cursus')
            ->andWhere('(p.id IS NULL OR p.status IN (:statuses))')
            ->setParameter('user', $user)
            ->setParameter('cursus', $cursus)
            ->setParameter('statuses', $validStatuses)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
