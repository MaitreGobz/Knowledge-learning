<?php

namespace App\Entity;

use App\Repository\CertificationRepository;
use App\Entity\User;
use App\Entity\Theme;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\BlameableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertificationRepository::class)]
#[ORM\Table(name: 'certifications')]
#[ORM\UniqueConstraint(name: 'uniq_cert_user_theme', columns: ['user_id', 'theme_id'])]
#[ORM\HasLifecycleCallbacks]
class Certification
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

    #[ORM\ManyToOne(targetEntity: Theme::class)]
    #[ORM\JoinColumn(name: 'theme_id', referencedColumnName: 'id', nullable: false)]
    private Theme $theme;

    //Audit fields//Attributes
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

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    public function setTheme(Theme $theme): static
    {
        $this->theme = $theme;

        return $this;
    }
}
