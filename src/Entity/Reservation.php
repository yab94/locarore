<?php

declare(strict_types=1);

namespace Rore\Entity;

use RRB\Type\Castable;

class Reservation
{
    use Castable;
    /** @var ReservationItem[] */
    private array $items = [];

    public function __construct(
        private ?int               $id,
        private string             $customerName,
        private string             $customerEmail,
        private ?string            $customerPhone,
        private ?string            $customerAddress,
        private ?string            $eventAddress,
        private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate,
        private string             $status,
        private ?string            $notes,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public function getId(): ?int                      { return $this->id; }
    public function getCustomerName(): string          { return $this->customerName; }
    public function getCustomerEmail(): string         { return $this->customerEmail; }
    public function getCustomerPhone(): ?string        { return $this->customerPhone; }
    public function getCustomerAddress(): ?string      { return $this->customerAddress; }
    public function getEventAddress(): ?string         { return $this->eventAddress; }
    public function getStartDate(): \DateTimeImmutable { return $this->startDate; }
    public function getEndDate(): \DateTimeImmutable   { return $this->endDate; }
    public function getStatus(): string                { return $this->status; }
    public function getNotes(): ?string                { return $this->notes; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    /** @return ReservationItem[] */
    public function getItems(): array                  { return $this->items; }

    public function setStatus(string $status): void                   { $this->status = $status; }
    public function setUpdatedAt(\DateTimeImmutable $dt): void        { $this->updatedAt = $dt; }

    /** @param ReservationItem[] $items */
    public function setItems(array $items): void       { $this->items = $items; }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isQuoted(): bool    { return $this->status === 'quoted'; }
    public function isConfirmed(): bool { return $this->status === 'confirmed'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
}
