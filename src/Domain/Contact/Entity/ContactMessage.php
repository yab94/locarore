<?php

declare(strict_types=1);

namespace Rore\Domain\Contact\Entity;

use Rore\Framework\Type\Castable;

class ContactMessage
{
    use Castable;
    public function __construct(
        private ?int               $id,
        private string             $firstName,
        private string             $lastName,
        private string             $email,
        private ?string            $phone,
        private string             $subject,
        private string             $content,
        private bool               $isRead,
        private \DateTimeImmutable $createdAt,
    ) {}

    public function getId(): ?int                      { return $this->id; }
    public function getFirstName(): string             { return $this->firstName; }
    public function getLastName(): string              { return $this->lastName; }
    public function getFullName(): string              { return $this->firstName . ' ' . $this->lastName; }
    public function getEmail(): string                 { return $this->email; }
    public function getPhone(): ?string                { return $this->phone; }
    public function getSubject(): string               { return $this->subject; }
    public function getContent(): string               { return $this->content; }
    public function isRead(): bool                     { return $this->isRead; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function markRead(): void   { $this->isRead = true; }
    public function markUnread(): void { $this->isRead = false; }
}
