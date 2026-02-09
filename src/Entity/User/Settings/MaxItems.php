<?php

// src/Entity/User/Settings/MaxItems.php

namespace App\Entity\User\Settings;

use App\Entity\NoMap\Time\TimeUpdated;
use App\Repository\User\Settings\MaxItemsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MaxItemsRepository::class)]
class MaxItems
{
    // Time: $created and $updated
    use TimeUpdated;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'max_id', type: Types::SMALLINT)]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(min: 5, max: 75)]
    private ?int $listing = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $reviews = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $recent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getListing(): ?int
    {
        return $this->listing;
    }

    public function setListing(int $listing): static
    {
        $this->listing = $listing;

        return $this;
    }

    public function getReviews(): ?int
    {
        return $this->reviews;
    }

    public function setReviews(int $reviews): static
    {
        $this->reviews = $reviews;

        return $this;
    }

    public function getRecent(): ?int
    {
        return $this->recent;
    }

    public function setRecent(int $recent): static
    {
        $this->recent = $recent;

        return $this;
    }
}
