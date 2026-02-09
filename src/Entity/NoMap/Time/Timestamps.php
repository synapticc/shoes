<?php

// src/Entity/NoMap/Time/Timestamps.php

namespace App\Entity\NoMap\Time;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PostUpdate;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

trait Timestamps
{
    #[Column(name: 'created', type: 'datetimetz_immutable')]
    private ?\DateTimeImmutable $created = null;

    #[Column(name: 'updated', type: 'datetimetz', nullable: true)]
    private $updated;

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    #[PrePersist]
    public function setCreated(): static
    {
        // $this->created = $created;
        $this->created = new \DateTimeImmutable('now');

        return $this;
    }

    // Retrieve updated time
    public function getUpdated()
    {
        return $this->updated;
    }

    #[PostUpdate]
    // #[PreUpdate]
    public function setUpdated()
    {
        $this->updated = new \DateTime('now');

        return $this;
    }
}
