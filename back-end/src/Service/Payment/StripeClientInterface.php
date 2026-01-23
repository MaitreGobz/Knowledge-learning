<?php

namespace App\Service\Payment;

interface StripeClientInterface
{
    /**
     * @param array<string, mixed> $payload
     * @return array{id: string, url: string}
     */
    public function createCheckoutSession(array $payload): array;
}
