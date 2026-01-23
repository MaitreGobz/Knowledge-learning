<?php

namespace App\Service\Payment;

use Stripe\StripeClient as StripeSdkClient;

final class StripeClient implements StripeClientInterface
{
    public function __construct(private readonly StripeSdkClient $stripe) {}


    public function createCheckoutSession(array $payload): array
    {
        $session = $this->stripe->checkout->sessions->create($payload);

        return [
            'id' => $session->id,
            'url' => $session->url,
        ];
    }
}
