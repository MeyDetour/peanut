<?php

namespace App\Entity;

use App\Repository\RecurringOperationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecurringOperationRepository::class)]
class RecurringOperation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $repetition = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $addingType = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $firstDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastDate = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $montant = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'Operations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $occurences = '';

    #[ORM\Column(nullable: true)]
    private ?bool $wantedToRemoveAll = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepetition(): ?string
    {
        return $this->repetition;
    }

    public function setRepetition(string $repetition): static
    {
        $this->repetition = $repetition;

        return $this;
    }

    public function getAddingType(): ?string
    {
        return $this->addingType;
    }

    public function setAddingType(string $addingType): static
    {
        $this->addingType = $addingType;

        return $this;
    }

    public function getFirstDate(): ?\DateTimeInterface
    {
        return $this->firstDate;
    }

    public function setFirstDate(\DateTimeInterface $firstDate): static
    {
        $this->firstDate = $firstDate;

        return $this;
    }

    public function getLastDate(): ?\DateTimeInterface
    {
        return $this->lastDate;
    }

    public function setLastDate(?\DateTimeInterface $lastDate): static
    {
        $this->lastDate = $lastDate;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getOccurences(): ?string
    {
        if(!$this->occurences){
            return  '';
    }
        return $this->occurences;
    }

    public function setOccurences(?string $occurences): static
    {
        $this->occurences = $occurences;

        return $this;
    }

    public function isWantedToRemoveAll(): ?bool
    {
        return $this->wantedToRemoveAll;
    }

    public function setWantedToRemoveAll(?bool $wantedToRemoveAll): static
    {
        $this->wantedToRemoveAll = $wantedToRemoveAll;

        return $this;
    }
}
