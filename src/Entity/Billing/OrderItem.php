<?php

// src/Entity/Billing/OrderItem.php

namespace App\Entity\Billing;

use App\Entity\NoMap\Time\Timestamps;
use App\Entity\Product\ProductData\ProductData;
use App\Repository\Billing\OrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_item')]
class OrderItem
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'ord_itm_id', type: Types::BIGINT)]
    private ?string $item_id = null;

    #[ORM\ManyToOne(
        targetEntity: ProductData::class,
        inversedBy: 'items',
        fetch: 'EXTRA_LAZY'
    )]
    #[ORM\JoinColumn(
        name: 'fk_pro_id',
        referencedColumnName: 'pro_pvt_id',
        onDelete: 'CASCADE',
        nullable: false
    )]
    #[Assert\Type(type: ProductData::class)]
    #[Assert\Valid]
    private ?ProductData $product = null;

    #[ORM\ManyToOne(
        targetEntity: Order::class,
        inversedBy: 'items',
        fetch: 'EXTRA_LAZY'
    )]
    #[ORM\JoinColumn(
        name: 'fk_ord_id',
        referencedColumnName: 'ord_id',
        onDelete: 'CASCADE',
        nullable: false
    )]
    #[Assert\Type(type: Order::class)]
    #[Assert\Valid]
    private ?Order $orderRef = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(1)]
    private ?int $quantity = null;

    public function getId(): ?int
    {
        return $this->item_id;
    }

    public function getProductData(): ?ProductData
    {
        return $this->product;
    }

    public function setProductData(?ProductData $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getOrderRef(): ?Order
    {
        return $this->orderRef;
    }

    public function setOrderRef(?Order $orderRef): self
    {
        $this->orderRef = $orderRef;

        return $this;
    }

    /**
     * Tests if the given item given corresponds to the same order item.
     */
    public function equals(OrderItem $item): bool
    {
        return $this->getProductData()->getId() === $item->getProductData()->getId();
    }

    /**
     * Calculates the item total.
     *
     * @return float|int
     */
    public function getTotal(): float
    {
        return $this->getProductData()->getSellingPrice() * $this->getQuantity();
    }
}
