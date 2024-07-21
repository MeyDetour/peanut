<?php

namespace App\Entity;

use App\Repository\BudgetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
class Budget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2)]
    private ?string $montant = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $rappel1 = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $montant1 = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    private ?string $montant2 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rappel2 = null;

    #[ORM\ManyToOne(inversedBy: 'budgets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getRappel1(): ?string
    {
        return $this->rappel1;
    }

    public function setRappel1(string $rappel1): static
    {
        $this->rappel1 = $rappel1;

        return $this;
    }

    public function getMontant1(): ?string
    {
        return $this->montant1;
    }

    public function setMontant1(string $montant1): static
    {
        $this->montant1 = $montant1;

        return $this;
    }

    public function getMontant2(): ?string
    {
        return $this->montant2;
    }

    public function setMontant2(?string $montant2): static
    {
        $this->montant2 = $montant2;

        return $this;
    }

    public function getRappel2(): ?string
    {
        return $this->rappel2;
    }

    public function setRappel2(?string $rappel2): static
    {
        $this->rappel2 = $rappel2;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
