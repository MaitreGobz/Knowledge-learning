<?php

namespace App\Repository;

use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lesson>
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    public function findActiveByCursusIdOrdered(int $cursusId): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.cursus = :cursusId')
            ->andWhere('l.isActive = :active')
            ->setParameter('cursusId', $cursusId)
            ->setParameter('active', true)
            ->orderBy('l.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
