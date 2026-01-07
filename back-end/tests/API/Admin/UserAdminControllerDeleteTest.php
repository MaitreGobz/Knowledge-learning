<?php

namespace App\Tests\API\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests for the UserAdminController delete endpoint
 */
final class UserAdminControllerDeleteTest extends WebTestCase
{
    // Doctrine EntityManager used to persist and clean test data
    private EntityManagerInterface $em;
    // Password hasher used to generate valid hashed passwords for test users
    private UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Creates and persists a User entity for testing.
     */
    private function createUser(string $email, string $plainPassword, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setIsVerified(true);
        $user->setIsActive(true);

        $hashed = $this->hasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Performs a login request against the API authentication endpoint.
     */
    private function login(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $email, string $password): void
    {
        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode(['email' => $email, 'password' => $password], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Ensures that an authenticated admin user can soft delete a user (DELETE -> isActive=false).
     */
    public function testAdminCanDeleteUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        // Create an admin + target user
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $target = $this->createUser('target@test.com', 'User123!', ['ROLE_USER']);

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Call the update endpoint
        $client->request(
            'DELETE',
            '/api/admin/users/' . $target->getId(),
            server: ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Ensures that an authenticated admin user cannot delete himself
     */
    public function testAdminCannotDeleteSelf(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        // Create an admin user
        $admin = $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Call the delete endpoint on self
        $client->request(
            'DELETE',
            '/api/admin/users/' . $admin->getId(),
            server: ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertResponseStatusCodeSame(409);
    }

    /**
     * Ensures that a non-admin user cannot delete a user
     */
    public function testNonAdminCannotDeleteUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        // Create a non-admin + target user
        $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);
        $target = $this->createUser('target@test.com', 'Target123!', ['ROLE_USER']);

        // Authenticate as non-admin
        $this->login($client, 'user@test.com', 'User123!');

        // Call the delete endpoint
        $client->request(
            'DELETE',
            '/api/admin/users/' . $target->getId(),
            server: ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Ensure that an anonymous user cannot delete a user
     */
    public function testAnonymousCannotDeleteUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        // Create a target user
        $target = $this->createUser('target@test.com', 'Target123!', ['ROLE_USER']);

        // Call the delete endpoint without authentication
        $client->request(
            'DELETE',
            '/api/admin/users/' . $target->getId(),
            server: ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertResponseStatusCodeSame(401);
    }
}
