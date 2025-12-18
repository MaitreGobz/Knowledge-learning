<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

// Trait to add createdBy and updatedBy fields to an entity
trait BlameableTrait
{
    #[ORM\Column(name: 'created_by', type: 'string', length: 180, nullable: true)]
    private ?string $createdBy = null;

    #[ORM\Column(name: 'updated_by', type: 'string', length: 180, nullable: true)]
    private ?string $updatedBy = null;

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): static
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }
}
