<?php

// src/Entity/User/UserDelete.php

namespace App\Entity\User;

use App\Repository\User\UserDeleteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserDeleteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'user_delete')]
class UserDelete
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'udlt_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(
        targetEntity: User::class,
        inversedBy: 'userDelete',
        cascade: ['persist']
    )]
    #[ORM\JoinColumn(
        name: 'fk_user_id',
        referencedColumnName: 'user_id',
        onDelete: 'CASCADE',
        nullable: false
    )]
    private ?User $users = null;

    #[ORM\Column(nullable: true)]
    private ?bool $toBeDeleted = null;

    #[ORM\Column(name: 'requested', type: 'datetimetz_immutable')]
    private ?\DateTimeImmutable $requested = null;

    #[ORM\Column(type: 'datetimetz', nullable: true)]
    private ?\DateTimeInterface $deletionDate = null;

    #[ORM\Column(type: 'datetimetz', nullable: true)]
    private ?\DateTimeInterface $undoDeletionDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function isToBeDeleted(): ?bool
    {
        return $this->toBeDeleted;
    }

    public function setToBeDeleted(?bool $toBeDeleted): static
    {
        $this->toBeDeleted = $toBeDeleted;

        return $this;
    }

    public function getrequested(): ?\DateTimeImmutable
    {
        return $this->requested;
    }

    #[ORM\PrePersist]
    public function setrequested()
    {
        // $this->requested = $requested;
        $this->requested = new \DateTimeImmutable('now');

        return $this;
    }

    public function getDeletionDate(): ?\DateTimeInterface
    {
        return $this->deletionDate;
    }

    public function setDeletionDate(?\DateTimeInterface $deletionDate): self
    {
        $this->deletionDate = $deletionDate;

        return $this;
    }

    public function getUndoDeletionDate(): ?\DateTimeInterface
    {
        return $this->undoDeletionDate;
    }

    public function setUndoDeletionDate(?\DateTimeInterface $undoDeletionDate): self
    {
        $this->undoDeletionDate = $undoDeletionDate;

        return $this;
    }
}
