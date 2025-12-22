<?php

namespace App\Tests\Api\Auth;

use App\Entity\User;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RegisterTest extends WebTestCase
{
    private EntityManagerInterface $em;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        // Ensures that no previous Kernel instance leaks between tests
        self::ensureKernelShutdown();

        // Boot the Symfony kernel and create an HTTP client for functional tests
        $this->client = static::createClient();

        // Fetch Doctrine EntityManager from the service container
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testRegisterSucces(): void
    {
        $payload = [
            'email' => 'newuser@test.com',
            'password' => 'User123!',
        ];

        $this->client->request(
            method: 'POST',
            uri: '/api/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload)
        );

        // 201 Created
        $this->assertResponseStatusCodeSame(201);

        // Validate response JSON contract
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('newuser@test.com', $data['email']);
        $this->assertSame('PENDING_VERIFICATION', $data['status']);

        // Validate database state
        /** @var User|null $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'newuser@test.com']);

        $this->assertNotNull($user, 'Utilisateur existe en base de donnée après création.');

        // Hash password
        $this->assertNotSame('User123!', $user->getPassword());
        $this->assertNotEmpty($user->getPassword());

        // Newly registered users must not be verified yet
        $this->assertFalse($user->isVerified());

        // Activation token must be generated and stored
        $this->assertNotNull($user->getToken());
        $this->assertNotEmpty($user->getToken());

        // Expiration must be set
        $this->assertNotNull($user->getTokenExpiresAt());
    }

    public function testRegisterEmailAlreadyExists(): void
    {

        // Insert an existing user in database
        $existing = new User();
        $existing->setEmail('exists@test.com');

        // Need a placeholder password value.
        $existing->setPassword('hashed-password-placeholder');

        $existing->setRoles(['ROLE_USER']);
        $existing->setIsActive(true);
        $existing->setIsVerified(false);

        $this->em->persist($existing);
        $this->em->flush();

        // Attempt to register with the same email
        $payload = [
            'email' => 'exists@test.com',
            'password' => 'User123!',
        ];

        $this->client->request(
            method: 'POST',
            uri: '/api/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload)
        );

        // 409 Conflict
        $this->assertResponseStatusCodeSame(409);

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame('Email déjà existant.', $data['message']);
    }

    public function testRegisterPasswordInvalid(): void
    {

        // Password too short
        $payload = [
            'email' => 'weak@test.com',
            'password' => '123',
        ];

        $this->client->request(
            method: 'POST',
            uri: '/api/auth/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload)
        );

        // 422 Unprocessable Entity
        $this->assertResponseStatusCodeSame(422);

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('errors', $data);

        // We expect a "password" key in the validation errors
        $this->assertArrayHasKey('password', $data['errors']);

        // Adjust this assertion if your DTO error message differs
        $this->assertContains(
            'Le mot de passe doit contenir au moins 8 caractères',
            $data['errors']['password']
        );
    }
}
