<?php

// src/Entity/Product/ProductColor/ExcludeProductColor.php

namespace App\Entity\Product\ProductColor;

use App\Entity\NoMap\Time\TimeUpdated;
use App\Repository\Product\ProductData\ExcludeProductColorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExcludeProductColorRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'exclude_product_color')]
class ExcludeProductColor
{
    // Time: $updated
    use TimeUpdated;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'exclude_product_color_oth_exc_clr_pvt_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'exc_clr_pvt_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: SimilarProductColor::class, inversedBy: 'excludeProductColors', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'fk_clr_pvt_sm_id', referencedColumnName: 'clr_pvt_sm_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: SimilarProductColor::class)]
    #[Assert\Valid]
    private ?SimilarProductColor $similarProductColor = null;

    #[ORM\ManyToOne(
        targetEntity: ProductColor::class,
        // inversedBy: 'excludeProductColors',
        cascade: ['persist']
    )]
    #[ORM\JoinColumn(name: 'fk_clr_pvt_id', referencedColumnName: 'clr_pvt_id', nullable: false)]
    #[Assert\Type(type: ProductColor::class)]
    #[Assert\Valid]
    private ?ProductColor $color = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSimilarProductColor(): ?SimilarProductColor
    {
        return $this->similarProductColor;
    }

    public function setSimilarProductColor(?SimilarProductColor $similarProductColor): self
    {
        $this->similarProductColor = $similarProductColor;

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
}
