<?php

// src/Entity/Review/ReviewRecommend.php

namespace App\Entity\Review;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Review\ReviewRecommendRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRecommendRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'review_recommends')]
class ReviewRecommend
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'rvw_rec_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $recommend = null;

    #[ORM\OneToOne(
        targetEntity: Review::class,
        inversedBy: 'reviewRecommend',
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

    public function getRecommend(): ?bool
    {
        return $this->recommend;
    }

    public function setRecommend(?bool $recommend): self
    {
        $this->recommend = $recommend;

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
