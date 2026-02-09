<?php

// src/Entity/Review/ReviewData.php

namespace App\Entity\Review;

use App\Entity\Billing\OrderItem;
use App\Entity\Product\ProductColor\ProductColor;
use App\Entity\Product\ProductData\ProductData;
use App\Repository\Review\ReviewDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewDataRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ReviewData
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'rvw_pvt_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(
        targetEntity: Review::class,
        inversedBy: 'reviewData'
    )]
    #[ORM\JoinColumn(
        name: 'fk_rvw_id',
        referencedColumnName: 'rvw_id',
        nullable: false
    )]
    #[Assert\Type(type: Review::class)]
    #[Assert\Valid]
    private ?Review $review = null;

    #[ORM\ManyToOne(
        targetEntity: ProductData::class
    )]
    #[ORM\JoinColumn(
        name: 'fk_pro_id',
        referencedColumnName: 'pro_pvt_id',
        nullable: false
    )]
    #[Assert\Type(type: ProductData::class)]
    #[Assert\Valid]
    private ?ProductData $product = null;

    #[ORM\ManyToOne(
        targetEntity: OrderItem::class
    )]
    #[ORM\JoinColumn(
        name: 'fk_ord_id',
        referencedColumnName: 'ord_itm_id',
        nullable: false
    )]
    #[Assert\Type(type: OrderItem::class)]
    #[Assert\Valid]
    private ?OrderItem $items = null;

    #[ORM\ManyToOne(
        targetEntity: ProductColor::class
    )]
    #[ORM\JoinColumn(
        name: 'fk_clr_id',
        referencedColumnName: 'clr_pvt_id',
        nullable: false
    )]
    #[Assert\Type(type: ProductColor::class)]
    #[Assert\Valid]
    private ?ProductColor $color = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProduct(): ?ProductData
    {
        return $this->product;
    }

    public function setProduct(ProductData $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getItems(): ?OrderItem
    {
        return $this->items;
    }

    public function setItems(OrderItem $items): static
    {
        $this->items = $items;

        return $this;
    }

    public function getColor(): ?ProductColor
    {
        return $this->color;
    }

    public function setColor(ProductColor $color): static
    {
        $this->color = $color;

        return $this;
    }
}
