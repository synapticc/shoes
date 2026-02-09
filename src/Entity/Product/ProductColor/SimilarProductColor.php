<?php

// src/Entity/Product/ProductColor/SimilarProductColor.php

namespace App\Entity\Product\ProductColor;

use App\Entity\NoMap\Time\TimeUpdated;
use App\Repository\Product\ProductColor\SimilarProductColorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SimilarProductColorRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'similar_product_color')]
class SimilarProductColor
{
    // Time: $updated
    use TimeUpdated;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'similar_product_color_clr_pvt_sm_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'clr_pvt_sm_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\OneToOne(
        targetEntity: ProductColor::class,
        inversedBy: 'similarProductColor',
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(
        name: 'fk_clr_pvt_id',
        referencedColumnName: 'clr_pvt_id',
        onDelete: 'CASCADE',
        nullable: false
    )]
    #[Assert\Type(type: ProductColor::class)]
    #[Assert\Valid]
    private ?ProductColor $productColor = null;

    /**
     * @var Collection<int, ExcludeProductColor>
     */
    #[ORM\OneToMany(
        targetEntity: ExcludeProductColor::class,
        mappedBy: 'similarProductColor',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $excludeProductColors;

    #[ORM\OneToOne(
        targetEntity: ExcludeColor::class,
        mappedBy: 'similarProductColor',
        orphanRemoval: true,
        cascade: ['persist', 'remove']
    )]
    private ?ExcludeColor $excludeColor = null;

    #[ORM\Column(nullable: true)]
    private ?array $sort = null;

    public function __construct()
    {
        $this->excludeProductColors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductColor(): ?ProductColor
    {
        return $this->productColor;
    }

    public function setProductColor(ProductColor $productColor): self
    {
        $this->productColor = $productColor;

        return $this;
    }

    /**
     * @return Collection<int, ExcludeProductColor>
     */
    public function getExcludeProductColors(): Collection
    {
        return $this->excludeProductColors;
    }

    public function addExcludeProductColor(ExcludeProductColor $excludeProductColor): self
    {
        if (!$this->excludeProductColors->contains($excludeProductColor)) {
            $this->excludeProductColors[] = $excludeProductColor;
            $excludeProductColor->setSimilarProductColor($this);
        }

        return $this;
    }

    public function removeExcludeProductColor(ExcludeProductColor $excludeProductColor): self
    {
        if ($this->excludeProductColors->removeElement($excludeProductColor)) {
            // set the owning side to null (unless already changed)
            if ($excludeProductColor->getSimilarProductColor() === $this) {
                $excludeProductColor->setSimilarProductColor(null);
            }
        }

        return $this;
    }

    /**
     * Removes all excludeProductColor from SimilarProductColor.
     *
     * @return $this
     */
    public function clearExcludeProductColor(): self
    {
        foreach ($this->getExcludeProductColors() as $excludeProductColor) {
            $this->removeExcludeProductColor($excludeProductColor);
        }

        return $this;
    }

    public function getExcludeColor(): ?ExcludeColor
    {
        return $this->excludeColor;
    }

    public function setExcludeColor(ExcludeColor $excludeColor): self
    {
        // set the owning side of the relation if necessary
        if ($excludeColor->getSimilarProductColor() !== $this) {
            $excludeColor->setSimilarProductColor($this);
        }

        $this->excludeColor = $excludeColor;

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
}
