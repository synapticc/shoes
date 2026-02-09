<?php

// src/Entity/Billing/Order.php

namespace App\Entity\Billing;

use App\Entity\NoMap\Time\Timestamps;
use App\Entity\User\User;
use App\Repository\Billing\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: '`orders`')]
class Order
{
    // Time: $created and $updated
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'ord_id', type: Types::BIGINT)]
    private ?string $order_id = null;

    #[ORM\ManyToOne(
        targetEntity: User::class,
        inversedBy: 'orders',
        fetch: 'EXTRA_LAZY'
    )]
    #[ORM\JoinColumn(
        name: 'fk_user_id',
        referencedColumnName: 'user_id',
        onDelete: 'CASCADE',
        nullable: true
    )]
    #[Assert\Type(type: User::class)]
    #[Assert\Valid]
    private ?User $users = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(
        targetEntity: OrderItem::class,
        mappedBy: 'orderRef',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $items;

    #[ORM\Column(type: Types::STRING, length: 10)]
    private ?string $status = self::STATUS_CART;

    #[ORM\Column(type: Types::STRING, length: 150, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $activeStatus = null;

    #[ORM\OneToOne(
        targetEntity: Billing::class,
        mappedBy: 'orderBilling',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private ?Billing $billing = null;

    /**
     * An order that is in progress, not placed yet.
     *
     * @var string
     */
    public const STATUS_CART = 'cart';

    /**
     * An order that is paid and confirmed.
     *
     * @var string
     */
    public const STATUS_PAID = 'paid';

    public const COOKIE_CART = 'cart';

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->order_id;
    }

    /**
     * @return Collection|OrderItem[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setOrderRef($this);
        }

        return $this;
    }

    // public function addItem(OrderItem $item): self
    // {
    //     foreach ($this->getItems() as $existingItem)
    //     {
    //         // The item already exists, update the quantity
    //         if ($existingItem->equals($item))
    //         {
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

    public function removeItem(OrderItem $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getOrderRef() === $this) {
                $item->setOrderRef(null);
            }
        }

        return $this;
    }

    /**
     * Removes all items from the order.
     *
     * @return $this
     */
    public function removeItems(): self
    {
        foreach ($this->getItems() as $item) {
            $this->removeItem($item);
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Calculates the order total.
     */
    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getItems() as $item) {
            $total += $item->getTotal();
        }

        return $total;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function isActiveStatus(): ?bool
    {
        return $this->activeStatus;
    }

    public function setActiveStatus(?bool $activeStatus): self
    {
        $this->activeStatus = $activeStatus;

        return $this;
    }

    public function getBilling(): ?Billing
    {
        return $this->billing;
    }

    public function setBilling(Billing $billing): static
    {
        // set the owning side of the relation if necessary
        if ($billing->getOrderBilling() !== $this) {
            $billing->setOrderBilling($this);
        }

        $this->billing = $billing;

        return $this;
    }
}
