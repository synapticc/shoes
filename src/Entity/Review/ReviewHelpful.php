<?php

// src/Entity/Review/ReviewHelpful.php

namespace App\Entity\Review;

use App\Entity\NoMap\Time\Timestamps;
use App\Entity\User\User;
use App\Repository\Review\ReviewHelpfulRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewHelpfulRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'review_helpful')]
class ReviewHelpful
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'rvw_hlp_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $isHelpful = null;

    #[ORM\ManyToOne(
        targetEntity: Review::class,
        inversedBy: 'reviewHelpfuls'
    )]
    #[ORM\JoinColumn(
        name: 'fk_rvw_id',
        referencedColumnName: 'rvw_id'
    )]
    #[Assert\Type(type: Review::class)]
    #[Assert\Valid]
    private ?Review $review = null;

    #[ORM\ManyToOne(
        targetEntity: User::class,
        inversedBy: 'reviewHelpfuls'
    )]
    #[ORM\JoinColumn(
        name: 'fk_user_id',
        referencedColumnName: 'user_id'
    )]
    #[Assert\Type(type: User::class)]
    #[Assert\Valid]
    private ?User $users = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsHelpful(): ?bool
    {
        return $this->isHelpful;
    }

    public function setIsHelpful(?bool $isHelpful): self
    {
        $this->isHelpful = $isHelpful;

        return $this;
    }

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(?Review $review): static
    {
        $this->review = $review;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): static
    {
        $this->users = $users;

        return $this;
    }
}
