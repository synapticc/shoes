<?php

// src/Entity/NoMap/Transfer/Billing/OrderTransfer.php

namespace App\Entity\NoMap\Transfer\Billing;

use Symfony\Component\Validator\Constraints as Assert;

class OrderTransfer
{
    public function __construct(
        #[Assert\GreaterThanOrEqual(1)]
        public readonly int $order_id,
        public readonly ?string $status,
        public readonly ?string $userAgent,
        #[Assert\Valid]
        #[Assert\NotBlank]
        public bool $activeStatus,
    ) {
    }

    public function getId(): ?int
    {
        return $this->order_id;
    }

    // /**
    // * @return Collection|OrderItem[]
    //  */
    // public function getItems(): Collection
    // {
    //     return $this->items;
    // }

    // public function addItem(OrderItem $item): self
    // {
    //     if (!$this->items->contains($item)) {
    //         $this->items[] = $item;
    //         $item->setOrderRef($this);
    //     }
    //
    //     return $this;
    // }

    // public function addItem(OrderItem $item): self
    // {
    //     foreach ($this->getItems() as $existingItem) {
    //         // The item already exists, update the quantity
    //         if ($existingItem->equals($item)) {
    //             $existingItem->setQuantity(
    //                 $existingItem->getQuantity() + $item->getQuantity()
    //             );
    //             return $this;
    //         }
    //     }
    //
    //     $this->items[] = $item;
    //     $item->setOrderRef($this);
    //
    //     return $this;
    // }
    //
    // public function removeItem(OrderItem $item): self
    // {
    //     if ($this->items->removeElement($item)) {
    //         // set the owning side to null (unless already changed)
    //         if ($item->getOrderRef() === $this) {
    //             $item->setOrderRef(null);
    //         }
    //     }
    //
    //     return $this;
    // }

    /**
     * Removes all items from the order.
     *
     * @return $this
     */
    // public function removeItems(): self
    // {
    //     foreach ($this->getItems() as $item) {
    //         $this->removeItem($item);
    //     }
    //
    //     return $this;
    // }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
    //
    // /**
    //  * Calculates the order total.
    //  *
    //  * @return float
    // //  */
    // // public function getTotal(): float
    // // {
    // //     $total = 0;
    // //
    // //     foreach ($this->getItems() as $item) {
    // //         $total += $item->getTotal();
    // //     }
    // //
    // //     return $total;
    // // }
    //
    // public function getUsers(): ?User
    // {
    //     return $this->users;
    // }
    //
    // public function setUsers(?User $users): self
    // {
    //     $this->users = $users;
    //
    //     return $this;
    // }

    // public function getCompany(): ?string
    // {
    //     return $this->company;
    // }
    //
    // public function setCompany(?string $company): self
    // {
    //     $this->company = $company;
    //
    //     return $this;
    // }

    // public function getBillingPhone(): ?BillingPhone
    // {
    //     return $this->billingPhone;
    // }
    //
    // public function setBillingPhone(?BillingPhone $billingPhone): self
    // {
    //     // unset the owning side of the relation if necessary
    //     if ($billingPhone === null && $this->billingPhone !== null) {
    //         $this->billingPhone->setOrderDetails(null);
    //     }
    //
    //     // set the owning side of the relation if necessary
    //     if ($billingPhone !== null && $billingPhone->getOrderDetails() !== $this) {
    //         $billingPhone->setOrderDetails($this);
    //     }
    //
    //     $this->billingPhone = $billingPhone;
    //
    //     return $this;
    // }
    //
    // public function getBillingAddress(): ?BillingAddress
    // {
    //     return $this->billingAddress;
    // }
    //
    // public function setBillingAddress(?BillingAddress $billingAddress): self
    // {
    //     // unset the owning side of the relation if necessary
    //     if ($billingAddress === null && $this->billingAddress !== null) {
    //         $this->billingAddress->setOrderDetails(null);
    //     }
    //
    //     // set the owning side of the relation if necessary
    //     if ($billingAddress !== null && $billingAddress->getOrderDetails() !== $this) {
    //         $billingAddress->setOrderDetails($this);
    //     }
    //
    //     $this->billingAddress = $billingAddress;
    //
    //     return $this;
    // }
    //
    // public function getBillingMethod(): ?BillingMethod
    // {
    //     return $this->billingMethod;
    // }
    //
    // public function setBillingMethod(?BillingMethod $billingMethod): self
    // {
    //     // unset the owning side of the relation if necessary
    //     if ($billingMethod === null && $this->billingMethod !== null) {
    //         $this->billingMethod->setOrderDetails(null);
    //     }
    //
    //     // set the owning side of the relation if necessary
    //     if ($billingMethod !== null && $billingMethod->getOrderDetails() !== $this) {
    //         $billingMethod->setOrderDetails($this);
    //     }
    //
    //     $this->billingMethod = $billingMethod;
    //
    //     return $this;
    // }

    // public function getDateOfPurchase(): ?DateTimeInterface
    // {
    //     return $this->dateOfPurchase;
    // }
    //
    // public function setDateOfPurchase(?DateTimeInterface $dateOfPurchase): self
    // {
    //     $this->dateOfPurchase = $dateOfPurchase;
    //
    //     return $this;
    // }

    // public function getInvoiceTotal(): ?float
    // {
    //     return $this->invoiceTotal;
    // }
    //
    // public function setInvoiceTotal(?float $invoiceTotal): self
    // {
    //     $this->invoiceTotal = $invoiceTotal;
    //
    //     return $this;
    // }

    // public function getBilling(): ?Billing
    // {
    //     return $this->billing;
    // }
    //
    // public function setBilling(?Billing $billing): self
    // {
    //     // unset the owning side of the relation if necessary
    //     if ($billing === null && $this->billing !== null) {
    //         $this->billing->setOrderDetails(null);
    //     }
    //
    //     // set the owning side of the relation if necessary
    //     if ($billing !== null && $billing->getOrderDetails() !== $this) {
    //         $billing->setOrderDetails($this);
    //     }
    //
    //     $this->billing = $billing;
    //
    //     return $this;
    // }

    // public function getUserAgent(): ?string
    // {
    //     return $this->userAgent;
    // }
    //
    // public function setUserAgent(?string $userAgent): self
    // {
    //     $this->userAgent = $userAgent;
    //
    //     return $this;
    // }
    //
    public function isActiveStatus(): ?bool
    {
        return $this->activeStatus;
    }

    public function setActiveStatus(?bool $activeStatus): self
    {
        $this->activeStatus = $activeStatus;

        return $this;
    }
}
