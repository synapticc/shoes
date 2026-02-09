<?php

// src/Entity/Product/ProductColor/ProductColor.php

namespace App\Entity\Product\ProductColor;

use App\Controller\_Utils\Attributes;
use App\Entity\NoMap\Time\TimeUpdated;
use App\Entity\Product\Product\Product;
use App\Entity\Product\ProductColor\ProductColorTags as Tag;
use App\Entity\Product\ProductColor\ProductColorTexture as Texture;
use App\Entity\Product\ProductColor\ProductColorVideo as Video;
use App\Entity\Product\ProductData\ProductData;
use App\Entity\Product\ProductImage\ProductImage2;
use App\Entity\Product\ProductImage\ProductImage3;
use App\Entity\Product\ProductImage\ProductImage4;
use App\Entity\Product\ProductImage\ProductImage5;
use App\Repository\Product\ProductColor\ProductColorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductColorRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'product_color')]
class ProductColor implements \Stringable
{
    // Time: $updated
    use TimeUpdated;

    // use Attributes;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(name: 'clr_pvt_id', type: Types::BIGINT)]
    #[ORM\SequenceGenerator(sequenceName: 'product_color_clr_pvt_id_seq', initialValue: 1, allocationSize: 1)]
    private string $id;

    #[ORM\ManyToOne(
        targetEntity: Product::class,
        inversedBy: 'productColor'
    )]
    #[ORM\JoinColumn(
        name: 'fk_pro_id',
        referencedColumnName: 'pro_id',
        onDelete: 'CASCADE'
    )]
    #[Assert\Type(type: Product::class)]
    #[Assert\Valid]
    private ?Product $product = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private string $color;

    #[ORM\Column(type: Types::JSONB)]
    private $fabric = [];

    #[ORM\OneToOne(
        targetEntity: ProductImage2::class,
        mappedBy: 'product',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private ?ProductImage2 $productImage2 = null;

    #[ORM\OneToOne(targetEntity: ProductImage3::class, mappedBy: 'product', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?ProductImage3 $productImage3 = null;

    #[ORM\OneToOne(targetEntity: ProductImage4::class, mappedBy: 'product', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?ProductImage4 $productImage4 = null;

    #[ORM\OneToOne(targetEntity: ProductImage5::class, mappedBy: 'product', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?ProductImage5 $productImage5 = null;

    #[ORM\OneToOne(targetEntity: Video::class, mappedBy: 'productColor', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Video $video = null;

    /**
     * @var Collection<int, ProductData>
     */
    #[ORM\OneToMany(
        targetEntity: ProductData::class,
        mappedBy: 'color',
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $productData;

    #[ORM\OneToOne(
        targetEntity: SimilarProductColor::class,
        mappedBy: 'productColor',
        orphanRemoval: true,
        cascade: ['persist', 'remove']
    )]
    private ?SimilarProductColor $similarProductColor = null;

    #[ORM\OneToOne(
        targetEntity: Tag::class,
        mappedBy: 'productColor',
        orphanRemoval: true,
        cascade: ['persist', 'remove']
    )]
    private ?Tag $tag = null;

    #[ORM\OneToOne(
        mappedBy: 'productColor',
        cascade: ['persist', 'remove']
    )]
    private ?Texture $texture = null;

    #[ORM\Column(type: Types::STRING, length: 150)]
    private ?string $image = null;

    #[ORM\Column(type: Types::STRING, length: 150)]
    private ?string $imageOriginal = null;

    #[ORM\Column(name: 'image_md', type: Types::STRING, length: 150)]
    private ?string $imageMedium = null;

    #[ORM\Column(name: 'image_sm', type: Types::STRING, length: 150)]
    private ?string $imageSmall = null;

    #[ORM\Column(name: 'image_created', type: 'datetimetz_immutable')]
    private ?\DateTimeImmutable $imageCreated = null;

    #[ORM\Column(name: 'image_updated', type: 'datetimetz', nullable: true)]
    private ?\DateTimeInterface $imageUpdated = null;

    public function __toString(): string
    {
        return $this->displayColor();
    }

    public function displayColor(): string
    {
        // foreach (explode('-', (string) $this->getColor()) as $i => $val) {
        //     $color[] = $this->name($val);
        // }
        //
        // foreach ($this->getFabric() as $j => $value) {
        //     $fabrics[] = strtolower((string) $this->name($value));
        // }
        //
        // $colors = implode(' | ', $color);
        // $fabrics = implode(', ', $fabrics);
        // $string = (string) $colors.' ('.$fabrics.')';

        // return $string;
        return true;
    }

    public function __construct()
    {
        $this->productData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getFabric()
    {
        return $this->fabric;
    }

    public function setFabric($fabric): self
    {
        $this->fabric = $fabric;

        return $this;
    }

    public function getProductImage2(): ?ProductImage2
    {
        return $this->productImage2;
    }

    public function setProductImage2(?ProductImage2 $productImage2): self
    {
        // unset the owning side of the relation if necessary
        if (null === $productImage2 && null !== $this->productImage2) {
            $this->productImage2->setProduct(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $productImage2 && $productImage2->getProduct() !== $this) {
            $productImage2->setProduct($this);
        }

        $this->productImage2 = $productImage2;

        return $this;
    }

    public function getProductImage3(): ?ProductImage3
    {
        return $this->productImage3;
    }

    public function setProductImage3(?ProductImage3 $productImage3): self
    {
        // unset the owning side of the relation if necessary
        if (null === $productImage3 && null !== $this->productImage3) {
            $this->productImage3->setProduct(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $productImage3 && $productImage3->getProduct() !== $this) {
            $productImage3->setProduct($this);
        }

        $this->productImage3 = $productImage3;

        return $this;
    }

    public function getProductImage4(): ?ProductImage4
    {
        return $this->productImage4;
    }

    public function setProductImage4(?ProductImage4 $productImage4): self
    {
        // unset the owning side of the relation if necessary
        if (null === $productImage4 && null !== $this->productImage4) {
            $this->productImage4->setProduct(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $productImage4 && $productImage4->getProduct() !== $this) {
            $productImage4->setProduct($this);
        }

        $this->productImage4 = $productImage4;

        return $this;
    }

    public function getProductImage5(): ?ProductImage5
    {
        return $this->productImage5;
    }

    public function setProductImage5(?ProductImage5 $productImage5): self
    {
        // unset the owning side of the relation if necessary
        if (null === $productImage5 && null !== $this->productImage5) {
            $this->productImage5->setProduct(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $productImage5 && $productImage5->getProduct() !== $this) {
            $productImage5->setProduct($this);
        }

        $this->productImage5 = $productImage5;

        return $this;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setVideo(Video $video): self
    {
        // set the owning side of the relation if necessary
        if ($video->getProductColor() !== $this) {
            $video->setProductColor($this);
        }

        $this->video = $video;

        return $this;
    }

    /**
     * @return Collection<int, ProductData>
     */
    public function getProductData(): Collection
    {
        return $this->productData;
    }

    public function addProductData(ProductData $productData): self
    {
        if (!$this->productData->contains($productData)) {
            $this->productData[] = $productData;
            $productData->setColor($this);
        }

        return $this;
    }

    public function removeProductData(ProductData $productData): self
    {
        if ($this->productData->removeElement($productData)) {
            // set the owning side to null (unless already changed)
            if ($productData->getColor() === $this) {
                $productData->setColor(null);
            }
        }

        return $this;
    }

    public function getSimilarProductColor(): ?SimilarProductColor
    {
        return $this->similarProductColor;
    }

    public function setSimilarProductColor(?SimilarProductColor $similarProductColor): self
    {
        // unset the owning side of the relation if necessary
        if (null === $similarProductColor && null !== $this->similarProductColor) {
            $this->similarProductColor->setProductColor(null);
        }

        // set the owning side of the relation if necessary
        if ($similarProductColor->getProductColor() !== $this) {
            $similarProductColor->setProductColor($this);
        }

        $this->similarProductColor = $similarProductColor;

        return $this;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(Tag $tag): self
    {
        // set the owning side of the relation if necessary
        if ($tag->getProductColor() !== $this) {
            $tag->setProductColor($this);
        }

        $this->tag = $tag;

        return $this;
    }

    public function getTexture(): ?Texture
    {
        return $this->texture;
    }

    public function setTexture(Texture $texture): static
    {
        // set the owning side of the relation if necessary
        if ($texture->getProductColor() !== $this) {
            $texture->setProductColor($this);
        }

        $this->texture = $texture;

        return $this;
    }

    public function getImageMedium(): ?string
    {
        return $this->imageMedium;
    }

    public function setImageMedium(string $imageMedium): self
    {
        $this->imageMedium = $imageMedium;

        return $this;
    }

    public function getImageSmall(): ?string
    {
        return $this->imageSmall;
    }

    public function setImageSmall(string $imageSmall): self
    {
        $this->imageSmall = $imageSmall;

        return $this;
    }

    public function getImageOriginal(): ?string
    {
        return $this->imageOriginal;
    }

    public function setImageOriginal(string $imageOriginal): self
    {
        $this->imageOriginal = $imageOriginal;

        return $this;
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

    public function setImageCreated(): static
    {
        // $this->created = $created;
        $this->imageCreated = new DatetimeImmutable('now');

        return $this;
    }

    public function setImageUpdated()
    {
        $this->imageUpdated = new DateTime('now');

        return $this;
    }
}
