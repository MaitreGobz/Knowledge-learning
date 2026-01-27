<?php

namespace App\Tests\Controller\Api\Lesson;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Minimal tests for lesson access control (401/403/200 admin).
 */
final class LessonControllerTest extends WebTestCase
{
    /**
     * Get first cursus id from /api/themes fixtures.
     */
    private function getFirstCursusIdFromApi($client): int
    {
        // Request themes
        $client->request('GET', '/api/themes');
        self::assertResponseIsSuccessful();

        $themesResponse = $client->getResponse();
        self::assertTrue(
            str_contains((string) $themesResponse->headers->get('Content-Type'), 'application/json'),
            'La réponse /api/themes doit être au format JSON.'
        );

        // Get content and decode
        $themesContent = (string) $themesResponse->getContent();
        self::assertNotSame('', $themesContent);

        $themesData = json_decode($themesContent, true, 512, JSON_THROW_ON_ERROR);

        // Assertions on structure
        self::assertIsArray($themesData);
        self::assertNotEmpty($themesData);

        $cursusId = null;

        // Find first cursus id
        foreach ($themesData as $theme) {
            if (!isset($theme['cursus']) || !is_array($theme['cursus'])) {
                continue;
            }

            if (!empty($theme['cursus']) && isset($theme['cursus'][0]['id'])) {
                $cursusId = $theme['cursus'][0]['id'];
                break;
            }
        }

        self::assertNotNull($cursusId, 'Aucun cursus trouvé via /api/themes.');

        return (int) $cursusId;
    }

    /**
     * Get first lesson id from /api/cursus/{id} JSON.
     */
    private function getFirstLessonIdFromCursusApi($client, int $cursusId): int
    {
        // Request cursus details
        $client->request('GET', '/api/cursus/' . $cursusId);
        self::assertResponseIsSuccessful();

        $response = $client->getResponse();
        self::assertTrue(
            str_contains((string) $response->headers->get('Content-Type'), 'application/json'),
            'La réponse /api/cursus/{id} doit être au format JSON.'
        );

        // Get content and decode
        $content = (string) $response->getContent();
        self::assertNotSame('', $content);

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        // Assertions on structure
        self::assertIsArray($data);
        self::assertArrayHasKey('lessons', $data);
        self::assertIsArray($data['lessons']);
        self::assertNotEmpty($data['lessons']);
        self::assertArrayHasKey('id', $data['lessons'][0]);

        // Return first lesson id
        return (int) $data['lessons'][0]['id'];
    }

    /**
     * Create a user in DB (minimal) and login via /api/auth/login.
     */
    private function createUserAndLogin($client, string $email, string $plainPassword, array $roles): void
    {
        $container = static::getContainer();

        // Create user in DB
        $em = $container->get(EntityManagerInterface::class);
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setIsVerified(true);
        $user->setIsActive(true);
        $user->setPassword($hasher->hashPassword($user, $plainPassword));

        $em->persist($user);
        $em->flush();

        // Login
        $client->request(
            'POST',
            '/api/auth/login',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode(['email' => $email, 'password' => $plainPassword], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Anonymous user gets 401 when accessing a lesson.
     */
    public function testAnonymousCannotAccessLesson(): void
    {
        $client = static::createClient();

        $cursusId = $this->getFirstCursusIdFromApi($client);
        $lessonId = $this->getFirstLessonIdFromCursusApi($client, $cursusId);

        $client->request('GET', '/api/lessons/' . $lessonId);

        self::assertResponseStatusCodeSame(401);
    }

    /**
     * User without access right gets 403 when accessing a lesson.
     */
    public function testUserWithoutAccessRightGets403(): void
    {
        $client = static::createClient();

        $this->createUserAndLogin($client, 'user_no_access_' . uniqid() . '@test.com', 'User123!', ['ROLE_USER']);

        $cursusId = $this->getFirstCursusIdFromApi($client);
        $lessonId = $this->getFirstLessonIdFromCursusApi($client, $cursusId);

        $client->request('GET', '/api/lessons/' . $lessonId);

        self::assertResponseStatusCodeSame(403);
    }

    /**
     * Admin user can access a lesson without purchase.
     */
    public function testAdminCanAccessLessonWithoutPurchase(): void
    {
        $client = static::createClient();

        $this->createUserAndLogin($client, 'admin_' . uniqid() . '@test.com', 'Admin123!', ['ROLE_ADMIN']);

        $cursusId = $this->getFirstCursusIdFromApi($client);
        $lessonId = $this->getFirstLessonIdFromCursusApi($client, $cursusId);

        $client->request('GET', '/api/lessons/' . $lessonId);

        self::assertResponseStatusCodeSame(200);
    }
}
