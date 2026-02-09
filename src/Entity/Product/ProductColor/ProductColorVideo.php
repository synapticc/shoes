<?php

// src/Entity/Product/ProductColor/ProductColorVideo.php

namespace App\Entity\Product\ProductColor;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Product\ProductColor\ProductColorVideoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductColorVideoRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'product_color_video')]
class ProductColorVideo
{
    // Time: $created and $updated
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'product_color_video_pro_vid_pvt_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'pro_pvt_vid_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\OneToOne(targetEntity: ProductColor::class, inversedBy: 'video', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'fk_clr_pvt_id', referencedColumnName: 'clr_pvt_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: ProductColor::class)]
    #[Assert\Valid]
    private ?ProductColor $productColor = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): self
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    public function getProductColor(): ?ProductColor
    {
        return $this->productColor;
    }

    public function setProductColor(ProductColor $productColor): self
    {
        $this->productColor = $productColor;

        return $this;
    }
}
