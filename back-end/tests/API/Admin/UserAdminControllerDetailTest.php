<?php

namespace App\Tests\API\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests for the UserAdminController Detail API endpoint
 */
final class UserAdminControllerDetailTest extends WebTestCase
{
    // Endpoint URL for user details
    private const DETAIL_URL_TEMPLATE = '/api/admin/users/%d';

    // Doctrine EntityManager used to persist and clean test data
    private EntityManagerInterface $em;
    // Password hasher used to generate valid hashed passwords for test users
    private UserPasswordHasherInterface $hasher;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Creates and persists a User entity for testing purposes.
     */
    private function createUser(string $email, string $plainPassword, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setIsVerified(true);

        $hashed = $this->hasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Performs a login request against the API authentication endpoint.
     * Roles are injected explicitly to simulate admin or standard users.
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
     * Ensure that an authenticated admin user can retrieve the details of a specific user.
     */
    public function testAdminCanShowUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        // Clean users table (same pattern as your list test if needed)
        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create admin + target
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $target = $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Call the detail endpoint
        $client->request(
            'GET',
            sprintf(self::DETAIL_URL_TEMPLATE, $target->getId()),
            server: ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // Validate response structure
        $this->assertSame($target->getId(), $data['id']);
        $this->assertSame('user@test.com', $data['email']);
        $this->assertArrayHasKey('roles', $data);
        $this->assertArrayHasKey('isVerified', $data);
        $this->assertArrayHasKey('createdAt', $data);

        // Never expose password
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('hashedPassword', $data);
    }

    /**
     * Ensure that an authenticated non-admin user cannot retrieve the details of a specific user.
     */
    public function testUserCannotShowUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create admin + user + target
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $this->createUser('user1@test.com', 'User123!', ['ROLE_USER']);
        $target = $this->createUser('target@test.com', 'User123!', ['ROLE_USER']);

        // Authenticate as regular user
        $this->login($client, 'user1@test.com', 'User123!');

        // Attempt to access admin endpoint
        $client->request(
            'GET',
            sprintf(self::DETAIL_URL_TEMPLATE, $target->getId()),
            server: ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Ensure that an anonymous user cannot retrieve the details of a specific user.
     */
    public function testAnonymousCannotShowUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        $target = $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);

        // No authentication performed
        $client->request(
            'GET',
            sprintf(self::DETAIL_URL_TEMPLATE, $target->getId()),
            server: ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Ensure that admin receives 404 for a non-existing user id.
     */
    public function testShowUserNotFound(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create admin
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Non-existing id
        $client->request(
            'GET',
            sprintf(self::DETAIL_URL_TEMPLATE, 999999),
            server: ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(404);
    }
}
