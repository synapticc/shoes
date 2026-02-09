<?php

// src/Entity/Product/ProductColor/ProductColorTexture.php

namespace App\Entity\Product\ProductColor;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Product\ProductColor\ProductColorTextureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductColorTextureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProductColorTexture
{
    // Time: $created and $updated
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'product_color_texture_pro_tx_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'pro_tx_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(type: Types::JSONB, nullable: true)]
    private $texture;

    #[ORM\OneToOne(targetEntity: ProductColor::class, inversedBy: 'texture', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'fk_clr_pvt_id', referencedColumnName: 'clr_pvt_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: ProductColor::class)]
    #[Assert\Valid]
    private ?ProductColor $productColor = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexture()
    {
        return $this->texture;
    }

    public function setTexture($texture): static
    {
        $this->texture = $texture;

        return $this;
    }

    public function getProductColor(): ?ProductColor
    {
        return $this->productColor;
    }

    public function setProductColor(ProductColor $productColor): static
    {
        $this->productColor = $productColor;

        return $this;
    }
}
