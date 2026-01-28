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
}
