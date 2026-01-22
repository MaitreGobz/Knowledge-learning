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
 * Tests for the LessonAdminController update endpoint
 */
final class LessonAdminControllerUpdateTest extends WebTestCase
{
    // Endpoint URL for updating lessons
    private const UPDATE_URL = '/api/admin/lessons';

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
     * Minimal fixture for a lesson (Theme -> Cursus -> Lesson)
     */
    private function createLessonFixture(): Lesson
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

        $lesson = new Lesson();
        $lesson->setTitle('Lesson test');
        $lesson->setContent('Content for lesson test');
        $lesson->setPrice(10);
        $lesson->setPosition(1);
        $lesson->setIsActive(true);
        $lesson->setCursus($cursus);
        $lesson->setCreatedAt($now);
        $lesson->setUpdatedAt($now);

        $this->em->persist($lesson);
        $this->em->flush();

        return $lesson;
    }

    /**
     * Ensures that an authenticated admin user can update a lesson.
     */
    public function testAdminCanUpdateLesson(): void
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

        // Create admin + target lesson
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $target = $this->createLessonFixture();

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Call update endpoint
        $client->request(
            'PATCH',
            self::UPDATE_URL . '/' . $target->getId(),
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'title' => 'Updated lesson',
                'content' => 'Updated content for lesson (min 10 chars).',
                'price' => 20,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // Validate response structure
        $this->assertSame($target->getId(), $data['id']);
        $this->assertSame('Updated lesson', $data['title']);
        $this->assertSame(20, $data['price']);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);

        // isActive must NOT be changed by PATCH
        $this->em->clear();
        $reloaded = $this->em->getRepository(Lesson::class)->find($target->getId());
        $this->assertTrue($reloaded->isActive());
    }

    /**
     * Ensures that an authenticated non-admin user cannot update a lesson.
     */
    public function testUserCannotUpdateLesson(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create a non-admin + target user
        $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);
        $target = $this->createLessonFixture();

        // Authenticate as regular user
        $this->login($client, 'user@test.com', 'User123!');

        // Attempt to access admin endpoint
        $client->request(
            'PATCH',
            self::UPDATE_URL . '/' . $target->getId(),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode(['title' => 'Nope'], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Ensures that an anonymous user cannot update a lesson.
     */
    public function testAnonymousCannotUpdateLesson(): void
    {
        $client = static::createClient();

        // No authentication performed
        $client->request(
            'PATCH',
            self::UPDATE_URL . '/1',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode(['title' => 'Nope'], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Ensure that admin gets validation errors when updating with invalid data.
     */
    public function testAdminValidationErrors(): void
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

        // Create admin + target lesson
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $target = $this->createLessonFixture();

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Call update endpoint with invalid data
        $client->request(
            'PATCH',
            self::UPDATE_URL . '/' . $target->getId(),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode([
                'title' => 'a',
                'content' => 'short',
                'price' => -10,
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

        // Clean tables
        $this->em->createQuery('DELETE FROM App\Entity\Lesson l')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Cursus c')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Theme t')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create admin + target lesson
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $target = $this->createLessonFixture();

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Call update endpoint with invalid JSON
        $client->request(
            'PATCH',
            self::UPDATE_URL . '/' . $target->getId(),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: '{invalid-json'
        );

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('JSON invalide.', $data['message']);
    }

    /**
     * Ensure that admin receives 404 for a non-existing lesson id.
     */
    public function testShowLessonNotFound(): void
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

        // Non-existing lesson ID
        $client->request(
            'PATCH',
            self::UPDATE_URL . '/999999',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            content: json_encode(['title' => 'Updated'], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(404);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Leçon introuvable.', $data['message']);
    }
}
