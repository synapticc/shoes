<?php

// src/Entity/Product/Product/ProductQtyPack.php

namespace App\Entity\Product\Product;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Product\Product\ProductQtyPackRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductQtyPackRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProductQtyPack
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'product_qty_pack_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'pro_pck_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $qtyPack = null;

    #[ORM\OneToOne(targetEntity: Product::class, inversedBy: 'qtyPack', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'fk_pro_id', referencedColumnName: 'pro_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: Product::class)]
    #[Assert\Valid]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQtyPack(): ?int
    {
        return $this->qtyPack;
    }

    public function setQtyPack(int $qtyPack): self
    {
        $this->qtyPack = $qtyPack;

        return $this;
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
}
