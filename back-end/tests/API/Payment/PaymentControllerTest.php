<?php

namespace App\Tests\API\Payment;

use App\Entity\User;
use App\Service\Payment\StripeClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests for the Payment checkout endpoint.
 */
final class PaymentControllerTest extends WebTestCase
{
    // Endpoint URL for checkout
    private const CHECKOUT_URL = '/api/payments/checkout';

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
    private function login(KernelBrowser $client, string $email, string $password): void
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
     * Fetches a CSRF token from the API CSRF endpoint.
     */
    private function getCsrfToken(KernelBrowser $client): string
    {
        $client->request(
            'GET',
            '/api/auth/csrf',
            server: ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('csrfToken', $data);
        $this->assertIsString($data['csrfToken']);
        $this->assertNotSame('', $data['csrfToken']);

        return $data['csrfToken'];
    }

    /**
     * Fetches the first available cursus ID from the /api/themes endpoint.
     */
    private function getFirstCursusIdFromApi(KernelBrowser $client): int
    {
        // Fetch themes to get a valid cursus ID from fixtures
        $client->request('GET', '/api/themes', server: ['HTTP_ACCEPT' => 'application/json']);

        // Ensure successful response
        $this->assertResponseIsSuccessful();

        // Validate response content type and structure
        $themesResponse = $client->getResponse();
        $this->assertTrue(
            str_contains((string) $themesResponse->headers->get('Content-Type'), 'application/json'),
            'La réponse /api/themes doit être au format JSON.'
        );

        // Decode and validate response data
        $themesContent = (string) $themesResponse->getContent();
        $this->assertNotSame('', $themesContent);

        $themesData = json_decode($themesContent, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($themesData);
        $this->assertNotEmpty($themesData);

        $cursusId = null;

        // Iterate themes to find a cursus ID
        foreach ($themesData as $theme) {
            if (!isset($theme['cursus']) || !is_array($theme['cursus'])) {
                continue;
            }

            if (!empty($theme['cursus']) && isset($theme['cursus'][0]['id'])) {
                $cursusId = $theme['cursus'][0]['id'];
                break;
            }
        }

        $this->assertNotNull($cursusId, 'Aucun cursus n\'a été trouvé via /api/themes. Vérifie tes fixtures.');

        return (int) $cursusId;
    }

    /**
     * Mocks the StripeClientInterface to avoid real API calls during tests.
     */
    private function mockStripe(): void
    {
        static::getContainer()->set(
            StripeClientInterface::class,
            new class implements StripeClientInterface {
                public function createCheckoutSession(array $payload): array
                {
                    return [
                        'id' => 'cs_test_123',
                        'url' => 'https://checkout.stripe.com/test',
                    ];
                }
            }
        );
    }

    /**
     * Ensure that an authenticated user can create a checkout session for a cursus.
     */
    public function testUserCanCreateCheckoutSessionForCursus(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        // Clean existing users and purchases
        $this->em->createQuery('DELETE FROM App\Entity\Purchase p')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Mock Stripe API
        $this->mockStripe();

        // Create user and authenticate
        $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);
        $this->login($client, 'user@test.com', 'User123!');

        // Get CSRF token
        $csrfToken = $this->getCsrfToken($client);

        // Get a valid cursus id from fixtures via API
        $cursusId = $this->getFirstCursusIdFromApi($client);

        // Call checkout endpoint
        $client->request(
            'POST',
            self::CHECKOUT_URL,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_CSRF_TOKEN' => $csrfToken,
            ],
            content: json_encode([
                'type' => 'cursus',
                'itemId' => $cursusId,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // Validate response structure
        $this->assertArrayHasKey('sessionId', $data);
        $this->assertArrayHasKey('checkoutUrl', $data);

        $this->assertSame('cs_test_123', $data['sessionId']);
        $this->assertSame('https://checkout.stripe.com/test', $data['checkoutUrl']);
    }

    /**
     * Ensure that an anonymous user cannot create a checkout session.
     */
    public function testAnonymousCannotCreateCheckoutSession(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        // Do not clean fixtures. Only ensure Stripe mock exists if code path reaches it.
        $this->mockStripe();

        // Reuse cursus id via API
        $cursusId = $this->getFirstCursusIdFromApi($client);

        // No login, no CSRF token
        $client->request(
            'POST',
            self::CHECKOUT_URL,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode([
                'type' => 'cursus',
                'itemId' => $cursusId,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(401);
    }
}
