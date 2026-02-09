<?php

// src/Entity/User/Reset/ResetPasswordRequest.php

namespace App\Entity\User\Reset;

use App\Entity\User\User;
use App\Repository\User\Reset\ResetPasswordRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
#[ORM\Table(name: '`request_password`')]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'reset_id', type: Types::INTEGER)]
    private ?int $id = null;

    public function __construct(#[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(name: 'fk_user_id', referencedColumnName: 'user_id', nullable: false)]
        #[Assert\Type(type: User::class)]
        private object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): object
    {
        return $this->user;
    }
}
