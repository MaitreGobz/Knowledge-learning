<?php

namespace App\Tests\API\Admin;

use App\Entity\User;
use App\Entity\Theme;
use App\Entity\Cursus;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests for the LessonAdminController create lesson endpoint.
 */
final class LessonAdminControllerCreateTest extends WebTestCase
{
    // Endpoint URL for creating lessons
    private const CREATE_URL = '/api/admin/lessons';

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
     * Minimal fixture for a lesson (Theme -> Cursus)
     */
    private function createCursusFixture(): Cursus
    {
        $now = new \DateTime();

        $theme = new Theme();
        $theme->setTitle('Theme test');
        $theme->setDescription('Description test');
        $theme->setSlug('theme-test');
        $theme->setCreatedAt($now);
        $theme->setUpdatedAt($now);
        $this->em->persist($theme);

        $cursus = new Cursus();
        $cursus->setTitle('Cursus test');
        $cursus->setDescription('Description test');
        $cursus->setPrice(10);
        $cursus->setIsActive(true);
        $cursus->setTheme($theme);
        $cursus->setCreatedAt($now);
        $cursus->setUpdatedAt($now);
        $this->em->persist($cursus);

        $this->em->flush();

        return $cursus;
    }

    /**
     * Ensures that an authenticated admin user can create a new lesson.
     */
    public function testAdminCanCreateLesson(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        // Clean tables
        $this->em->createQuery('DELETE FROM App\Entity\Lesson l')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Cursus c')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Theme t')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create admin user and cursus fixture
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $cursus = $this->createCursusFixture();

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Call create endpoint
        $client->request(
            'POST',
            self::CREATE_URL,
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'title' => 'New lesson',
                'content' => 'This is the lesson content (min 10 chars).',
                'price' => 15,
                'cursusId' => $cursus->getId(),
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // Validate response structure
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('New lesson', $data['title']);
        $this->assertSame(15, $data['price']);

        // Verify DB side
        $repo = $this->em->getRepository(Lesson::class);
        $created = $repo->find($data['id']);
        $this->assertNotNull($created);
        $this->assertSame('New lesson', $created->getTitle());
        $this->assertSame(15, $created->getPrice());
        $this->assertTrue($created->isActive());
        $this->assertSame($cursus->getId(), $created->getCursus()->getId());
    }

    /**
     * Ensures that an authenticated non-admin user cannot create a new lesson.
     */
    public function testUserCannotCreateLesson(): void
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

        // Attempt to call create endpoint
        $client->request(
            'POST',
            self::CREATE_URL,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode([
                'title' => 'New lesson',
                'content' => 'This is the lesson content (min 10 chars).',
                'price' => 15,
                'cursusId' => 1,
            ], JSON_THROW_ON_ERROR)
        );

        // Is granted must block
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Ensures that an anonymous user cannot create a new lesson.
     */
    public function testAnonymousCannotCreateLesson(): void
    {
        $client = static::createClient();

        // No authentication performed
        $client->request(
            'POST',
            self::CREATE_URL,
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'title' => 'New lesson',
                'content' => 'This is the lesson content (min 10 chars).',
                'price' => 15,
                'cursusId' => 1,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Ensures that an authenticated admin user gets validation errors when creating a lesson with invalid data.
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

        // Call create endpoint with invalid data
        $client->request(
            'POST',
            self::CREATE_URL,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode([
                'title' => '',
                'content' => 'short',
                'price' => -1,
                'cursusId' => null,
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

        // Call create endpoint with invalid JSON
        $client->request(
            'POST',
            self::CREATE_URL,
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: '{invalid-json'
        );

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('JSON invalide.', $data['message']);
    }
}
