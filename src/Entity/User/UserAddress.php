<?php

// src/Entity/User/UserAddress.php

namespace App\Entity\User;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\User\UserAddressRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ZipCodeValidator\Constraints\ZipCode;

#[ORM\Entity(repositoryClass: UserAddressRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'user_address')]
class UserAddress
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'uadd_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'userAddress', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'fk_user_id', referencedColumnName: 'user_id', onDelete: 'CASCADE', nullable: false)]
    private ?User $users = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $street2 = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $city = null;

    /**
     * @ZipCode(getter="getCountry")
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(1)]
    private ?int $zip = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $country = null;

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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getStreet2(): ?string
    {
        return $this->street2;
    }

    public function setStreet2(?string $street2): self
    {
        $this->street2 = $street2;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZip(): ?int
    {
        return $this->zip;
    }

    public function setZip(?int $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }
}
