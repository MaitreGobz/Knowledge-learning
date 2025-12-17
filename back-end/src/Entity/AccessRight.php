<?php

namespace App\Entity;

use App\Repository\AccessRightRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessRightRepository::class)]
#[ORM\Table(
    name: 'access_rights', 
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_access_right', columns: ['user_id', 'cursus_id'])]
    )]
class AccessRight
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Cursus::class)]
    #[ORM\JoinColumn(name: 'cursus_id', referencedColumnName: 'id', nullable: true)]
    private ?Cursus $cursus = null;

    #[ORM\ManyToOne(targetEntity: Lesson::class)]
    #[ORM\JoinColumn(name: 'lesson_id', referencedColumnName: 'id', nullable: true)]
    private ?Lesson $lesson = null;

    #[ORM\ManyToOne(targetEntity: Purchase::class)]
    #[ORM\JoinColumn(name: 'purchase_id', referencedColumnName: 'id', nullable: true)]
    private ?Purchase $purchase = null;

    #[ORM\Column(name: 'granted_at')]
    private ?\DateTime $grantedAt = null;

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

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getGrantedAt(): ?\DateTime
    {
        return $this->grantedAt;
    }

    public function setGrantedAt(\DateTime $grantedAt): static
    {
        $this->grantedAt = $grantedAt;

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
