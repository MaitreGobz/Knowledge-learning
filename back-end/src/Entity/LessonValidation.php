<?php

namespace App\Entity;

use App\Repository\LessonValidationRepository;
use App\Entity\User;
use App\Entity\Lesson;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\BlameableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonValidationRepository::class)]
#[ORM\Table(
    name: 'lesson_validations',
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_lv_user_lesson', columns: ['user_id', 'lesson_id'])]
)]
#[ORM\HasLifecycleCallbacks]
class LessonValidation
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

    #[ORM\ManyToOne(targetEntity: Lesson::class)]
    #[ORM\JoinColumn(name: 'lesson_id', referencedColumnName: 'id', nullable: false)]
    private Lesson $lesson;

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
}
