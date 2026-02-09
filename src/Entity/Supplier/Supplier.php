<?php

// src/Entity/Supplier/Supplier.php

namespace App\Entity\Supplier;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Supplier\SupplierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: SupplierRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['name'], message: 'There is already a supplier with this name.')]
#[ORM\Table(name: 'suppliers')]
class Supplier
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'sp_id', type: Types::SMALLINT)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'phone_number', nullable: true)]
    private $phone;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $city = null;

    /**
     * @var Collection<int,SupplierData>
     */
    #[ORM\OneToMany(targetEntity: SupplierData::class, mappedBy: 'supplier', orphanRemoval: true, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private Collection $supplierData;

    #[ORM\Column(type: Types::STRING, length: 5, nullable: true)]
    private ?string $countryCode = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $country = null;

    public function __construct()
    {
        $this->supplierData = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection|SupplierData[]
     */
    public function getSupplierData(): Collection
    {
        return $this->supplierData;
    }

    public function addSupplierData(SupplierData $supplierData): self
    {
        if (!$this->supplierData->contains($supplierData)) {
            $this->supplierData[] = $supplierData;
            $supplierData->setSupplier($this);
        }

        return $this;
    }

    public function removeSupplierData(SupplierData $supplierData): self
    {
        if ($this->supplierData->removeElement($supplierData)) {
            // set the owning side to null (unless already changed)
            if ($supplierData->getSupplier() === $this) {
                $supplierData->setSupplier(null);
            }
        }

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }
}
