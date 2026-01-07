<?php

namespace App\Tests\API\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests for the UserAdminController update endpoint
 */
final class UserAdminControllerUpdateTest extends WebTestCase
{
    //Endpoint URL for updating users
    private const UPDATE_URL = '/api/admin/users';

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
     * Ensure that an authenticated admin user can update a user.
     */
    public function testAdminCanUpdateUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create an admin + target user
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $target = $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Call the update endpoint
        $client->request(
            'PATCH',
            self::UPDATE_URL . '/' . $target->getId(),
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'email' => 'updated.email@test.com',
                'roles' => ['ROLE_ADMIN'],
                'isVerified' => false,
            ], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // Validate response structure
        $this->assertSame($target->getId(), $data['id']);
        $this->assertSame('updated.email@test.com', $data['email']);
        $this->assertArrayHasKey('roles', $data);
        $this->assertIsArray($data['roles']);
        $this->assertContains('ROLE_ADMIN', $data['roles']);
        $this->assertSame(false, $data['isVerified']);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);

        // isActive must NOT be changed by PATCH
        $this->em->clear();
        $reloaded = $this->em->getRepository(User::class)->find($target->getId());
        $this->assertTrue($reloaded->isActive());

        // Never expose password
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('hashedPassword', $data);
    }

    /**
     * Ensure that an authenticated non-admin user cannot update a user.
     */
    public function testUserCannotUpdateUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create a non-admin + target user
        $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);
        $target = $this->createUser('target@test.com', 'Target123!', ['ROLE_USER']);

        // Authenticate as regular user
        $this->login($client, 'user@test.com', 'User123!');

        // Attempt to access admin endpoint
        $client->request(
            'PATCH',
            self::UPDATE_URL . '/' . $target->getId(),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'email' => 'blocked@test.com',
                'roles' => ['ROLE_USER'],
            ], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     *  Ensure that an anonymous user cannot update a user.
     */
    public function testAnonymousCannotUpdateUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        $target = $this->createUser('target@test.com', 'User123!', ['ROLE_USER']);

        // No authentication performed
        $client->request(
            'PATCH',
            self::UPDATE_URL . '/' . $target->getId(),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'email' => 'nope@test.com',
                'roles' => ['ROLE_USER'],
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Ensure that admin cannot update a user with an email already existing.
     */
    public function testAdminEmailAlreadyExists(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create admin
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);

        // Create existing user
        $target = $this->createUser('target@test.com', 'User123!', ['ROLE_USER']);

        // Create duplicate email user
        $this->createUser('dup@test.com', 'User123!', ['ROLE_USER']);

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        $client->request(
            'PATCH',
            self::UPDATE_URL . '/' . $target->getId(),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'email' => 'dup@test.com',
                'roles' => ['ROLE_USER'],
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(409);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Email déjà existant.', $data['message']);
    }

    /**
     *  Ensure that admin gets validation errors when updating with invalid data.
     */
    public function testAdminValidationErrors(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create admin + target user
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $target = $this->createUser('target@test.com', 'User123!', ['ROLE_USER']);

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Call the update endpoint with invalid data
        $client->request(
            'PATCH',
            self::UPDATE_URL . '/' . $target->getId(),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode([
                'email' => 'not-an-email',
                'roles' => [],
            ], JSON_THROW_ON_ERROR),
        );

        $this->assertResponseStatusCodeSame(422);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Validation échouée.', $data['message']);
        $this->assertArrayHasKey('errors', $data);
    }

    /**
     * Ensure that an authenticated admin user gets a 400 Bad Request when sending invalid JSON.
     */
    public function testAdminInvalidJson(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create admin + target user
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $target = $this->createUser('target@test.com', 'User123!', ['ROLE_USER']);

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        $client->request(
            'PATCH',
            self::UPDATE_URL . '/' . $target->getId(),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: '{invalid-json'
        );

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('JSON invalide.', $data['message']);
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
            'PATCH',
            self::UPDATE_URL . '/999999',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'email' => 'new@test.com',
                'roles' => ['ROLE_USER'],
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(404);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Utilisateur introuvable.', $data['message']);
    }
}
