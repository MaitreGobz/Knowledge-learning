<?php

namespace App\Repository;

use App\Entity\Cursus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cursus>
 */
class CursusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cursus::class);
    }

    public function findActiveById(int $id): ?\App\Entity\Cursus
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->andWhere('c.isActive = :active')
            ->setParameter('id', $id)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
