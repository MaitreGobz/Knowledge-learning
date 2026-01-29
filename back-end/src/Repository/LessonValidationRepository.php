<?php

namespace App\Repository;

use App\Entity\LessonValidation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LessonValidation>
 */
class LessonValidationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LessonValidation::class);
    }

    /**
     * Count validated lessons in a cursus by a user
     */
    public function validatedLessonInCursusByUser(User $user, int $cursusId): int
    {
        return (int) $this->createQueryBuilder('lv')
            ->select('COUNT(lv.id)')
            ->innerJoin('lv.lesson', 'l')
            ->andWhere('lv.user = :user')
            ->andWhere('l.cursus = :cursusId')
            ->setParameter('user', $user)
            ->setParameter('cursusId', $cursusId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count validated lessons in a theme by a user
     */
    public function validatedLessonsInThemeByUser(User $user, int $themeId): int
    {
        return (int) $this->createQueryBuilder('lv')
            ->select('COUNT(lv.id)')
            ->innerJoin('lv.lesson', 'l')
            ->innerJoin('l.cursus', 'c')
            ->innerJoin('c.theme', 't')
            ->andWhere('lv.user = :user')
            ->andWhere('t.id = :themeId')
            ->andWhere('l.isActive = :active')
            ->andWhere('c.isActive = :cursusActive')
            ->setParameter('user', $user)
            ->setParameter('themeId', $themeId)
            ->setParameter('active', true)
            ->setParameter('cursusActive', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
