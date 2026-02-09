<?php

// src/Entity/User/UserDeactivate.php

namespace App\Entity\User;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\User\UserDeactivateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserDeactivateRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class UserDeactivate
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'udtv_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $deactivate = null;

    #[ORM\OneToOne(inversedBy: 'userDeactivate', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'fk_user_id', referencedColumnName: 'user_id', onDelete: 'CASCADE', nullable: false)]
    private ?User $users = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDeactivate(): ?bool
    {
        return $this->deactivate;
    }

    public function setDeactivate(bool $deactivate): static
    {
        $this->deactivate = $deactivate;

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
