<?php

// src/Entity/Product/Product/ProductDiscount.php

namespace App\Entity\Product\Product;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Product\Product\ProductDiscountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductDiscountRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProductDiscount
{
    // Time: $created and $updated
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'product_discount_pro_dsct_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'pro_dsct_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $discount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\OneToOne(
        targetEntity: Product::class,
        inversedBy: 'discount',
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(
        name: 'fk_pro_id',
        referencedColumnName: 'pro_id',
        onDelete: 'CASCADE',
        nullable: false
    )]
    #[Assert\Type(type: Product::class)]
    #[Assert\Valid]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    public function setDiscount(int $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
