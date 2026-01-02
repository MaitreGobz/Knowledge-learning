<?php

namespace App\Tests\API\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests for the UserAdminController create user endpoints.
 */
final class UserAdminControllerCreateTest extends WebTestCase
{
    //Endpoint URL for creating users
    private const CREATE_URL = '/api/admin/users';

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
     * Ensure that an authenticated admin user can create a new user.
     */
    public function testAdminCanCreateUser(): void
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

        // Call create endpoint
        $client->request(
            'POST',
            self::CREATE_URL,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'email' => 'new.user@test.com',
                'password' => 'User123!',
                'roles' => ['ROLE_USER']
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('new.user@test.com', $data['email']);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertArrayHasKey('isActive', $data);
        $this->assertArrayHasKey('isVerified', $data);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);

        // Never expose password
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('hashedPassword', $data);

        // Verify DB side
        $repo = $this->em->getRepository(User::class);
        $created = $repo->findOneBy(['email' => 'new.user@test.com']);
        $this->assertNotNull($created);
        $this->assertNotSame('StrongP@ssw0rd123!', $created->getPassword());
        $this->assertTrue($created->isVerified());
    }

    /**
     * Ensure that an authenticated non-admin user cannot create a new user.
     */
    public function testUserCannotCreateUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create user (non-admin)
        $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);

        // Authenticate as regular user
        $this->login($client, 'user@test.com', 'User123!');

        $client->request(
            'POST',
            self::CREATE_URL,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'email' => 'new.user@test.com',
                'password' => 'StrongP@ssw0rd123!',
            ], JSON_THROW_ON_ERROR)
        );

        // IsGranted must block
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Ensure that an anonymous user cannot create a new user.
     */
    public function testAnonymousCannotCreateUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // No authentication performed
        $client->request(
            'POST',
            self::CREATE_URL,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'email' => 'new.user@test.com',
                'password' => 'StrongP@ssw0rd123!',
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Ensure that an authenticated admin user cannot create a new user with an emil already existing.
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
        $this->createUser('dup@test.com', 'User123!', ['ROLE_USER']);

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        $client->request(
            'POST',
            self::CREATE_URL,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'email' => 'dup@test.com',
                'password' => 'StrongP@ssw0rd123!',
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(409);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Email déjà existant.', $data['message']);
    }

    /**
     * Ensure that an authenticated admin user gets validation errors when creating a new user with invalid data.
     */
    public function testAdminValidationError(): void
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

        // invalid password according to DTO
        $client->request(
            'POST',
            self::CREATE_URL,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'email' => 'bad@test.com',
                'password' => 'weak',
            ], JSON_THROW_ON_ERROR)
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

        // Create admin
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        $client->request(
            'POST',
            self::CREATE_URL,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: '{invalid-json'
        );

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Invalid JSON.', $data['message']);
    }
}
