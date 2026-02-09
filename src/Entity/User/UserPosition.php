<?php

// src/Entity/User/UserPosition.php

namespace App\Entity\User;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\User\UserPositionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPositionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class UserPosition
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'up_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $jobPosition = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'userPosition', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'fk_user_id', referencedColumnName: 'user_id', nullable: false)]
    private ?User $users = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobPosition(): ?string
    {
        return $this->jobPosition;
    }

    public function setJobPosition(?string $jobPosition): static
    {
        $this->jobPosition = $jobPosition;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(User $users): static
    {
        $this->users = $users;

        return $this;
    }
}
