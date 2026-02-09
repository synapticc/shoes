<?php

// src/Entity/Review/ReviewDelivery.php

namespace App\Entity\Review;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Review\ReviewDeliveryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewDeliveryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ReviewDelivery
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'rvw_dlv_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?bool $delivery = null;

    #[ORM\OneToOne(
        targetEntity: Review::class,
        inversedBy: 'reviewDelivery'
    )]
    #[ORM\JoinColumn(
        name: 'fk_rvw_id',
        referencedColumnName: 'rvw_id',
        nullable: false
    )]
    private ?Review $review = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDelivery(): ?bool
    {
        return $this->delivery;
    }

    public function setDelivery(?bool $delivery): static
    {
        $this->delivery = $delivery;

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
