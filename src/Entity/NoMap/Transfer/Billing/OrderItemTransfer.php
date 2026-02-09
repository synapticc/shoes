<?php

// src/Entity/NoMap/Transfer/Billing/OrderItemTransfer.php

namespace App\Entity\NoMap\Transfer\Billing;

class OrderItemTransfer
{
    public function __construct(
        #[Assert\GreaterThanOrEqual(1)]
        public readonly int $order_id,
        #[Assert\GreaterThanOrEqual(1)]
        public readonly int $item_id,
        #[Assert\GreaterThanOrEqual(1)]
        public readonly int $id,
        #[Assert\GreaterThanOrEqual(1)]
        public int $quantity,
        public string $color,
        public array $fabrics,
        public int $pc,
    ) {
    }

    public function getId(): ?int
    {
        return $this->item_id;
    }

    public function getProduct(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?int
    {
        return $this->order_id;
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
}
