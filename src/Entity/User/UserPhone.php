<?php

// src/Entity/User/UserPhone.php

namespace App\Entity\User;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\User\UserPhoneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;

#[ORM\Entity(repositoryClass: UserPhoneRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'user_phones')]
class UserPhone
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'upho_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'userPhone', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'fk_user_id', referencedColumnName: 'user_id', onDelete: 'CASCADE', nullable: false)]
    private ?User $users = null;

    #[AssertPhoneNumber(type: [AssertPhoneNumber::MOBILE])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    private $mobile;

    #[AssertPhoneNumber(type: [AssertPhoneNumber::FIXED_LINE])]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    private $landline;

    #[AssertPhoneNumber()]
    #[ORM\Column(type: 'phone_number', nullable: true)]
    private $fax;

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

    public function getMobile()
    {
        return $this->mobile;
    }

    public function setMobile($mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getLandline()
    {
        return $this->landline;
    }

    public function setLandline($landline): self
    {
        $this->landline = $landline;

        return $this;
    }

    public function getFax()
    {
        return $this->fax;
    }

    public function setFax($fax): self
    {
        $this->fax = $fax;

        return $this;
    }
}
