<?php

namespace App\Repository;

use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Lesson>
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    /**
     * List lessons with pagination
     */
    public function listPaginated(int $page, int $limit): Paginator
    {
        $qb = $this->createQueryBuilder('l')
            ->andWhere('l.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('l.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb->getQuery(), true);
    }

    /**
     * Find active lessons by cursus ID ordered by position
     */
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
