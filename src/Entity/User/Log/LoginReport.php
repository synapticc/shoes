<?php

// src/Entity/User/Log/LoginReport.php

namespace App\Entity\User\Log;

use App\Entity\NoMap\Time\TimeCreated;
use App\Entity\User\Session\Session;
use App\Repository\User\Log\LoginReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LoginReportRepository::class)]
class LoginReport
{
    // Time: $created
    use TimeCreated;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'lgi_id', type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: LogoutReport::class, mappedBy: 'loginReport', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?LogoutReport $logoutReport = null;

    #[ORM\OneToOne(targetEntity: Session::class, inversedBy: 'loginReport', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'fk_sess_id', referencedColumnName: 'sess_id', onDelete: 'CASCADE', nullable: false)]
    #[Assert\Type(type: Session::class)]
    #[Assert\Valid]
    private ?Session $session = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogoutReport(): ?LogoutReport
    {
        return $this->logoutReport;
    }

    public function setLogoutReport(LogoutReport $logoutReport): self
    {
        // set the owning side of the relation if necessary
        if ($logoutReport->getLoginReport() !== $this) {
            $logoutReport->setLoginReport($this);
        }

        $this->logoutReport = $logoutReport;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(Session $session): self
    {
        $this->session = $session;

        return $this;
    }
}
