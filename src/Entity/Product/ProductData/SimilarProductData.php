<?php

// src/Entity/Product/ProductData/SimilarProductData.php

namespace App\Entity\Product\ProductData;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Product\ProductData\SimilarProductDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SimilarProductDataRepository::class)]
#[ORM\HasLifecycleCallbacks]
class SimilarProductData
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'similar_product_data_pro_pvt_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'pro_pvt_sm_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\OneToOne(targetEntity: ProductData::class, inversedBy: 'similarProductData')] // ,
    #[ORM\JoinColumn(name: 'fk_pro_pvt_id', referencedColumnName: 'pro_pvt_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: ProductData::class)]
    #[Assert\Valid]
    private ?ProductData $productData = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $qtySlider = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $sameSize = null;

    #[ORM\Column(nullable: true)]
    private ?array $sizes = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $qtySize = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $samePrice = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $minPrice = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $maxPrice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductData(): ?ProductData
    {
        return $this->productData;
    }

    public function setProductData(ProductData $productData): self
    {
        $this->productData = $productData;

        return $this;
    }

    public function getSizes(): array
    {
        return $this->sizes;
    }

    public function setSizes(array $sizes): static
    {
        $this->sizes = $sizes;

        return $this;
    }

    public function getMinPrice(): ?int
    {
        return $this->minPrice;
    }

    public function setMinPrice(?int $minPrice): self
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    public function getMaxPrice(): ?int
    {
        return $this->maxPrice;
    }

    public function setMaxPrice(?int $maxPrice): self
    {
        $this->maxPrice = $maxPrice;

        return $this;
    }

    public function isSameSize(): ?bool
    {
        return $this->sameSize;
    }

    public function setSameSize(bool $sameSize): self
    {
        $this->sameSize = $sameSize;

        return $this;
    }

    public function isSamePrice(): ?bool
    {
        return $this->samePrice;
    }

    public function setSamePrice(bool $samePrice): self
    {
        $this->samePrice = $samePrice;

        return $this;
    }

    public function getQtySlider(): ?int
    {
        return $this->qtySlider;
    }

    public function setQtySlider(int $qtySlider): self
    {
        $this->qtySlider = $qtySlider;

        return $this;
    }

    public function getQtySize(): ?int
    {
        return $this->qtySize;
    }

    public function setQtySize(int $qtySize): self
    {
        $this->qtySize = $qtySize;

        return $this;
    }
}
