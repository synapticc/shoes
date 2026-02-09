<?php

// src/Entity/Product/Product/SimilarProduct.php

namespace App\Entity\Product\Product;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Product\Product\SimilarProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SimilarProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class SimilarProduct
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(
        sequenceName: 'similar_products_pro_sm_id_seq',
        initialValue: 1,
        allocationSize: 1
    )]
    #[ORM\Column(name: 'pro_sm_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\OneToOne(
        targetEntity: Product::class,
        inversedBy: 'similarProduct'
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

    #[ORM\Column]
    private array $brands = [];

    #[ORM\Column]
    private array $occasions = [];

    #[ORM\Column]
    private array $types = [];

    #[ORM\Column]
    private array $fabrics = [];

    #[ORM\Column(nullable: true)]
    private ?array $textures = null;

    #[ORM\Column(nullable: true)]
    private ?array $sizes = null;

    #[ORM\Column]
    private array $colors = [];

    #[ORM\Column(nullable: true)]
    private ?array $sort = [];

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $minPrice = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $maxPrice = null;

    #[ORM\Column(nullable: true)]
    private ?array $tags = null;

    public function __construct()
    {
        $this->otherProducts = new ArrayCollection();
    }

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

    public function getSizes(): ?array
    {
        return $this->sizes;
    }

    public function setSizes(?array $sizes): self
    {
        $this->sizes = $sizes;

        return $this;
    }

    public function setOccasions(?array $occasions): self
    {
        $this->occasions = $occasions;

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

    public function getSort(): ?array
    {
        return $this->sort;
    }

    public function setSort(?array $sort): static
    {
        $this->sort = $sort;

        return $this;
    }

    public function brands(): ?array
    {
        return $this->brands;
    }

    public function setBrands(array $brands): self
    {
        $this->brands = $brands;

        return $this;
    }

    public function getOccasions(): ?array
    {
        return $this->occasions;
    }

    public function getTypes(): ?array
    {
        return $this->types;
    }

    public function setTypes(array $types): self
    {
        $this->types = $types;

        return $this;
    }

    public function getColors(): array
    {
        return $this->colors;
    }

    public function setColors(array $colors): self
    {
        $this->colors = $colors;

        return $this;
    }

    public function getFabrics(): ?array
    {
        return $this->fabrics;
    }

    public function setFabrics(array $fabrics): self
    {
        $this->fabrics = $fabrics;

        return $this;
    }

    public function getTextures(): ?array
    {
        return $this->textures;
    }

    public function setTextures(?array $textures): self
    {
        $this->textures = $textures;

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }
}
