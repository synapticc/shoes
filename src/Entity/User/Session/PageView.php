<?php

// src/Entity/User/Session/PageView.php

namespace App\Entity\User\Session;

use App\Entity\NoMap\Time\TimeCreated;
use App\Entity\Product\ProductData\ProductData;
use App\Repository\User\Session\PageViewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PageViewRepository::class)]
#[ORM\Table(name: '`tracker_pages`')]
class PageView
{
    // Time: $created
    use TimeCreated;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'pg_id', type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $url = null;

    #[ORM\ManyToOne(
        targetEntity: Session::class,
        inversedBy: 'pageViews'
    )]
    #[ORM\JoinColumn(
        name: 'fk_sess_id',
        referencedColumnName: 'sess_id',
        onDelete: 'CASCADE',
        nullable: false
    )]
    #[Assert\Type(type: Session::class)]
    #[Assert\Valid]
    private ?Session $session = null;

    #[ORM\ManyToOne(
        targetEntity: ProductData::class,
        // inversedBy: 'pageViews',
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(
        name: 'fk_pro_pvt_id',
        referencedColumnName: 'pro_pvt_id',
        onDelete: 'CASCADE',
        nullable: true
    )]
    #[Assert\Type(type: ProductData::class)]
    #[Assert\Valid]
    private ?ProductData $product = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $referer = null;

    #[ORM\Column(nullable: true)]
    private ?array $queryParameters = null;

    #[ORM\Column(type: Types::STRING, length: 45, nullable: true)]
    private ?string $route = null;

    #[ORM\ManyToOne(
        targetEntity: ProductData::class,
        // inversedBy: 'pageViews',
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(
        name: 'fk_pro_ref_id',
        referencedColumnName: 'pro_pvt_id',
        onDelete: 'CASCADE',
        nullable: true
    )]
    #[Assert\Type(type: ProductData::class)]
    #[Assert\Valid]
    private ?ProductData $refererProduct = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $refererRoute = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getProduct(): ?ProductData
    {
        return $this->product;
    }

    public function setProduct(?ProductData $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function setReferer(?string $referer): self
    {
        $this->referer = $referer;

        return $this;
    }

    public function getQueryParameters(): ?array
    {
        return $this->queryParameters;
    }

    public function setQueryParameters(?array $queryParameters): static
    {
        $this->queryParameters = $queryParameters;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getRefererProduct(): ?ProductData
    {
        return $this->refererProduct;
    }

    public function setRefererProduct(?ProductData $refererProduct): static
    {
        $this->refererProduct = $refererProduct;

        return $this;
    }

    public function getRefererRoute(): ?string
    {
        return $this->refererRoute;
    }

    public function setRefererRoute(string $refererRoute): static
    {
        $this->refererRoute = $refererRoute;

        return $this;
    }
}
