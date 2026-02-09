<?php

// src/Entity/Billing/Billing.php

namespace App\Entity\Billing;

use App\Repository\Billing\BillingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;
use libphonenumber\PhoneNumber;

#[ORM\Entity(repositoryClass: BillingRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Billing
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'bill_id', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(
        targetEntity: Order::class,
        inversedBy: 'billing',
        cascade: ['persist']
    )]
    #[ORM\JoinColumn(
        name: 'fk_ord_id',
        referencedColumnName: 'ord_id',
        onDelete: 'CASCADE',
        nullable: false
    )]
    private ?Order $orderBilling = null;

    #[ORM\Column(type: 'phone_number')]
    private ?PhoneNumber $mobile = null;

    #[ORM\Column(type: 'phone_number')]
    private ?PhoneNumber $landline = null;

    #[ORM\Column(length: 150)]
    private ?string $street = null;

    #[ORM\Column(length: 50)]
    private ?string $city = null;

    #[ORM\Column]
    private ?int $zip = null;

    #[ORM\Column(length: 4)]
    private ?string $country = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $cardNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $expiryDate = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $cvc = null;

    #[ORM\Column(length: 60)]
    private ?string $cardHolder = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $invoicePath = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $invoiceThumbnail = null;

    #[ORM\Column(nullable: true)]
    private ?float $invoiceTotal = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeImmutable $purchaseDate = null;

    #[ORM\OneToOne(
        targetEntity: BillingDelivery::class,
        mappedBy: 'billing',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private ?BillingDelivery $billingDelivery = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderBilling(): ?Order
    {
        return $this->orderBilling;
    }

    public function setOrderBilling(Order $orderBilling): static
    {
        $this->orderBilling = $orderBilling;

        return $this;
    }

    public function getMobile(): ?PhoneNumber
    {
        return $this->mobile;
    }

    public function setMobile(PhoneNumber $mobile): static
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getLandline(): ?PhoneNumber
    {
        return $this->landline;
    }

    public function setLandline(PhoneNumber $landline): static
    {
        $this->landline = $landline;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZip(): ?int
    {
        return $this->zip;
    }

    public function setZip(int $zip): static
    {
        $this->zip = $zip;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCardNumber(): ?string
    {
        return $this->cardNumber;
    }

    public function setCardNumber(?string $cardNumber): static
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    public function getExpiryDate(): ?\DateTime
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(\DateTime $expiryDate): static
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    public function getCvc(): ?int
    {
        return $this->cvc;
    }

    public function setCvc(int $cvc): static
    {
        $this->cvc = $cvc;

        return $this;
    }

    public function getCardHolder(): ?string
    {
        return $this->cardHolder;
    }

    public function setCardHolder(string $cardHolder): static
    {
        $this->cardHolder = $cardHolder;

        return $this;
    }

    public function getInvoicePath(): ?string
    {
        return $this->invoicePath;
    }

    public function setInvoicePath(?string $invoicePath): static
    {
        $this->invoicePath = $invoicePath;

        return $this;
    }

    public function getInvoiceThumbnail(): ?string
    {
        return $this->invoiceThumbnail;
    }

    public function setInvoiceThumbnail(?string $invoiceThumbnail): static
    {
        $this->invoiceThumbnail = $invoiceThumbnail;

        return $this;
    }

    public function getInvoiceTotal(): ?float
    {
        return $this->invoiceTotal;
    }

    public function setInvoiceTotal(?float $invoiceTotal): static
    {
        $this->invoiceTotal = $invoiceTotal;

        return $this;
    }

    public function getBillingDelivery(): ?BillingDelivery
    {
        return $this->billingDelivery;
    }

    public function setBillingDelivery(BillingDelivery $billingDelivery): static
    {
        // set the owning side of the relation if necessary
        if ($billingDelivery->getBilling() !== $this) {
            $billingDelivery->setBilling($this);
        }

        $this->billingDelivery = $billingDelivery;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeImmutable
    {
        return $this->purchaseDate;
    }

    #[PrePersist]
    public function setPurchaseDate(): static
    {
        // $this->purchaseDate = $purchaseDate;
        $this->purchaseDate = new \DateTimeImmutable('now');

        return $this;
    }
}
