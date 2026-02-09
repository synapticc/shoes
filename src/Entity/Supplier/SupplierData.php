<?php

// src/Entity/Supplier/SupplierData.php

namespace App\Entity\Supplier;

use App\Entity\Product\ProductData\ProductData;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SupplierRepository::class)]
#[ORM\Table(name: 'supplier_data')]
class SupplierData
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pivot_sp_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: Supplier::class, inversedBy: 'supplierData')]
    #[ORM\JoinColumn(name: 'fk_sp_id', referencedColumnName: 'sp_id', onDelete: 'CASCADE', nullable: true)]
    #[Assert\Type(type: Supplier::class)]
    #[Assert\Valid]
    private ?Supplier $supplier = null;

    /**
     * @var Collection<int, ProductData>
     */
    #[ORM\OneToMany(
        targetEntity: ProductData::class,
        mappedBy: 'supplier',
        orphanRemoval: true,
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $product;

    public function __construct()
    {
        $this->product = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @return Collection|ProductData[]
     */
    public function getProduct(): Collection
    {
        return $this->product;
    }

    public function addProduct(ProductData $product): self
    {
        if (!$this->product->contains($product)) {
            $this->product[] = $product;
            $product->setSupplier($this);
        }

        return $this;
    }

    public function removeProduct(ProductData $product): self
    {
        if ($this->product->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getSupplier() === $this) {
                $product->setSupplier(null);
            }
        }

        return $this;
    }
}
