<?php

namespace App\Tests\Controller\Api\Certification;

use App\Entity\Certification;
use App\Entity\Theme;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests for certifications endpoint.
 */
final class CertificationControllerTest extends WebTestCase
{
    /**
     * Create a user in DB and login via /api/auth/login.
     */
    private function createUserAndLogin($client, string $email, string $plainPassword, array $roles): User
    {
        $container = static::getContainer();

        // Create user
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
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode(
                ['email' => $email, 'password' => $plainPassword],
                JSON_THROW_ON_ERROR
            )
        );

        self::assertResponseStatusCodeSame(200);

        return $user;
    }

    /**
     * Anonymous user cannot list certifications.
     */
    public function testAnonymousCannotListCertifications(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/certifications');

        self::assertResponseStatusCodeSame(401);
    }

    /**
     * Authenticated user without certifications gets an empty list.
     */
    public function testUserWithoutCertificationsGetsEmptyList(): void
    {
        $client = static::createClient();

        // Create and login user
        $this->createUserAndLogin(
            $client,
            'user_no_cert_' . uniqid() . '@test.com',
            'User123!',
            ['ROLE_USER']
        );

        // Call API
        $client->request('GET', '/api/certifications');

        self::assertResponseStatusCodeSame(200);

        // Get and check response
        $response = $client->getResponse();
        self::assertTrue(
            str_contains((string) $response->headers->get('Content-Type'), 'application/json'),
            'La réponse /api/certifications doit être au format JSON.'
        );

        // Get content
        $content = (string) $response->getContent();
        self::assertNotSame('', $content);

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($data);
        self::assertSame([], $data);
    }

    /**
     * Authenticated user with certifications gets a non-empty list.
     */
    public function testUserWithCertificationsGetsList(): void
    {
        $client = static::createClient();

        // Create and login user
        $user = $this->createUserAndLogin(
            $client,
            'user_with_cert_' . uniqid() . '@test.com',
            'User123!',
            ['ROLE_USER']
        );

        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        // Create a theme
        $theme = new Theme();
        $theme->setTitle('Informatique');
        $theme->setDescription('Thème informatique');
        $theme->setSlug('informatique_' . uniqid());

        $em->persist($theme);

        // Create a certification
        $certification = new Certification();
        $certification->setUser($user);
        $certification->setTheme($theme);
        $certification->setValidatedAt(new \DateTime('2024-01-15 10:00:00'));

        $em->persist($certification);
        $em->flush();

        // Call API
        $client->request('GET', '/api/certifications');

        self::assertResponseStatusCodeSame(200);

        $response = $client->getResponse();
        self::assertTrue(
            str_contains((string) $response->headers->get('Content-Type'), 'application/json'),
            'La réponse /api/certifications doit être au format JSON.'
        );

        $content = (string) $response->getContent();
        self::assertNotSame('', $content);

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        // Assertions on data structure
        self::assertIsArray($data);
        self::assertCount(1, $data);

        self::assertArrayHasKey('themeId', $data[0]);
        self::assertArrayHasKey('themeTitle', $data[0]);
        self::assertArrayHasKey('validatedAt', $data[0]);

        self::assertSame($theme->getId(), $data[0]['themeId']);
        self::assertSame('Informatique', $data[0]['themeTitle']);
        self::assertNotEmpty($data[0]['validatedAt']);
    }
}
