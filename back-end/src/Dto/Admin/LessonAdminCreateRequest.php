<?php

namespace App\Dto\Admin;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for creating a lesson via admin API
 */
final class LessonAdminCreateRequest
{
    #[Assert\NotBlank(message: 'Titre requis.')]
    #[Assert\Length(min: 3, minMessage: 'Le titre doit faire au moins 3 caractères.')]
    public ?string $title = null;

    #[Assert\NotBlank(message: 'Contenu requis.')]
    #[Assert\Length(min: 10, minMessage: 'Le contenu doit faire au moins 10 caractères.')]
    public ?string $content = null;

    #[Assert\NotNull(message: 'Prix requis.')]
    #[Assert\Type(type: 'integer', message: 'Le prix doit être un entier.')]
    #[Assert\PositiveOrZero(message: 'Le prix doit être un entier positif.')]
    public ?int $price = null;

    #[Assert\NotNull(message: 'Cursus requis.')]
    #[Assert\Type(type: 'integer', message: 'Le cursus doit être un identifiant numérique.')]
    #[Assert\Positive(message: 'Le cursus doit être valide.')]
    public ?int $cursusId = null;

    /**
     * Normalize input values
     */
    public function normalize(): void
    {
        if ($this->title !== null) {
            $this->title = trim($this->title);
        }
        if ($this->content !== null) {
            $this->content = trim($this->content);
        }
    }
}
