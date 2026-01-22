<?php

namespace App\Dto\Admin;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO for updating a lesson via admin API
 */
final class LessonAdminUpdateRequest
{
    #[Assert\Length(min: 3, minMessage: 'Le titre doit faire au moins 3 caractères.')]
    public ?string $title = null;

    #[Assert\Length(min: 10, minMessage: 'Le contenu doit faire au moins 10 caractères.')]
    public ?string $content = null;

    #[Assert\Type(type: 'integer', message: 'Le prix doit être un entier.')]
    #[Assert\PositiveOrZero(message: 'Le prix doit être un entier positif.')]
    public ?int $price = null;

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
