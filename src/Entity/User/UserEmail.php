<?php

// src/Entity/User/UserEmail.php

namespace App\Entity\User;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\User\UserEmailRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserEmailRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'user_emails')]
class UserEmail
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'uem_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'userEmail', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'fk_user_id', referencedColumnName: 'user_id', onDelete: 'CASCADE', nullable: false)]
    private ?User $users = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $email2 = null;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail2(): ?string
    {
        return $this->email2;
    }

    public function setEmail2(?string $email2): self
    {
        $this->email2 = $email2;

        return $this;
    }
}
