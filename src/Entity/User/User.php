<?php

// src/Entity/User/User.php

namespace App\Entity\User;

use App\Entity\Billing\Order;
use App\Entity\NoMap\Time\Timestamps;
use App\Entity\Review\Review;
use App\Entity\Review\ReviewHelpful;
use App\Entity\User\Log\LoginReport;
use App\Entity\User\Log\LogoutReport;
use App\Entity\User\Session\Session;
use App\Repository\User\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks()]
#[ORM\Table(name: 'users')]
#[ORM\UniqueEntity(fields: 'email', message: 'There is already an account with this email.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // /**
    //  * @var Collection<int, LoginReport>
    //  */
    // #[ORM\OneToMany(targetEntity: LoginReport::class, mappedBy: 'users', orphanRemoval: true, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    // private Collection $loginReports;
    //
    // /**
    //  * @var Collection<int, LogoutReport>
    //  */
    // #[ORM\OneToMany(targetEntity: LogoutReport::class, mappedBy: 'users', orphanRemoval: true, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    // private Collection $logoutReports;
    //
    // /**
    //  * @var Collection<int, Session>
    //  */
    // #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'users', orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    // private Collection $sessions;

    // Time: $created and $updated
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'user_id', type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 40, nullable: false, unique: true)]
    private string $uuid;

    #[ORM\Column(length: 50, unique: true)]
    private string $email;

    #[ORM\Column]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(type: Types::STRING)]
    private string $password;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isVerified = false;

    #[ORM\Column(type: Types::STRING, length: 5, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING, length: 15, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::STRING, length: 15, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::STRING, length: 15, nullable: true)]
    private ?string $middleName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthday = null;

    #[ORM\OneToOne(
        targetEntity: UserPhone::class,
        mappedBy: 'users',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private ?UserPhone $userPhone = null;

    #[ORM\OneToOne(targetEntity: UserEmail::class, mappedBy: 'users', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?UserEmail $userEmail = null;

    #[ORM\OneToOne(targetEntity: UserAddress::class, mappedBy: 'users', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?UserAddress $userAddress = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'users', orphanRemoval: true, cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private Collection $orders;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(
        targetEntity: Review::class,
        mappedBy: 'users',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $productReviews;

    #[ORM\OneToOne(targetEntity: UserImage::class, mappedBy: 'users', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?UserImage $userImage = null;

    #[ORM\OneToOne(targetEntity: UserDelete::class, mappedBy: 'users', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?UserDelete $userDelete = null;

    #[ORM\OneToOne(mappedBy: 'users', cascade: ['persist', 'remove'])]
    private ?UserDeactivate $userDeactivate = null;

    #[ORM\OneToOne(mappedBy: 'users', cascade: ['persist', 'remove'])]
    private ?UserPosition $userPosition = null;

    #[ORM\Column(length: 10)]
    private ?string $theme = 'light';

    /**
     * @var Collection<int, ReviewHelpful>
     */
    #[ORM\OneToMany(
        targetEntity: ReviewHelpful::class,
        mappedBy: 'users',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
        fetch: 'EXTRA_LAZY'
    )]
    private Collection $reviewHelpfuls;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->productReviews = new ArrayCollection();
        $this->userData = new ArrayCollection();
        $this->loginReports = new ArrayCollection();
        $this->logoutReports = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->reviewHelpfuls = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getUserPhone(): ?UserPhone
    {
        return $this->userPhone;
    }

    public function setUserPhone(?UserPhone $userPhone): self
    {
        // unset the owning side of the relation if necessary
        if (null === $userPhone && null !== $this->userPhone) {
            $this->userPhone->setUsers(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $userPhone && $userPhone->getUsers() !== $this) {
            $userPhone->setUsers($this);
        }

        $this->userPhone = $userPhone;

        return $this;
    }

    public function getUserEmail(): ?UserEmail
    {
        return $this->userEmail;
    }

    public function setUserEmail(?UserEmail $userEmail): self
    {
        // unset the owning side of the relation if necessary
        if (null === $userEmail && null !== $this->userEmail) {
            $this->userEmail->setUsers(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $userEmail && $userEmail->getUsers() !== $this) {
            $userEmail->setUsers($this);
        }

        $this->userEmail = $userEmail;

        return $this;
    }

    public function getUserAddress(): ?UserAddress
    {
        return $this->userAddress;
    }

    public function setUserAddress(?UserAddress $userAddress): self
    {
        // unset the owning side of the relation if necessary
        if (null === $userAddress && null !== $this->userAddress) {
            $this->userAddress->setUsers(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $userAddress && $userAddress->getUsers() !== $this) {
            $userAddress->setUsers($this);
        }

        $this->userAddress = $userAddress;

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }
    //
    // public function addOrder(Order $order): self
    // {
    //     if (!$this->orders->contains($order)) {
    //         $this->orders[] = $order;
    //         $order->setUsers($this);
    //     }
    //
    //     return $this;
    // }
    //
    // public function removeOrder(Order $order): self
    // {
    //     if ($this->orders->removeElement($order)) {
    //         // set the owning side to null (unless already changed)
    //         if ($order->getUsers() === $this) {
    //             $order->setUsers(null);
    //         }
    //     }
    //
    //     return $this;
    // }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->productReviews;
    }

    public function addReview(Review $productReview): self
    {
        if (!$this->productReviews->contains($productReview)) {
            $this->productReviews[] = $productReview;
            $productReview->setUsers($this);
        }

        return $this;
    }

    public function removeReview(Review $productReview): self
    {
        if ($this->productReviews->removeElement($productReview)) {
            // set the owning side to null (unless already changed)
            if ($productReview->getUsers() === $this) {
                $productReview->setUsers(null);
            }
        }

        return $this;
    }

    public function getUserImage(): ?UserImage
    {
        return $this->userImage;
    }

    public function setUserImage(?UserImage $userImage): self
    {
        // unset the owning side of the relation if necessary
        if (null === $userImage && null !== $this->userImage) {
            $this->userImage->setUsers(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $userImage && $userImage->getUsers() !== $this) {
            $userImage->setUsers($this);
        }

        $this->userImage = $userImage;

        return $this;
    }

    public function getUserDelete(): ?UserDelete
    {
        return $this->userDelete;
    }

    public function setUserDelete(?UserDelete $userDelete): self
    {
        // unset the owning side of the relation if necessary
        if (null === $userDelete && null !== $this->userDelete) {
            $this->userDelete->setUsers(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $userDelete && $userDelete->getUsers() !== $this) {
            $userDelete->setUsers($this);
        }

        $this->userDelete = $userDelete;

        return $this;
    }

    // /**
    //  * @return Collection<int, LoginReport>
    //  */
    // public function getLoginReports(): Collection
    // {
    //     return $this->loginReports;
    // }
    //
    // public function addLoginReport(LoginReport $loginReport): self
    // {
    //     if (!$this->loginReports->contains($loginReport)) {
    //         $this->loginReports[] = $loginReport;
    //         $loginReport->setUsers($this);
    //     }
    //
    //     return $this;
    // }
    //
    // public function removeLoginReport(LoginReport $loginReport): self
    // {
    //     if ($this->loginReports->removeElement($loginReport)) {
    //         // set the owning side to null (unless already changed)
    //         if ($loginReport->getUsers() === $this) {
    //             $loginReport->setUsers(null);
    //         }
    //     }
    //
    //     return $this;
    // }
    //
    // /**
    //  * @return Collection<int, LogoutReport>
    //  */
    // public function getLogoutReports(): Collection
    // {
    //     return $this->logoutReports;
    // }
    //
    // public function addLogoutReport(LogoutReport $logoutReport): self
    // {
    //     if (!$this->logoutReports->contains($logoutReport)) {
    //         $this->logoutReports[] = $logoutReport;
    //         $logoutReport->setUsers($this);
    //     }
    //
    //     return $this;
    // }
    //
    // public function removeLogoutReport(LogoutReport $logoutReport): self
    // {
    //     if ($this->logoutReports->removeElement($logoutReport)) {
    //         // set the owning side to null (unless already changed)
    //         if ($logoutReport->getUsers() === $this) {
    //             $logoutReport->setUsers(null);
    //         }
    //     }
    //
    //     return $this;
    // }
    //
    // /**
    //  * @return Collection<int, Session>
    //  */
    // public function getSessions(): Collection
    // {
    //     return $this->sessions;
    // }
    //
    // public function addSession(Session $session): self
    // {
    //     if (!$this->sessions->contains($session)) {
    //         $this->sessions[] = $session;
    //         $session->setUsers($this);
    //     }
    //
    //     return $this;
    // }
    //
    // public function removeSession(Session $session): self
    // {
    //     if ($this->sessions->removeElement($session)) {
    //         // set the owning side to null (unless already changed)
    //         if ($session->getUsers() === $this) {
    //             $session->setUsers(null);
    //         }
    //     }
    //
    //     return $this;
    // }

    public function getUserDeactivate(): ?UserDeactivate
    {
        return $this->userDeactivate;
    }

    public function setUserDeactivate(UserDeactivate $userDeactivate): static
    {
        // set the owning side of the relation if necessary
        if ($userDeactivate->getUsers() !== $this) {
            $userDeactivate->setUsers($this);
        }

        $this->userDeactivate = $userDeactivate;

        return $this;
    }

    public function getUserPosition(): ?UserPosition
    {
        return $this->userPosition;
    }

    public function setUserPosition(UserPosition $userPosition): static
    {
        // set the owning side of the relation if necessary
        if ($userPosition->getUsers() !== $this) {
            $userPosition->setUsers($this);
        }

        $this->userPosition = $userPosition;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return Collection<int, ReviewHelpful>
     */
    public function getReviewHelpfuls(): Collection
    {
        return $this->reviewHelpfuls;
    }

    public function addReviewHelpful(ReviewHelpful $reviewHelpful): static
    {
        if (!$this->reviewHelpfuls->contains($reviewHelpful)) {
            $this->reviewHelpfuls->add($reviewHelpful);
            $reviewHelpful->setUsers($this);
        }

        return $this;
    }

    public function removeReviewHelpful(ReviewHelpful $reviewHelpful): static
    {
        if ($this->reviewHelpfuls->removeElement($reviewHelpful)) {
            // set the owning side to null (unless already changed)
            if ($reviewHelpful->getUsers() === $this) {
                $reviewHelpful->setUsers(null);
            }
        }

        return $this;
    }

    public function serialize()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
