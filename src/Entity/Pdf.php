<?php

namespace App\Entity;

use App\Repository\PdfRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PdfRepository::class)]
class Pdf
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $mensuelDetails = null;

    #[ORM\Column]
    private ?bool $names = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $EntiteName = null;

    #[ORM\ManyToOne(inversedBy: 'pdf')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isMensuelDetails(): ?bool
    {
        return $this->mensuelDetails;
    }

    public function setMensuelDetails(bool $mensuelDetails): static
    {
        $this->mensuelDetails = $mensuelDetails;

        return $this;
    }

    public function isNames(): ?bool
    {
        return $this->names;
    }

    public function setNames(bool $names): static
    {
        $this->names = $names;

        return $this;
    }

    public function getEntiteName(): ?string
    {
        return $this->EntiteName;
    }

    public function setEntiteName(?string $EntiteName): static
    {
        $this->EntiteName = $EntiteName;

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
