<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\Controller\InvoiceIncrementationController;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new GetCollection(),
        new Post(),
        new Post(
            uriTemplate: '/invoices/{id}/increment',
            controller: InvoiceIncrementationController::class,
            openapi: new OpenApiOperation(
                summary: 'Incrémente une facture',
                description: 'Incrémente le chrono d\'une facture donnée',
            ),
            name: 'increment',
        ),
        new GetCollection(
            uriTemplate: '/customers/{customerId}/invoices',
            uriVariables: [
                'customerId' => new Link(
                    fromClass: Customer::class,
                    toProperty: 'customer',
                ),
            ],
            normalizationContext: ['groups' => ['invoices_subresource']],
        ),
    ],
    paginationEnabled: false,
    order: ['sentAt' => 'desc'],
    normalizationContext: ['groups' => ['invoices_read']],
)]
#[ApiFilter(OrderFilter::class, properties: ['amount', 'sentAt'])]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: 'Le montant de la facture est obligatoire !')]
    #[Assert\Type(type: 'numeric', message: 'Le montant de la facture doit être un numérique !')]
    private ?float $amount = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\NotNull(message: "La date d'envoi doit être renseignée")]
    private ?\DateTimeInterface $sentAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: 'Le statut de la facture est obligatoire')]
    #[Assert\Choice(choices: ['SENT', 'PAID', 'CANCELLED'], message: 'Le statut doit être SENT, PAID ou CANCELLED')]
    private ?string $status = null;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invoices_read'])]
    #[Assert\NotBlank(message: 'Le client de la facture doit être renseigné')]
    private ?Customer $customer = null;

    #[ORM\Column]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank(message: 'Il faut absolument un chrono pour la facture')]
    #[Assert\Type(type: 'integer', message: 'Le chrono doit être un nombre !')]
    private ?int $chrono = null;

    /**
     * Permet de récupérer le User à qui appartient finalement la facture
     */
    #[Groups(['invoices_read', 'invoices_subresource'])]
    public function getUser(): ?User
    {
        return $this->customer?->getUser();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeInterface $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getChrono(): ?int
    {
        return $this->chrono;
    }

    public function setChrono(int $chrono): self
    {
        $this->chrono = $chrono;

        return $this;
    }
}
