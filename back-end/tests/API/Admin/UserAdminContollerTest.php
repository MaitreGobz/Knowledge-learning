<?php

namespace App\Tests\Api\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests for the UserAdminController API endpoints
 */
final class UserAdminContollerTest extends WebTestCase
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
     * Creates and persists a User entity for testing purposes.
     * Roles are injected explicitly to simulate admin or standard users.
     */
    private function createUser(string $email, string $plainPassword, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setIsVerified(true);

        // Hash the plain password using Symfony's password hasher
        $hashed = $this->hasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        // Persist user into the test database
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Performs a login request against the API authentication endpoint.
     * The authentication cookie / token is automatically stored in the test client.
     */
    private function login(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $email, string $password): void
    {
        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode(['email' => $email, 'password' => $password], JSON_THROW_ON_ERROR)
        );

        // A successful login must return HTTP 200
        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Ensures that an authenticated admin user can access
     * the admin users listing endpoint.
     */
    public function test_admin_can_list_users(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        // Create an admin user and a regular user
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Call the admin endpoint
        $client->request('GET', '/api/admin/users?page=1&limit=20', server: ['HTTP_ACCEPT' => 'application/json']);

        // Admin access should be granted
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);

        // Expected JSON response structure
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertIsArray($data['items']);
        $this->assertIsArray($data['meta']);

        // Pagination metadata must be present
        $this->assertArrayHasKey('page', $data['meta']);
        $this->assertArrayHasKey('limit', $data['meta']);
        $this->assertArrayHasKey('totalItems', $data['meta']);
        $this->assertArrayHasKey('totalPages', $data['meta']);
    }

    /**
     * Ensures that an authenticated non-admin user
     * cannot access the admin users listing endpoint.
     */
    public function test_user_cannot_list_users(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);

        // Authenticate as a regular user
        $this->login($client, 'user@test.com', 'User123!');

        // Attempt to access admin endpoint
        $client->request('GET', '/api/admin/users', server: ['HTTP_ACCEPT' => 'application/json']);

        // Access must be denied (ROLE_ADMIN required)
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Ensures that an anonymous (unauthenticated) user
     * cannot access the admin users listing endpoint.
     */
    public function test_anonymous_cannot_list_users(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);

        // No authentication performed here
        $client->request('GET', '/api/admin/users', server: ['HTTP_ACCEPT' => 'application/json']);

        // Anonymous access must be rejected
        $this->assertResponseStatusCodeSame(401);
    }
}
