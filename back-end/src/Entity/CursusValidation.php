<?php

namespace App\Entity;

use App\Repository\CursusValidationRepository;
use App\Entity\User;
use App\Entity\Cursus;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\BlameableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CursusValidationRepository::class)]
#[ORM\Table(name: 'cursus_validations')]
#[ORM\UniqueConstraint(name: 'uniq_cv_user_cursus', columns: ['user_id', 'cursus_id'])]
#[ORM\HasLifecycleCallbacks]
class CursusValidation
{
    use TimestampableTrait;
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    //Relations
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Cursus::class)]
    #[ORM\JoinColumn(name: 'cursus_id', referencedColumnName: 'id', nullable: false)]
    private Cursus $cursus;

    //Attributes
    #[ORM\Column(name: 'validated_at')]
    private ?\DateTime $validatedAt = null;

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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCursus(): Cursus
    {
        return $this->cursus;
    }

    public function setCursus(Cursus $cursus): static
    {
        $this->cursus = $cursus;

        return $this;
    }
}
