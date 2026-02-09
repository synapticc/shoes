<?php

// src/Entity/User/UserImage.php

namespace App\Entity\User;

use App\Repository\User\UserImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserImageRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: '`user_image`')]
class UserImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'uimage_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 150, nullable: true)]
    private ?string $image = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'userImage', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'fk_user_id', referencedColumnName: 'user_id', onDelete: 'CASCADE', nullable: false)]
    private ?User $users = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
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
}
