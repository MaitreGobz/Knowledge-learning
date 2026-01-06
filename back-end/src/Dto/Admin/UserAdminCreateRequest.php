<?php

namespace App\Dto\Admin;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for creating a user via admin API
 */
final class UserAdminCreateRequest
{
    #[Assert\NotBlank(message: 'Email requis.')]
    #[Assert\Email(message: 'Format d\'email invalide.')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Mot de passe requis.')]
    #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit faire au moins 8 caractères.')]
    public ?string $password = null;

    #[Assert\NotNull(message: 'Un rôle doit être fourni.')]
    #[Assert\Count(
        min: 1,
        max: 1,
        exactMessage: 'Un seul rôle doit être choisi.'
    )]
    #[Assert\Type('array', message: 'roles doit être un tableau.')]
    public ?array $roles = null;

    #[Assert\Type('bool')]
    public ?bool $isActive = null;

    #[Assert\Type('bool')]
    public ?bool $isVerified = null;

    /**
     * Normalize and set default values
     */
    public function normalize(): void
    {
        if ($this->email !== null) {
            $this->email = trim($this->email);
        }
        $this->isActive = true;
        $this->isVerified = true;
    }

    /**
     * Validate roles 
     */
    public function validateRoles(array $allowedRoles): ?string
    {
        if ($this->roles === null) {
            return null;
        }
        foreach ($this->roles as $role) {
            if (!is_string($role) || !in_array($role, $allowedRoles, true)) {
                return 'Un rôle doit être validé.';
            }
        }
        return null;
    }
}
