<?php

// src/Entity/User/Log/LogoutReport.php

namespace App\Entity\User\Log;

use App\Entity\NoMap\Time\TimeCreated;
use App\Repository\User\Log\LogoutReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LogoutReportRepository::class)]
class LogoutReport
{
    // Time: $created
    use TimeCreated;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'lgo_id', type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: LoginReport::class, inversedBy: 'logoutReport')]
    #[ORM\JoinColumn(name: 'fk_lgi_id', referencedColumnName: 'lgi_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: LoginReport::class)]
    #[Assert\Valid]
    private ?LoginReport $loginReport = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLoginReport(): ?LoginReport
    {
        return $this->loginReport;
    }

    public function setLoginReport(LoginReport $loginReport): self
    {
        $this->loginReport = $loginReport;

        return $this;
    }
}
