<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Test for the UserRepository.
 */
final class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private UserRepository $repo;

    /**
     * Setup before each test case.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repo = static::getContainer()->get(UserRepository::class);
    }

    /**
     * Test listPaginated method returns only active users and respects limit.
     */
    public function testListPaginatedReturnsOnlyActiveUsersAndRespectsLimit(): void
    {
        // Create 3 users, 2 active + 1 inactive
        $u1 = (new User())->setEmail('a1@test.local')->setPassword('x')->setRoles(['ROLE_USER']);
        $u1->setIsActive(true);

        $u2 = (new User())->setEmail('a2@test.local')->setPassword('x')->setRoles(['ROLE_USER']);
        $u2->setIsActive(true);

        $u3 = (new User())->setEmail('i1@test.local')->setPassword('x')->setRoles(['ROLE_USER']);
        $u3->setIsActive(false);

        $this->em->persist($u1);
        $this->em->persist($u2);
        $this->em->persist($u3);
        $this->em->flush();
        $this->em->clear();

        // Act
        $paginator = $this->repo->listPaginated(page: 1, limit: 1);

        // Assert
        self::assertCount(2, $paginator, 'Le total des utilisateurs actifs doit être de 2.');

        $items = iterator_to_array($paginator);
        self::assertCount(1, $items, 'La page 1 avec une limite de 1 devrait renvoyer exactement 1 élément.');

        /** @var User $first */
        $first = $items[0];
        self::assertTrue($first->isActive(), 'L\'utilisateur retourné doit être actif.');
    }
}
