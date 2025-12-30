<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function searchPaginated(
        int $page,
        int $limit,
        ?string $email,
        ?bool $isVerified,
        string $sort,
        string $order
    ): Paginator {
        $qb = $this->createQueryBuilder('u');

        // Filter by email
        if ($email !== null && $email !== '') {
            $qb->andWhere('u.email LIKE :email')
                ->setParameter('email', '%' . $email . '%');
        }

        // Filter by email verification flag
        if ($isVerified !== null) {
            $qb->andWhere('u.isVerified = :isVerified')
                ->setParameter('isVerified', $isVerified);
        }

        // Sorting
        $qb->orderBy('u.' . $sort, strtoupper($order) === 'ASC' ? 'ASC' : 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb->getQuery(), true);
    }
}
