<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    private bool $isVerified = false;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'owner')]
    private Collection $categories;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $payment;

    /**
     * @var Collection<int, Pdf>
     */
    #[ORM\OneToMany(targetEntity: Pdf::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $pdf;

    /**
     * @var Collection<int, RecurringOperation>
     */
    #[ORM\OneToMany(targetEntity: RecurringOperation::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $Operations;

    /**
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $payments;

    /**
     * @var Collection<int, Budget>
     */
    #[ORM\OneToMany(targetEntity: Budget::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $budgets;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->payment = new ArrayCollection();
        $this->pdf = new ArrayCollection();
        $this->Operations = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->budgets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
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
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
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

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setOwner($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getOwner() === $this) {
                $category->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getPayment(): Collection
    {
        return $this->payment;
    }

    public function addPayment(Transaction $payment): static
    {
        if (!$this->payment->contains($payment)) {
            $this->payment->add($payment);
            $payment->setOwner($this);
        }

        return $this;
    }

    public function removePayment(Transaction $payment): static
    {
        if ($this->payment->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getOwner() === $this) {
                $payment->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Pdf>
     */
    public function getPdf(): Collection
    {
        return $this->pdf;
    }

    public function addPdf(Pdf $pdf): static
    {
        if (!$this->pdf->contains($pdf)) {
            $this->pdf->add($pdf);
            $pdf->setOwner($this);
        }

        return $this;
    }

    public function removePdf(Pdf $pdf): static
    {
        if ($this->pdf->removeElement($pdf)) {
            // set the owning side to null (unless already changed)
            if ($pdf->getOwner() === $this) {
                $pdf->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RecurringOperation>
     */
    public function getOperations(): Collection
    {
        return $this->Operations;
    }

    public function addOperation(RecurringOperation $operation): static
    {
        if (!$this->Operations->contains($operation)) {
            $this->Operations->add($operation);
            $operation->setOwner($this);
        }

        return $this;
    }

    public function removeOperation(RecurringOperation $operation): static
    {
        if ($this->Operations->removeElement($operation)) {
            // set the owning side to null (unless already changed)
            if ($operation->getOwner() === $this) {
                $operation->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    /**
     * @return Collection<int, Budget>
     */
    public function getBudgets(): Collection
    {
        return $this->budgets;
    }

    public function addBudget(Budget $budget): static
    {
        if (!$this->budgets->contains($budget)) {
            $this->budgets->add($budget);
            $budget->setOwner($this);
        }

        return $this;
    }

    public function removeBudget(Budget $budget): static
    {
        if ($this->budgets->removeElement($budget)) {
            // set the owning side to null (unless already changed)
            if ($budget->getOwner() === $this) {
                $budget->setOwner(null);
            }
        }

        return $this;
    }
}
