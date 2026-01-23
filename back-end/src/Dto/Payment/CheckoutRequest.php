<?php

namespace App\Dto\Payment;

use Symfony\Component\Validator\Constraints as Assert;

final class CheckoutRequest
{
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['cursus', 'lesson'])]
    public string $type;

    #[Assert\Positive]
    public int $itemId;
}
