<?php

namespace App\Tests\Double;

use App\Service\Payment\StripeClientInterface;

final class StripeClientFake implements StripeClientInterface
{
    public function createCheckoutSession(array $payload): array
    {
        return [
            'id' => 'cs_test_123',
            'url' => 'https://checkout.stripe.com/test',
        ];
    }
}
