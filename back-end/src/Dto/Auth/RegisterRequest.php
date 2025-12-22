<?php

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterRequest
{
    //DTO + Validator for auth
    #[Assert\NotBlank(message: "Tous les champs doivent être remplis")]
    #[Assert\Email(message: "L'email doit être valide")]
    public ?string $email = null;

    #[Assert\NotBlank(message: "Tous les champs doivent être remplis")]
    #[Assert\Length(min: 8, minMessage: "Le mot de passe doit contenir au moins 8 caractères")]
    public ?string $password = null;
}
