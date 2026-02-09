<?php

// src/Entity/User/Reset/ResetEmailRequest.php

namespace App\Entity\User\Reset;

use App\Entity\User\User;
use App\Repository\User\Reset\ResetEmailRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ResetEmailRequestRepository::class)]
#[ORM\Table(name: '`request_email`')]
class ResetEmailRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'reset_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private ?string $selector = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private ?string $hashedToken = null;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private ?\DateTimeImmutable $requestedAt = null;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'fk_user_id', referencedColumnName: 'user_id', nullable: false)]
    #[Assert\Type(type: User::class)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSelector(): ?string
    {
        return $this->selector;
    }

    public function setSelector(string $selector): self
    {
        $this->selector = $selector;

        return $this;
    }

    public function getHashedToken(): ?string
    {
        return $this->hashedToken;
    }

    public function setHashedToken(string $hashedToken): self
    {
        $this->hashedToken = $hashedToken;

        return $this;
    }

    public function getRequestedAt(): ?\DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeImmutable $requestedAt): self
    {
        // $this->requestedAt = $requestedAt;
        $this->requestedAt = new \DateTimeImmutable('now');

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): self
    {
        // $this->expiresAt = $expiresAt;
        $this->expiresAt = new \DateTimeImmutable('now');

        return $this;
    }

    public function getUser(): object
    {
        return $this->user;
    }

    // public function getUsers(): ?User
    // {
    //     return $this->users;
    // }
    //
    // public function setUsers(?User $users): self
    // {
    //     $this->users = $users;
    //
    //     return $this;
    // }
}
