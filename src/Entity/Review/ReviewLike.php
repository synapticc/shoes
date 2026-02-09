<?php

// src/Entity/Review/ReviewLike.php

namespace App\Entity\Review;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Review\ReviewLikeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewLikeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'review_likes')]
class ReviewLike
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'rvw_lk_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'vote_like', type: Types::BOOLEAN, nullable: true)]
    private ?bool $like = null;

    #[ORM\OneToOne(
        targetEntity: Review::class,
        inversedBy: 'reviewLike',
    )]
    #[ORM\JoinColumn(
        name: 'fk_rvw_id',
        referencedColumnName: 'rvw_id',
        onDelete: 'CASCADE',
        nullable: false
    )]
    private ?Review $review = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLike(): ?bool
    {
        return $this->like;
    }

    public function setLike(?bool $like): self
    {
        $this->like = $like;

        return $this;
    }

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(?Review $review): self
    {
        $this->review = $review;

        return $this;
    }
}
