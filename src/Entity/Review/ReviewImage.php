<?php

// src/Entity/Review/ReviewImage.php

namespace App\Entity\Review;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Review\ReviewImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewImageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ReviewImage
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'rvw_img_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 150, nullable: true)]
    private ?string $image = null;

    /*
      Doctrine JoinColumn Cascade

      The `#[ORM\JoinColumn]` annotation with `onDelete: 'CASCADE'` specifies
      that when the referenced entity (in this case: Review, the entity associated
      with the `rvw_id` column) is deleted, all entities that reference it
      via the `fk_rvw_id` foreign key will also be automatically deleted at
      the database level  This behavior is enforced by the database server
      itself, not by Doctrine's ORM layer, meaning the foreign key constraint
      in the database is configured with `ON DELETE CASCADE`.

      The nullable: false constraint ensures that the foreign
      key column cannot contain null values, meaning every record in the
      owning table must reference a valid record in the target table.
     */
    #[ORM\OneToOne(
        targetEntity: Review::class,
        inversedBy: 'reviewImage'
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

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(Review $review): self
    {
        $this->review = $review;

        return $this;
    }
}
