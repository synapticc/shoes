<?php

// src/Entity/Product/ProductColor/ExcludeColor.php

namespace App\Entity\Product\ProductColor;

use App\Repository\Product\ProductColor\ExcludeColorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExcludeColorRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'exclude_color')]
class ExcludeColor
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'exclude_color_exc_clr_id_seq', initialValue: 1, allocationSize: 1)]
    #[ORM\Column(name: 'exc_clr_id', type: Types::BIGINT)]
    private ?string $id = null;

    #[ORM\Column(nullable: true)]
    private ?array $colors = null;

    #[ORM\OneToOne(targetEntity: SimilarProductColor::class, inversedBy: 'excludeColor', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'fk_clr_pvt_sm_id', referencedColumnName: 'clr_pvt_sm_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: SimilarProductColor::class)]
    #[Assert\Valid]
    private ?SimilarProductColor $similarProductColor = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColors(): ?array
    {
        return $this->colors;
    }

    public function setColors(?array $colors): self
    {
        $this->colors = $colors;

        return $this;
    }

    public function getSimilarProductColor(): ?SimilarProductColor
    {
        return $this->similarProductColor;
    }

    public function setSimilarProductColor(SimilarProductColor $similarProductColor): self
    {
        $this->similarProductColor = $similarProductColor;

        return $this;
    }
}
