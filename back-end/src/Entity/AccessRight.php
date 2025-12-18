<?php

namespace App\Entity;

use App\Repository\AccessRightRepository;
use App\Entity\User;
use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Entity\Purchase;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\BlameableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessRightRepository::class)]
#[ORM\Table(
    name: 'access_rights', 
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_access_right', columns: ['user_id', 'cursus_id'])]
    )]
class AccessRight
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
    #[ORM\JoinColumn(name: 'cursus_id', referencedColumnName: 'id', nullable: true)]
    private ?Cursus $cursus = null;

    #[ORM\ManyToOne(targetEntity: Lesson::class)]
    #[ORM\JoinColumn(name: 'lesson_id', referencedColumnName: 'id', nullable: true)]
    private ?Lesson $lesson = null;

    #[ORM\ManyToOne(targetEntity: Purchase::class)]
    #[ORM\JoinColumn(name: 'purchase_id', referencedColumnName: 'id', nullable: true)]
    private ?Purchase $purchase = null;

    //Attributes
    #[ORM\Column(name: 'granted_at')]
    private ?\DateTime $grantedAt = null;

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
}
