<?php

// src/Entity/Product/ProductImage/ProductImage5.php

namespace App\Entity\Product\ProductImage;

use App\Entity\NoMap\Time\Timestamps;
use App\Entity\Product\ProductColor\ProductColor;
use App\Repository\Product\ProductImage\ProductImage5Repository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductImage5Repository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'product_image5')]
class ProductImage5
{
    // Time: $created and $updated
    use Timestamps;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'product_image_pro_img5_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'pro_img5_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(type: Types::STRING, length: 150, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(name: 'image_md', type: Types::STRING, length: 150, nullable: true)]
    private ?string $imageMedium = null;

    #[ORM\Column(name: 'image_sm', type: Types::STRING, length: 150, nullable: true)]
    private ?string $imageSmall = null;

    #[ORM\Column(type: Types::STRING, length: 150, nullable: true)]
    private ?string $imageOriginal = null;

    #[ORM\OneToOne(targetEntity: ProductColor::class, inversedBy: 'productImage5')]
    #[ORM\JoinColumn(name: 'fk_clr_pvt_id', referencedColumnName: 'clr_pvt_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: ProductColor::class)]
    #[Assert\Valid]
    private ?ProductColor $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImageMedium(): ?string
    {
        return $this->imageMedium;
    }

    public function setImageMedium(string $imageMedium): self
    {
        $this->imageMedium = $imageMedium;

        return $this;
    }

    public function getImageSmall(): ?string
    {
        return $this->imageSmall;
    }

    public function setImageSmall(string $imageSmall): self
    {
        $this->imageSmall = $imageSmall;

        return $this;
    }

    public function getImageOriginal(): ?string
    {
        return $this->imageOriginal;
    }

    public function setImageOriginal(string $imageOriginal): self
    {
        $this->imageOriginal = $imageOriginal;

        return $this;
    }

    public function getProduct(): ?ProductColor
    {
        return $this->product;
    }

    public function setProduct(?ProductColor $product): self
    {
        $this->product = $product;

        return $this;
    }
}
