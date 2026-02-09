<?php

// src/Entity/User/Session/Session.php

namespace App\Entity\User\Session;

use App\Entity\NoMap\Time\TimeCreated;
use App\Entity\User\Log\LoginReport;
use App\Entity\User\User;
use App\Repository\User\Session\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\Table(name: '`tracker_session`')]
class Session
{
    // Time: $created
    use TimeCreated;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'sess_id', type: Types::BIGINT)]
    private ?int $id = null;

    /**
     * @var Collection<int, PageView>
     */
    #[ORM\OneToMany(
        targetEntity: PageView::class,
        mappedBy: 'session',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $pageViews;

    #[ORM\ManyToOne(
        targetEntity: User::class,
        // inversedBy: 'sessions',
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(
        name: 'fk_user_id',
        referencedColumnName: 'user_id',
        nullable: true,
    )]
    #[Assert\Type(type: User::class)]
    #[Assert\Valid]
    private ?User $users = null;

    #[ORM\OneToOne(
        targetEntity: LoginReport::class,
        mappedBy: 'session',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        fetch: 'EXTRA_LAZY'
    )]
    private ?LoginReport $loginReport = null;

    #[ORM\Column(length: 150)]
    private ?string $userAgent = null;

    #[ORM\Column(length: 150)]
    private ?string $ipAddress = null;

    public function __construct()
    {
        $this->pageViews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // /**
    //  * @return Collection<int, PageView>
    //  */
    // public function getPageViews(): Collection
    // {
    //     return $this->pageViews;
    // }

    public function addPageView(PageView $pageView): self
    {
        if (!$this->pageViews->contains($pageView)) {
            $this->pageViews[] = $pageView;
            $pageView->setSession($this);
        }

        return $this;
    }

    // public function removePageView(PageView $pageView): self
    // {
    //     if ($this->pageViews->removeElement($pageView)) {
    //         // set the owning side to null (unless already changed)
    //         if ($pageView->getSession() === $this) {
    //             $pageView->setSession(null);
    //         }
    //     }
    //
    //     return $this;
    // }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getLoginReport(): ?LoginReport
    {
        return $this->loginReport;
    }

    public function setLoginReport(LoginReport $loginReport): self
    {
        // set the owning side of the relation if necessary
        if ($loginReport->getSession() !== $this) {
            $loginReport->setSession($this);
        }

        $this->loginReport = $loginReport;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }
}
