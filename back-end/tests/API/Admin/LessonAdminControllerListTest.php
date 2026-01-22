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
 * Tests for the LessonAdminController List API endpoint
 */
final class LessonAdminControllerListTest extends WebTestCase
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
        $user->setIsActive(true);

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
     * Ensures that an authenticated admin user can access
     * the admin lessons listing endpoint.
     */
    public function testAdminCanListLessons(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        // Clean tables (order matters because of FK)
        $this->em->createQuery('DELETE FROM App\Entity\Lesson l')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Cursus c')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Theme t')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create an admin user and a lesson fixture
        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $this->createLessonFixture();

        // Authenticate as admin
        $this->login($client, 'admin@test.com', 'Admin123!');

        // Call the admin endpoint
        $client->request('GET', '/api/admin/lessons?page=1&limit=20', server: ['HTTP_ACCEPT' => 'application/json']);

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
     * cannot access the admin lessons listing endpoint.
     */
    public function testUserCannotListLessons(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        $this->createUser('admin@test.com', 'Admin123!', ['ROLE_ADMIN']);
        $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);

        // Authenticate as a regular user
        $this->login($client, 'user@test.com', 'User123!');

        // Attempt to access admin endpoint
        $client->request('GET', '/api/admin/lessons', server: ['HTTP_ACCEPT' => 'application/json']);

        // Access must be denied (ROLE_ADMIN required)
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Ensures that an anonymous (unauthenticated) user
     * cannot access the admin lessons listing endpoint.
     */
    public function testAnonymousCannotListLessons(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        //No authentication performed here
        $client->request('GET', '/api/admin/lessons', server: ['HTTP_ACCEPT' => 'application/json']);

        // Anonymous access must be rejected
        $this->assertResponseStatusCodeSame(401);
    }
}
