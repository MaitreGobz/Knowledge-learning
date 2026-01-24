<?php

namespace App\Tests\API\Payment;

use App\Entity\Purchase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests for Stripe webhook endpoint.
 */
final class StripeWebhookControllerTest extends WebTestCase
{
    // Endpoint URL for Stripe webhook
    private const WEBHOOK_URL = '/api/stripe/webhook';

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
     * Ensure webhook rejects invalid or missing Stripe signature.
     */
    public function testWebhookRejectsInvalidSignature(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->hasher = $container->get(UserPasswordHasherInterface::class);

        // Clean purchases + users
        $this->em->createQuery('DELETE FROM App\Entity\Purchase p')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();

        // Create a user just to ensure DB is initialized (not strictly required)
        $this->createUser('user@test.com', 'User123!', ['ROLE_USER']);

        // Call webhook WITHOUT Stripe-Signature header
        $client->request(
            'POST',
            self::WEBHOOK_URL,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode(['type' => 'checkout.session.completed'], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(400);

        // Ensure no Purchase was created
        $count = (int) $this->em->getRepository(Purchase::class)->count([]);
        $this->assertSame(0, $count, 'Aucun achat ne doit être créé si la signature est invalide.');
    }
}
