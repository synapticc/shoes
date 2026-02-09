<?php

// src/Entity/NoMap/Time/TimeUpdated.php

namespace App\Entity\NoMap\Time;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PreUpdate;

trait TimeUpdated
{
    #[Column(name: 'updated', type: 'datetimetz', nullable: true)]
    private ?\DateTimeInterface $updated = null;

    // Retrieve updated time
    public function getUpdated()
    {
        return $this->updated;
    }

    #[PreUpdate]
    public function setUpdated()
    {
        $this->updated = new \DateTime('now');

        return $this;
    }
}
