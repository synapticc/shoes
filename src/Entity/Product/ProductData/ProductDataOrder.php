<?php

// src/Entity/Product/ProductData/ProductDataOrder.php

namespace App\Entity\Product\ProductData;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Product\ProductData\ProductDataOrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductDataOrderRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProductDataOrder
{
    // Time: $created and $updated
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'product_data_order_pro_ord_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'pro_ord_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $qtyOnOrder = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $reorderLevel = null;

    #[ORM\OneToOne(targetEntity: ProductData::class, inversedBy: 'productDataOrder', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'fk_pro_pvt_id', referencedColumnName: 'pro_pvt_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: ProductData::class)]
    #[Assert\Valid]
    private ?ProductData $productData = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQtyOnOrder(): ?int
    {
        return $this->qtyOnOrder;
    }

    public function setQtyOnOrder(int $qtyOnOrder): self
    {
        $this->qtyOnOrder = $qtyOnOrder;

        return $this;
    }

    public function getReorderLevel(): ?int
    {
        return $this->reorderLevel;
    }

    public function setReorderLevel(?int $reorderLevel): self
    {
        $this->reorderLevel = $reorderLevel;

        return $this;
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
}
