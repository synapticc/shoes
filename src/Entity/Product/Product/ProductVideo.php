<?php

// src/Entity/Product/Product/ProductVideo.php

namespace App\Entity\Product\Product;

use App\Entity\NoMap\Time\Timestamps;
use App\Repository\Product\Product\ProductVideoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductVideoRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProductVideo
{
    // Time: $created and $updated
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'product_video_pro_vid_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'pro_vid_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\OneToOne(targetEntity: Product::class, inversedBy: 'video', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'fk_pro_id', referencedColumnName: 'pro_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: Product::class)]
    #[Assert\Valid]
    private ?Product $product = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private ?string $videoUrl = null;

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

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(string $videoUrl): self
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }
}
