<?php

namespace App\Repository;

use App\Entity\AccessRight;
use App\Entity\Lesson;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\Join;

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

    /**
     * Count active lessons by cursus ID
     */
    public function countActiveByCursusId(int $cursusId): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.cursus = :cursusId')
            ->andWhere('l.isActive = :active')
            ->setParameter('cursusId', $cursusId)
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count active lessons by theme ID
     */
    public function countActiveByThemeId(int $themeId): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->innerJoin('l.cursus', 'c')
            ->innerJoin('c.theme', 't')
            ->andWhere('t.id = :themeId')
            ->andWhere('l.isActive = :active')
            ->andWhere('c.isActive = :cursusActive')
            ->setParameter('themeId', $themeId)
            ->setParameter('active', true)
            ->setParameter('cursusActive', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAccessibleLessonsForUser(User $user): array
    {
        return $this->createQueryBuilder('l')
            ->select('DISTINCT l.id AS lessonId, l.title AS lessonTitle, l.position AS lessonPosition, c.id AS cursusId, c.title AS cursusTitle')
            ->innerJoin('l.cursus', 'c')
            ->leftJoin(AccessRight::class, 'arLesson', Join::WITH, 'arLesson.user = :user AND arLesson.lesson = l')
            ->leftJoin(AccessRight::class, 'arCursus', Join::WITH, 'arCursus.user = :user AND arCursus.cursus = c')
            ->andWhere('l.isActive = :active')
            ->andWhere('c.isActive = :cursusActive')
            ->andWhere('arLesson.id IS NOT NULL OR arCursus.id IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('active', true)
            ->setParameter('cursusActive', true)
            ->orderBy('c.title', 'ASC')
            ->addOrderBy('l.position', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }
}
