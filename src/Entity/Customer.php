<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['customers_read']],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'firstName' => 'partial',
    'lastName' => 'partial',
    'company' => 'partial',
    'email' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: ['firstName', 'lastName', 'company'])]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['customers_read', 'invoices_read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customers_read', 'invoices_read'])]
    #[Assert\NotBlank(message: 'Le prénom du customer est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le prénom doit faire entre 3 et 255 caractères',
        maxMessage: 'Le prénom doit faire entre 3 et 255 caractères',
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customers_read', 'invoices_read'])]
    #[Assert\NotBlank(message: 'Le nom de famille du customer est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom de famille doit faire entre 3 et 255 caractères',
        maxMessage: 'Le nom de famille doit faire entre 3 et 255 caractères',
    )]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customers_read', 'invoices_read'])]
    #[Assert\NotBlank(message: "L'adresse email du customer est obligatoire")]
    #[Assert\Email(message: "Le format de l'adresse email doit être valide")]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['customers_read', 'invoices_read'])]
    private ?string $company = null;

    /**
     * @var Collection<int, Invoice>
     */
    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'customer')]
    #[Groups(['customers_read'])]
    private Collection $invoices;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'customers')]
    #[Groups(['customers_read'])]
    #[Assert\NotBlank(message: "L'utilisateur est obligatoire")]
    private ?User $user = null;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
    }

    /**
     * Permet de récupérer le total des invoices
     */
    #[Groups(['customers_read'])]
    public function getTotalAmount(): float
    {
        return array_reduce($this->invoices->toArray(), function ($total, $invoice) {
            return $total + $invoice->getAmount();
        }, 0);
    }

    /**
     * Récupérer le montant total non payé (montant total hors factures payées ou annulées)
     */
    #[Groups(['customers_read'])]
    public function getUnpaidAmount(): float
    {
        return array_reduce($this->invoices->toArray(), function ($total, $invoice) {
            return $total + ($invoice->getStatus() === 'PAID' || $invoice->getStatus() === 'CANCELLED' ? 0 : $invoice->getAmount());
        }, 0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setCustomer($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getCustomer() === $this) {
                $invoice->setCustomer(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
