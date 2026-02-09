<?php

// src/Entity/Review/ReviewImage2.php

namespace App\Entity\Review;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Review\ReviewImage2Repository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewImage2Repository::class)]
#[ORM\HasLifecycleCallbacks]
class ReviewImage2
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'rvw_img2_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $image = null;

    #[ORM\OneToOne(
        targetEntity: Review::class,
        inversedBy: 'reviewImage2'
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(Review $review): static
    {
        $this->review = $review;

        return $this;
    }
}
