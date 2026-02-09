<?php

// src/Entity/Product/Product/ProductPricing.php

namespace App\Entity\Product\Product;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Product\Product\ProductPricingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductPricingRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProductPricing
{
    // Time: $created and $updated
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'product_pricing_pro_prc_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'pro_prc_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\OneToOne(
        targetEntity: Product::class,
        inversedBy: 'pricing',
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

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDowngradePrice = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $refundable = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $exchangeable = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getDateDowngradePrice(): ?\DateTimeInterface
    {
        return $this->dateDowngradePrice;
    }

    public function setDateDowngradePrice(?\DateTimeInterface $dateDowngradePrice): self
    {
        $this->dateDowngradePrice = $dateDowngradePrice;

        return $this;
    }

    public function isRefundable(): ?bool
    {
        return $this->refundable;
    }

    public function setRefundable(?bool $refundable): self
    {
        $this->refundable = $refundable;

        return $this;
    }

    public function isExchangeable(): ?bool
    {
        return $this->exchangeable;
    }

    public function setExchangeable(?bool $exchangeable): self
    {
        $this->exchangeable = $exchangeable;

        return $this;
    }
}
