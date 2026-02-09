<?php

// src/Entity/Product/Product/ProductDiscontinued.php

namespace App\Entity\Product\Product;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Product\Product\ProductDiscontinuedRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductDiscontinuedRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProductDiscontinued
{
    // Time: $created and $updated
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'product_discontinued_pro_dscd_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'pro_dscd_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\OneToOne(targetEntity: Product::class, inversedBy: 'discontinued', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'fk_pro_id', referencedColumnName: 'pro_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: Product::class)]
    #[Assert\Valid]
    private ?Product $product = null;

    #[ORM\Column(name: 'is_discontinued', type: Types::BOOLEAN)]
    private ?bool $discontinued = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDiscontinued = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function isDiscontinued(): ?bool
    {
        return $this->discontinued;
    }

    public function setDiscontinued(bool $discontinued): self
    {
        $this->discontinued = $discontinued;

        return $this;
    }

    public function getDateDiscontinued(): ?\DateTimeInterface
    {
        return $this->dateDiscontinued;
    }

    public function setDateDiscontinued(\DateTimeInterface $dateDiscontinued): self
    {
        $this->dateDiscontinued = $dateDiscontinued;

        return $this;
    }
}
