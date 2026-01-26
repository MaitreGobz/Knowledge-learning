<?php

namespace App\Service\Payment;

use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\User;
use App\Service\Payment\StripeClientInterface;

/**
 *  Service to handle checkout sessions creation
 */
final class CheckoutService
{
    public function __construct(
        private readonly StripeClientInterface $stripeClient,
        private readonly string $frontendUrl,
        private readonly string $successPath,
        private readonly string $cancelPath,
    ) {}

    // Create a checkout session for purchasing a cursus
    public function createCheckoutSessionForCursus(User $user, Cursus $cursus): array
    {
        // Ensure the cursus has a valid price
        $amount = $cursus->getPrice();
        if ($amount <= 0) {
            throw new \DomainException('Prix invalide');
        }

        // Create and return the checkout session
        return $this->create(
            user: $user,
            itemType: 'cursus',
            itemId: (int) $cursus->getId(),
            title: (string) $cursus->getTitle(),
            amount: $amount,
        );
    }

    // Create a checkout session for purchasing a lesson
    public function createCheckoutSessionForLesson(User $user, Lesson $lesson): array
    {
        // Ensure the lesson has a valid price
        $amount = $lesson->getPrice();
        if ($amount <= 0) {
            throw new \DomainException('Prix invalide');
        }

        // Create and return the checkout session
        return $this->create(
            user: $user,
            itemType: 'lesson',
            itemId: (int) $lesson->getId(),
            title: (string) $lesson->getTitle(),
            amount: $amount,
        );
    }

    // Common method to create a checkout session
    private function create(User $user, string $itemType, int $itemId, string $title, int $amount): array
    {
        // Validate minimum amount for Stripe
        $unitAmount = (int) round(((float) $amount) * 100);

        if ($unitAmount < 50) {
            throw new \DomainException('Montant minimum Stripe : 0,50 €');
        }

        // Prepare success and cancel URLs
        $successUrl = rtrim($this->frontendUrl, '/') . $this->successPath . "?session_id={CHECKOUT_SESSION_ID}";
        $cancelUrl = rtrim($this->frontendUrl, '/') . $this->cancelPath;

        // Prepare payload for Stripe checkout session
        $payload = [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $unitAmount,
                    'product_data' => [
                        'name' => $title,
                    ],
                ],
            ]],

            'metadata' => [
                'user_id' => (string) $user->getId(),
                'item_type' => $itemType,
                'item_id' => (string) $itemId,
            ],
        ];

        // Create the checkout session using the Stripe client
        $session = $this->stripeClient->createCheckoutSession($payload);

        return [
            'sessionId' => $session['id'],
            'checkoutUrl' => $session['url']
        ];
    }
}
