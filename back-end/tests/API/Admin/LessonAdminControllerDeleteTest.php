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
 * Tests for the LessonAdminController delete endpoint
 */
final class LessonAdminControllerDeleteTest extends WebTestCase
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
     * Ensures that an authenticated admin user can soft delete a lesson (DELETE -> isActive=false).
     */
    public function testAdminCanDeleteLesson(): void
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

        // Create an admin + target lesson
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $target = $this->createLessonFixture();

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Call the delete endpoint
        $client->request(
            'DELETE',
            '/api/admin/lessons/' . $target->getId(),
            server: ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Ensures that a non-admin user cannot delete a lesson
     */
    public function testNonAdminCannotDeleteLesson(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create non-admin user + target lesson
        $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);
        $target = $this->createLessonFixture();

        // Authenticate as non-admin
        $this->login($client, 'user@test.com', 'User123!');

        // Call the delete endpoint
        $client->request(
            'DELETE',
            '/api/admin/lessons/' . $target->getId(),
            server: ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Ensures that an anonymous user cannot delete a lesson
     */
    public function testAnonymousCannotDeleteLesson(): void
    {
        $client = static::createClient();

        // No authentication performed
        $client->request(
            'DELETE',
            '/api/admin/lessons/1',
            server: ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(401);
    }
}
