<?php

// src/Entity/NoMap/Time/TimeCreated.php

namespace App\Entity\NoMap\Time;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PrePersist;

trait TimeCreated
{
    #[Column(name: 'created', type: 'datetimetz_immutable')]
    private ?\DateTimeImmutable $created = null;

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
}
