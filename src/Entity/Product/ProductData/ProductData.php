<?php

// src/Entity/Product/ProductData/ProductData.php

namespace App\Entity\Product\ProductData;

use App\Entity\Billing\OrderItem;
use App\Entity\NoMap\Time\Timestamps;
use App\Entity\Product\Product\Product;
use App\Entity\Product\ProductColor\ProductColor;
use App\Entity\Supplier\SupplierData;
use App\Entity\User\Session\PageView;
use App\Repository\Product\ProductData\ProductDataRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\SequenceGenerator;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Valid;

#[ORM\Entity(repositoryClass: ProductDataRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'product_data')]
class ProductData implements \Stringable
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[GeneratedValue(strategy: 'SEQUENCE')]
    #[SequenceGenerator(
        sequenceName: 'product_data_pro_pvt_id_seq',
        initialValue: 1,
        allocationSize: 1
    )]
    #[ORM\Column(name: 'pro_pvt_id', type: Types::BIGINT)]
    private string $id;

    #[ORM\ManyToOne(
        targetEntity: Product::class,
        inversedBy: 'productData'
    )]
    #[ORM\JoinColumn(
        name: 'fk_pro_id',
        referencedColumnName: 'pro_id',
        onDelete: 'CASCADE',
        nullable: false
    )]
    #[Type(type: Product::class)]
    #[Valid]
    private Product $product;

    #[ORM\Column(type: Types::STRING, length: 30, nullable: false)]
    private string $sku;

    #[ORM\Column(type: Types::FLOAT, nullable: false)]
    #[Range(
        min: 50,
        max: 25000,
        notInRangeMessage: 'Cost price should be between Rs {{ min }}and Rs{{ max }}.'
    )]
    private float $costPrice;

    #[ORM\Column(type: Types::FLOAT, nullable: false)]
    #[Range(
        min: 500,
        max: 25000,
        notInRangeMessage: 'Selling price should be between Rs {{ min }}and Rs{{ max }}.'
    )]
    private float $sellingPrice;

    #[ORM\Column(type: Types::SMALLINT, nullable: false)]
    #[PositiveOrZero]
    #[GreaterThanOrEqual(value: 1)]
    private int $qtyInStock;

    #[ORM\ManyToOne(
        targetEntity: SupplierData::class,
        inversedBy: 'product',
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(
        name: 'fk_pvt_sp_id',
        referencedColumnName: 'pivot_sp_id',
        onDelete: 'CASCADE',
        nullable: true
    )]
    #[Type(type: SupplierData::class)]
    #[Valid]
    private ?SupplierData $supplier = null;

    #[ORM\Column(type: Types::STRING, length: 5, nullable: false)]
    private string $size;

    #[ORM\OneToOne(
        targetEntity: ProductDataOrder::class,
        mappedBy: 'productData',
        orphanRemoval: true,
        cascade: ['persist', 'remove']
    )]
    private ?ProductDataOrder $productDataOrder = null;

    #[ORM\ManyToOne(
        targetEntity: ProductColor::class,
        inversedBy: 'productData'
    )]
    #[ORM\JoinColumn(
        name: 'fk_clr_pvt_id',
        referencedColumnName: 'clr_pvt_id',
        nullable: false
    )]
    private ProductColor $color;

    #[ORM\OneToOne(
        targetEntity: SimilarProductData::class,
        mappedBy: 'productData',
        orphanRemoval: true,
        cascade: ['persist', 'remove']
    )]
    private ?SimilarProductData $similarProductData = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(
        targetEntity: OrderItem::class,
        mappedBy: 'product',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $items;

    // /**
    //  * @var Collection<int, PageView>
    //  */
    // #[OneToMany(
    //     targetEntity: PageView::class,
    //     mappedBy: 'product',
    //     orphanRemoval: true,
    //     cascade: ['persist', 'remove'],
    //     fetch: 'EXTRA_LAZY')]
    // private Collection $pageViews;

    // /**
    //  * @var Collection<int, PageView>
    //  */
    // #[ORM\OneToMany(targetEntity: PageView::class, mappedBy: 'refererProduct')]
    // private Collection $pageViews;

    public function __construct()
    {
        // $this->pageViews = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    public function __toString(): string
    {
        try {
            return (string) $this->product;
        } catch (Exception) {
            return '';
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getCostPrice(): ?float
    {
        return $this->costPrice;
    }

    public function setCostPrice(?float $costPrice): self
    {
        $this->costPrice = $costPrice;

        return $this;
    }

    public function getSellingPrice(): ?float
    {
        return $this->sellingPrice;
    }

    public function setSellingPrice(?float $sellingPrice): self
    {
        $this->sellingPrice = $sellingPrice;

        return $this;
    }

    public function getQtyInStock(): ?int
    {
        return $this->qtyInStock;
    }

    public function setQtyInStock(?int $qtyInStock): self
    {
        $this->qtyInStock = $qtyInStock;

        return $this;
    }

    public function getSupplier(): ?SupplierData
    {
        return $this->supplier;
    }

    public function setSupplier(?SupplierData $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getProductDataOrder(): ?ProductDataOrder
    {
        return $this->productDataOrder;
    }

    public function setProductDataOrder(ProductDataOrder $productDataOrder): self
    {
        // set the owning side of the relation if necessary
        if ($productDataOrder->getProductData() !== $this) {
            $productDataOrder->setProductData($this);
        }

        $this->productDataOrder = $productDataOrder;

        return $this;
    }

    public function getColor(): ?ProductColor
    {
        return $this->color;
    }

    public function setColor(?ProductColor $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getSimilarProductData(): ?SimilarProductData
    {
        return $this->similarProductData;
    }

    public function setSimilarProductData(SimilarProductData $similarProductData): self
    {
        // set the owning side of the relation if necessary
        if ($similarProductData->getProductData() !== $this) {
            $similarProductData->setProductData($this);
        }

        $this->similarProductData = $similarProductData;

        return $this;
    }

    /**
     * @return Collection|OrderItem[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    // /**
    //  * @return Collection<int, PageView>
    //  */
    // public function getPageViews(): Collection
    // {
    //     return $this->pageViews;
    // }
    //
    // public function addPageView(PageView $pageView): self
    // {
    //     if (!$this->pageViews->contains($pageView)) {
    //         $this->pageViews[] = $pageView;
    //         $pageView->setProduct($this);
    //     }
    //
    //     return $this;
    // }
    //
    // public function removePageView(PageView $pageView): self
    // {
    //     if ($this->pageViews->removeElement($pageView)) {
    //         // set the owning side to null (unless already changed)
    //         if ($pageView->getProduct() === $this) {
    //             $pageView->setProduct(null);
    //         }
    //     }
    //
    //     return $this;
    // }

    // /**
    //  * @return Collection<int, PageView>
    //  */
    // public function getPageViews(): Collection
    // {
    //     return $this->pageViews;
    // }
    //
    // public function addPageView(PageView $pageView): static
    // {
    //     if (!$this->pageViews->contains($pageView)) {
    //         $this->pageViews->add($pageView);
    //         $pageView->setRefererProduct($this);
    //     }
    //
    //     return $this;
    // }
    //
    // public function removePageView(PageView $pageView): static
    // {
    //     if ($this->pageViews->removeElement($pageView)) {
    //         // set the owning side to null (unless already changed)
    //         if ($pageView->getRefererProduct() === $this) {
    //             $pageView->setRefererProduct(null);
    //         }
    //     }
    //
    //     return $this;
    // }
}
