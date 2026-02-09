<?php

// src/Entity/Product/Product/Product.php

namespace App\Entity\Product\Product;

use App\Entity\NoMap\Time\Timestamps;
use App\Entity\Product\ProductColor\ProductColor;
use App\Entity\Product\ProductData\ProductData;
use App\Entity\Review\Review;
use App\Repository\Product\Product\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(
    name: 'products_search_idx',
    columns: ['search_vector'],
    // options: ["where" => "subscriber_notification = TRUE"]
)]
#[ORM\Index(name: 'IDX_products_name_gin', columns: ['name'], )]
#[ORM\Table(name: 'products')]
class Product implements \Stringable
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'products_pro_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'pro_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: false)]
    private string $description;

    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $features = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 100, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'brand', type: Types::STRING, length: 25, nullable: false)]
    private string $brand;

    #[ORM\Column(name: 'category', type: Types::STRING, length: 6, nullable: false)]
    private string $category;

    #[ORM\Column(type: Types::JSONB)]
    private $occasion = [];

    #[ORM\Column(name: 'type', type: Types::STRING, length: 30, nullable: false)]
    private string $type;

    #[ORM\Column(name: 'is_displayed', type: Types::BOOLEAN, nullable: false)]
    private bool $displayed;

    /**
     * @var Collection<int, ProductData>
     */
    #[ORM\OneToMany(targetEntity: ProductData::class, mappedBy: 'product', orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    private Collection $productData;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(
        targetEntity: Review::class,
        mappedBy: 'product',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $productReviews;

    /**
     * @var Collection<int, ProductColor>
     */
    #[ORM\OneToMany(
        targetEntity: ProductColor::class,
        mappedBy: 'product',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY'
    )]
    #[ORM\OrderBy(['color' => Criteria::ASC])]
    private Collection $productColor;

    #[ORM\OneToOne(targetEntity: ProductVideo::class, mappedBy: 'product', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private ?ProductVideo $video = null;

    #[ORM\OneToOne(targetEntity: ProductDiscontinued::class, mappedBy: 'product', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private ?ProductDiscontinued $discontinued = null;

    #[ORM\OneToOne(targetEntity: ProductPricing::class, mappedBy: 'product', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private ?ProductPricing $pricing = null;

    #[ORM\OneToOne(targetEntity: ProductQtyPack::class, mappedBy: 'product', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private ?ProductQtyPack $qtyPack = null;

    #[ORM\OneToOne(targetEntity: SimilarProduct::class, mappedBy: 'product', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private ?SimilarProduct $similarProduct = null;

    #[ORM\OneToOne(targetEntity: ProductDiscount::class, mappedBy: 'product', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private ?ProductDiscount $discount = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTime $displayDate = null;

    #[ORM\Column(
        type: 'tsvector',
        nullable: true,
        columnDefinition: "GENERATED ALWAYS AS (
            setweight(to_tsvector('english', coalesce(name, '')), 'A') ||
            setweight(to_tsvector('english', coalesce(description, '')), 'B') ||
            setweight(to_tsvector('english', coalesce(features, '')), 'C'))
         STORED",
        insertable: false,
        updatable: false,
        generated: 'ALWAYS'
    )]
    private mixed $search_vector = null;

    public function __construct()
    {
        $this->productData = new ArrayCollection();
        $this->productReviews = new ArrayCollection();
        $this->suppliers = new ArrayCollection();
        $this->supplierData = new ArrayCollection();
        $this->productColor = new ArrayCollection();
        $this->otherProducts = new ArrayCollection();
    }

    public function __toString(): string
    {
        try {
            return (string) $this->id.': '.$this->name;
        } catch (Exception) {
            return '';
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return Collection|ProductData[]
     */
    public function getProductData(): Collection
    {
        return $this->productData;
    }

    public function addProductData(ProductData $productData): self
    {
        if (!$this->productData->contains($productData)) {
            $this->productData[] = $productData;
            $productData->setProduct($this);
        }

        return $this;
    }

    public function removeProductData(ProductData $productData): self
    {
        if ($this->productData->removeElement($productData)) {
            // set the owning side to null (unless already changed)
            if ($productData->getProduct() === $this) {
                $productData->setProduct(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFeatures(): ?array
    {
        return $this->features;
    }

    public function setFeatures(?array $features): self
    {
        $this->features = $features;

        return $this;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->productReviews;
    }

    public function addReview(Review $productReview): self
    {
        if (!$this->productReviews->contains($productReview)) {
            $this->productReviews[] = $productReview;
            $productReview->setProduct($this);
        }

        return $this;
    }

    public function removeReview(Review $productReview): self
    {
        if ($this->productReviews->removeElement($productReview)) {
            // set the owning side to null (unless already changed)
            if ($productReview->getProduct() === $this) {
                $productReview->setProduct(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDisplayed(): ?bool
    {
        return $this->displayed;
    }

    public function setDisplayed(?bool $displayed): self
    {
        $this->displayed = $displayed;

        return $this;
    }

    /**
     * @return Collection<int, ProductColor>
     */
    public function getProductColor(): Collection
    {
        return $this->productColor;
    }

    public function addProductColor(ProductColor $productColor): self
    {
        if (!$this->productColor->contains($productColor)) {
            $this->productColor[] = $productColor;
            $productColor->setProduct($this);
        }

        return $this;
    }

    public function removeProductColor(ProductColor $productColor): self
    {
        if ($this->productColor->removeElement($productColor)) {
            // set the owning side to null (unless already changed)
            if ($productColor->getProduct() === $this) {
                $productColor->setProduct(null);
            }
        }

        return $this;
    }

    public function getVideo(): ?ProductVideo
    {
        return $this->video;
    }

    public function setVideo(ProductVideo $video): self
    {
        // set the owning side of the relation if necessary
        if ($video->getProduct() !== $this) {
            $video->setProduct($this);
        }

        $this->video = $video;

        return $this;
    }

    public function getDiscontinued(): ?ProductDiscontinued
    {
        return $this->discontinued;
    }

    public function setDiscontinued(ProductDiscontinued $discontinued): self
    {
        // set the owning side of the relation if necessary
        if ($discontinued->getProduct() !== $this) {
            $discontinued->setProduct($this);
        }

        $this->discontinued = $discontinued;

        return $this;
    }

    public function getPricing(): ?ProductPricing
    {
        return $this->pricing;
    }

    public function setPricing(ProductPricing $pricing): self
    {
        // set the owning side of the relation if necessary
        if ($pricing->getProduct() !== $this) {
            $pricing->setProduct($this);
        }

        $this->pricing = $pricing;

        return $this;
    }

    public function getQtyPack(): ?ProductQtyPack
    {
        return $this->qtyPack;
    }

    public function setQtyPack(ProductQtyPack $qtyPack): self
    {
        // set the owning side of the relation if necessary
        if ($qtyPack->getProduct() !== $this) {
            $qtyPack->setProduct($this);
        }

        $this->qtyPack = $qtyPack;

        return $this;
    }

    public function getSimilarProduct(): ?SimilarProduct
    {
        return $this->similarProduct;
    }

    public function setSimilarProduct(SimilarProduct $similarProduct): self
    {
        // set the owning side of the relation if necessary
        if ($similarProduct->getProduct() !== $this) {
            $similarProduct->setProduct($this);
        }

        $this->similarProduct = $similarProduct;

        return $this;
    }

    /**
     * @return Collection<int, OtherProduct>
     */
    public function getOtherProducts(): Collection
    {
        return $this->otherProducts;
    }

    public function getDiscount(): ?ProductDiscount
    {
        return $this->discount;
    }

    public function setDiscount(ProductDiscount $discount): static
    {
        // set the owning side of the relation if necessary
        if ($discount->getProduct() !== $this) {
            $discount->setProduct($this);
        }

        $this->discount = $discount;

        return $this;
    }

    public function getDisplayDate(): ?\DateTime
    {
        return $this->displayDate;
    }

    public function setDisplayDate(\DateTime $displayDate): static
    {
        $this->displayDate = $displayDate;

        return $this;
    }

    public function getOccasion(): array
    {
        return $this->occasion;
    }

    public function setOccasion(array $occasion): static
    {
        $this->occasion = $occasion;

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
}
