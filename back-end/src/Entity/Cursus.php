<?php

namespace App\Entity;

use App\Repository\CursusRepository;
use App\Entity\Theme;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\BlameableTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CursusRepository::class)]
#[ORM\Table(name: 'cursus')]
#[ORM\HasLifecycleCallbacks]
class Cursus
{
    use TimestampableTrait;
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    //Relations
    #[ORM\ManyToOne(targetEntity: Theme::class)]
    #[ORM\JoinColumn(name: 'theme_id', referencedColumnName: 'id', nullable: false)]
    private ?Theme $theme = null;

    #[ORM\OneToMany(mappedBy: 'cursus', targetEntity: Lesson::class, orphanRemoval: false)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $lessons;

    //Attributes
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column(name: 'is_active', options: ['default' => true])]
    private ?bool $isActive = true;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
    }

    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
