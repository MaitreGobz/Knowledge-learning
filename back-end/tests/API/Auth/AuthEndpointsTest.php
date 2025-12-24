<?php

namespace App\Tests\API\Auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Minimalist functional tests of authentication endpoints.
 */
final class AuthEndpointsTest extends WebTestCase
{
    // Symfony HTTP client used to simulate API requests
    private KernelBrowser $client;
    // EntityManager for manipulating the test database
    private EntityManagerInterface $em;
    // Password hashing service
    private UserPasswordHasherInterface $hasher;

    /**
     * Method performed before each test 
     */
    protected function setUp(): void
    {
        // Ensures that no previous kernel is still active
        self::ensureKernelShutdown();

        // Start the kernel and create a test HTTP client
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        // Services needed for testing
        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        // Prepare a verified user for login tests
        $this->seedVerifiedUser();
    }

    /**
     *  Create a verified user in the test database
     */
    private function seedVerifiedUser(): void
    {
        $repo = $this->em->getRepository(User::class);

        // We avoid recreating the user for each test
        $existing = $repo->findOneBy(['email' => 'verified@example.com']);
        if ($existing) {
            return;
        }

        $u = new User();
        $u->setEmail('verified@example.com');
        $u->setIsVerified(true);
        $u->setPassword($this->hasher->hashPassword($u, 'Password123!'));

        $this->em->persist($u);
        $this->em->flush();
    }

    /**
     *  Verify that the CSRF endpoint correctly returns a token usable by the front-end
     */
    public function testCsrfEndpointReturnsToken(): void
    {
        // CSRF endpoint call
        $this->client->request('GET', '/api/auth/csrf');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // The content must be a JSON file containing a "csrfToken" key.
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('csrfToken', $data);
        $this->assertIsString($data['csrfToken']);
        $this->assertNotEmpty($data['csrfToken']);
    }

    public function testLoginSuccess(): void
    {
        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => 'verified@example.com', 'password' => 'Password123!'])
        );

        $status = $this->client->getResponse()->getStatusCode();

        // Json_login returns 200 or 204
        $this->assertTrue(
            in_array($status, [Response::HTTP_OK, Response::HTTP_NO_CONTENT], true),
            'Expected 200 or 204, got ' . $status
        );
    }
}
