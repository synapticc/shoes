<?php

// src/Entity/Review/Review.php

namespace App\Entity\Review;

use App\Entity\NoMap\Time\Timestamps;
use App\Entity\Product\Product\Product;
use App\Entity\User\User;
use App\Repository\Review\ReviewRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'product_reviews')]
#[ORM\Index(
    name: 'IDX_product_reviews_search',
    columns: ['search_vector'],
    // // options: ["where" => "subscriber_notification = TRUE"]
)]
#[ORM\Index(
    name: 'IDX_product_reviews_active',
    columns: ['active'],
    // // options: ["where" => "subscriber_notification = TRUE"]
)]
class Review
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'rvw_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(
        targetEntity: User::class,
        inversedBy: 'productReviews'
    )]
    #[ORM\JoinColumn(
        name: 'fk_user_id',
        referencedColumnName: 'user_id'
    )]
    #[Assert\Type(type: User::class)]
    #[Assert\Valid]
    private ?User $users = null;

    #[ORM\ManyToOne(
        targetEntity: Product::class,
        inversedBy: 'productReviews'
    )]
    #[ORM\JoinColumn(
        name: 'fk_pro_id',
        referencedColumnName: 'pro_id'
    )]
    #[Assert\Type(type: Product::class)]
    #[Assert\Valid]
    private ?Product $product = null;

    #[ORM\Column(length: 60)]
    private ?string $headline = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $fit = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $width = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $comfort = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $rating = null;

    #[ORM\OneToOne(
        targetEntity: ReviewLike::class,
        mappedBy: 'review',
        orphanRemoval: true,
        fetch: 'EXTRA_LAZY',
        cascade: ['persist']
    )]
    private ?ReviewLike $reviewLike = null;

    #[ORM\OneToOne(
        targetEntity: ReviewRecommend::class,
        mappedBy: 'review',
        orphanRemoval: true,
        fetch: 'EXTRA_LAZY',
        cascade: ['persist']
    )]
    private ?ReviewRecommend $reviewRecommend = null;

    #[ORM\OneToOne(
        targetEntity: ReviewImage::class,
        mappedBy: 'review',
        orphanRemoval: true,
        fetch: 'EXTRA_LAZY',
        cascade: ['persist', 'remove']
    )]
    private ?ReviewImage $reviewImage = null;

    #[ORM\Column]
    private ?bool $active = null;

    /*
    Brave Search Answer: Persist tsvector column
    This configuration ensures that Doctrine does not attempt to insert or update the tsvector column directly, allowing the database to handle its generation automatically based on the specified expression. The columnDefinition attribute specifies the GENERATED ALWAYS AS expression, which computes the tsvector value from the title and summary fields using the to_tsvector function with the 'english' configuration.

    The columnDefinition must explicitly include the GENERATED ALWAYS AS clause to ensure the database creates the column correctly.

    Additionally, when using Doctrine migrations, the migration tool may incorrectly detect changes to the generated column and attempt to alter it repeatedly.
    To prevent this, you can use the @ORM\Ignore() annotation on the column to instruct Doctrine to ignore it during schema comparison.

    Finally, the entity should not have a setter method for the generated tsvector field, as the value is automatically populated by the database upon entity flush, and attempting to set it manually would be redundant and potentially problematic.
    The data for the tsvector field is automatically populated when the entity is flushed to the database, based on the fields specified in the columnDefinition expression.
    */
    #[ORM\Column(
        type: 'tsvector',
        nullable: true,
        columnDefinition: "GENERATED ALWAYS AS (
            setweight(to_tsvector('simple_english', coalesce(headline, '')), 'A') ||
            setweight(to_tsvector('simple_english', coalesce(comment, '')), 'B'))
         STORED",
        insertable: false,
        updatable: false,
        generated: 'ALWAYS'
    )]
    private mixed $search_vector = null;

    #[ORM\OneToOne(
        targetEntity: ReviewDelivery::class,
        mappedBy: 'review',
        orphanRemoval: true,
        fetch: 'EXTRA_LAZY',
        cascade: ['persist']
    )]
    private ?ReviewDelivery $reviewDelivery = null;

    /**
     * @var Collection<int, ReviewHelpful>
     */
    #[ORM\OneToMany(
        targetEntity: ReviewHelpful::class,
        mappedBy: 'review',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $reviewHelpfuls;

    /**
     * @var Collection<int, ReviewData>
     */
    #[ORM\OneToMany(
        targetEntity: ReviewData::class,
        mappedBy: 'review',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $reviewData;

    #[ORM\OneToOne(mappedBy: 'review', cascade: ['persist', 'remove'])]
    private ?ReviewImage2 $reviewImage2 = null;

    #[ORM\OneToOne(mappedBy: 'review', cascade: ['persist', 'remove'])]
    private ?ReviewImage3 $reviewImage3 = null;

    #[ORM\OneToOne(mappedBy: 'review', cascade: ['persist', 'remove'])]
    private ?ReviewImage4 $reviewImage4 = null;

    public function __construct()
    {
        $this->reviewHelpfuls = new ArrayCollection();
        $this->reviewData = new ArrayCollection();
    }

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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getHeadline(): ?string
    {
        return $this->headline;
    }

    public function setHeadline(string $headline): static
    {
        $this->headline = $headline;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getFit(): ?int
    {
        return $this->fit;
    }

    public function setFit(int $fit): self
    {
        $this->fit = $fit;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getComfort(): ?int
    {
        return $this->comfort;
    }

    public function setComfort(int $comfort): self
    {
        $this->comfort = $comfort;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getReviewLike(): ?ReviewLike
    {
        return $this->reviewLike;
    }

    public function setReviewLike(?ReviewLike $reviewLike): self
    {
        // unset the owning side of the relation if necessary
        if (null === $reviewLike && null !== $this->reviewLike) {
            $this->reviewLike->setReview(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $reviewLike && $reviewLike->getReview() !== $this) {
            $reviewLike->setReview($this);
        }

        $this->reviewLike = $reviewLike;

        return $this;
    }

    public function getReviewRecommend(): ?ReviewRecommend
    {
        return $this->reviewRecommend;
    }

    public function setReviewRecommend(?ReviewRecommend $reviewRecommend): self
    {
        // unset the owning side of the relation if necessary
        if (null === $reviewRecommend && null !== $this->reviewRecommend) {
            $this->reviewRecommend->setReview(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $reviewRecommend && $reviewRecommend->getReview() !== $this) {
            $reviewRecommend->setReview($this);
        }

        $this->reviewRecommend = $reviewRecommend;

        return $this;
    }

    public function getReviewImage(): ?ReviewImage
    {
        return $this->reviewImage;
    }

    public function setReviewImage(ReviewImage $reviewImage): self
    {
        // set the owning side of the relation if necessary
        if ($reviewImage->getReview() !== $this) {
            $reviewImage->setReview($this);
        }

        $this->reviewImage = $reviewImage;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getSearchVector(): mixed
    {
        return $this->search_vector;
    }

    // Setter cannot be used for tsvector column.
    // public function setSearchVector(mixed $search_vector): static
    // {
    //     $this->search_vector = $search_vector;
    //
    //     return $this;
    // }

    public function getReviewDelivery(): ?ReviewDelivery
    {
        return $this->reviewDelivery;
    }

    public function setReviewDelivery(ReviewDelivery $reviewDelivery): static
    {
        // set the owning side of the relation if necessary
        if ($reviewDelivery->getReview() !== $this) {
            $reviewDelivery->setReview($this);
        }

        $this->reviewDelivery = $reviewDelivery;

        return $this;
    }

    /**
     * @return Collection<int, ReviewHelpful>
     */
    public function getReviewHelpfuls(): Collection
    {
        return $this->reviewHelpfuls;
    }

    public function addReviewHelpful(ReviewHelpful $reviewHelpful): static
    {
        if (!$this->reviewHelpfuls->contains($reviewHelpful)) {
            $this->reviewHelpfuls->add($reviewHelpful);
            $reviewHelpful->setReview($this);
        }

        return $this;
    }

    public function removeReviewHelpful(ReviewHelpful $reviewHelpful): static
    {
        if ($this->reviewHelpfuls->removeElement($reviewHelpful)) {
            // set the owning side to null (unless already changed)
            if ($reviewHelpful->getReview() === $this) {
                $reviewHelpful->setReview(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReviewData>
     */
    public function getReviewData(): Collection
    {
        return $this->reviewData;
    }

    public function addReviewData(ReviewData $reviewData): static
    {
        if (!$this->reviewData->contains($reviewData)) {
            // $this->reviewData->add($reviewData);
            $this->reviewData[] = $reviewData;
            $reviewData->setReview($this);
        }

        return $this;
    }

    public function removeReviewData(ReviewData $reviewData): static
    {
        if ($this->reviewData->removeElement($reviewData)) {
            // set the owning side to null (unless already changed)
            if ($reviewData->getReview() === $this) {
                $reviewData->setReview(null);
            }
        }

        return $this;
    }

    public function getReviewImage2(): ?ReviewImage2
    {
        return $this->reviewImage2;
    }

    public function setReviewImage2(ReviewImage2 $reviewImage2): static
    {
        // set the owning side of the relation if necessary
        if ($reviewImage2->getReview() !== $this) {
            $reviewImage2->setReview($this);
        }

        $this->reviewImage2 = $reviewImage2;

        return $this;
    }

    public function getReviewImage3(): ?ReviewImage3
    {
        return $this->reviewImage3;
    }

    public function setReviewImage3(ReviewImage3 $reviewImage3): static
    {
        // set the owning side of the relation if necessary
        if ($reviewImage3->getReview() !== $this) {
            $reviewImage3->setReview($this);
        }

        $this->reviewImage3 = $reviewImage3;

        return $this;
    }

    public function getReviewImage4(): ?ReviewImage4
    {
        return $this->reviewImage4;
    }

    public function setReviewImage4(ReviewImage4 $reviewImage4): static
    {
        // set the owning side of the relation if necessary
        if ($reviewImage4->getReview() !== $this) {
            $reviewImage4->setReview($this);
        }

        $this->reviewImage4 = $reviewImage4;

        return $this;
    }
}
