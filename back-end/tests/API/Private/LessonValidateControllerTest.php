<?php

namespace App\Tests\Controller\Api\Progress;

use App\Entity\AccessRight;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 *  Tests for lesson validation endpoint
 */
final class LessonValidateControllerTest extends WebTestCase
{
    /**
     * Get first cursus id from /api/themes fixtures.
     */
    private function getFirstCursusIdFromApi($client): int
    {
        $client->request('GET', '/api/themes');
        self::assertResponseIsSuccessful();

        //
        $response = $client->getResponse();
        self::assertTrue(
            str_contains((string) $response->headers->get('Content-Type'), 'application/json'),
            'La réponse /api/themes doit être au format JSON.'
        );

        // Get content and decode
        $content = (string) $response->getContent();
        self::assertNotSame('', $content);

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        // Assertions on structure
        self::assertIsArray($data);
        self::assertNotEmpty($data);

        $cursusId = null;

        // Find first cursus id
        foreach ($data as $theme) {
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
     * Create a user in DB and login via /api/auth/login.
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
     * Fetch CSRF token from /api/auth/csrf.
     */
    private function fetchCsrfToken($client): string
    {
        $client->request('GET', '/api/auth/csrf', server: ['HTTP_ACCEPT' => 'application/json']);
        self::assertResponseIsSuccessful();

        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);
        self::assertArrayHasKey('csrfToken', $data);

        return (string) $data['csrfToken'];
    }

    /**
     * Test validating a lesson with invalid ID returns 404.
     */
    public function testValidateLessonInvalidIdReturns404(): void
    {
        $client = static::createClient();
        $this->createUserAndLogin($client, 'u404_' . uniqid() . '@test.com', 'User123!', ['ROLE_USER']);

        // Get CSRF token
        $csrf = $this->fetchCsrfToken($client);
        $headers = ['HTTP_X_CSRF_TOKEN' => $csrf];
        $headers = ['HTTP_ACCEPT' => 'application/json'];

        $client->request('POST', '/api/private/lessons/999999/validate', server: $headers);

        self::assertResponseStatusCodeSame(404);
    }

    /**
     * Test validating a lesson without purchase returns 403.
     */
    public function testValidateLessonWithoutPurchaseReturns403(): void
    {
        $client = static::createClient();
        $this->createUserAndLogin($client, 'u403_' . uniqid() . '@test.com', 'User123!', ['ROLE_USER']);

        // Get a lesson ID
        $cursusId = $this->getFirstCursusIdFromApi($client);
        $lessonId = $this->getFirstLessonIdFromCursusApi($client, $cursusId);

        // Attempt to validate lesson without access right
        $headers = ['HTTP_ACCEPT' => 'application/json'];

        $csrf = $this->fetchCsrfToken($client);
        $headers['HTTP_X_CSRF_TOKEN'] = $csrf;

        $client->request('POST', '/api/private/lessons/' . $lessonId . '/validate', server: $headers);

        self::assertResponseStatusCodeSame(403);
    }
}
