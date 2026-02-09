<?php

// src/Entity/Billing/BillingDelivery.php

namespace App\Entity\Billing;

use App\Entity\NoMap\Time\TimeCreated;
use App\Repository\Billing\BillingDeliveryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: BillingDeliveryRepository::class)]
class BillingDelivery
{
    // Time: $created
    use TimeCreated;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'bild_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $deliveryNotes = null;

    #[ORM\OneToOne(
        targetEntity: Billing::class,
        inversedBy: 'billingDelivery',
        cascade: ['persist']
    )]
    #[ORM\JoinColumn(
        name: 'fk_bill_id',
        referencedColumnName: 'bill_id',
        onDelete: 'CASCADE',
        nullable: false
    )]
    private ?Billing $billing = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeliveryNotes(): ?string
    {
        return $this->deliveryNotes;
    }

    public function setDeliveryNotes(string $deliveryNotes): static
    {
        $this->deliveryNotes = $deliveryNotes;

        return $this;
    }

    public function getBilling(): ?Billing
    {
        return $this->billing;
    }

    public function setBilling(Billing $billing): static
    {
        $this->billing = $billing;

        return $this;
    }
}
