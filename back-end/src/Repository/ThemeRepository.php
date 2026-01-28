<?php

namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Theme>
 */
class ThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    /**
     * Find all themes with their associated cursus as preview
     */
    public function findAllWithCursusPreview(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.cursus', 'c')
            ->addSelect('c')
            ->getQuery()
            ->getResult();
    }
}
