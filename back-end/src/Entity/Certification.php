<?php

namespace App\Entity;

use App\Repository\CertificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertificationRepository::class)]
#[ORM\Table(name: 'certifications',
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_cert_user_theme', columns: ['user_id', 'theme_id'])]
    )]
class Certification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    //Relations
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Theme::class)]
    #[ORM\JoinColumn(name: 'theme_id', referencedColumnName: 'id', nullable: false)]
    private Theme $theme;

    //Audit fields//Attributes
    #[ORM\Column(name: 'validated_at')]
    private ?\DateTime $validatedAt = null;
    
    //Audit fields
    #[ORM\Column(name: 'created_at')]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(name: 'updated_at')]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(name: 'created_by', nullable: true)]
    private ?int $createdBy = null;

    #[ORM\Column(name: 'updated_by', nullable: true)]
    private ?int $updatedBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValidatedAt(): ?\DateTime
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(\DateTime $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy(): ?int
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?int $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }
}
