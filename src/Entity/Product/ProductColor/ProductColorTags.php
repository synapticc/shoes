<?php

// src/Entity/Product/ProductColor/ProductColorTags.php

namespace App\Entity\Product\ProductColor;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Product\ProductColor\ProductColorTagsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductColorTagsRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'product_color_tags')]
class ProductColorTags
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'product_color_tags_pro_tg_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'pro_tg_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(type: Types::JSONB, nullable: true)]
    private $tag;

    #[ORM\OneToOne(targetEntity: ProductColor::class, inversedBy: 'tag')] // ,
    #[ORM\JoinColumn(name: 'fk_clr_pvt_id', referencedColumnName: 'clr_pvt_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: ProductColor::class)]
    #[Assert\Valid]
    private ?ProductColor $productColor = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag()
    {
        return $this->tags;
    }

    public function setTag($tag): static
    {
        $this->tag = $tag;

        return $this;
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
}
