<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Entity;

class Pack
{
    /** @var PackItem[] */
    private array $items = [];

    public function __construct(
        private ?int                $id,
        private string              $name,
        private string              $slug,
        private ?string             $description,
        private float               $pricePerDay,
        private bool                $isActive,
        private \DateTimeImmutable  $createdAt,
        private \DateTimeImmutable  $updatedAt,
    ) {}

    public function getId(): ?int                      { return $this->id; }
    public function getName(): string                  { return $this->name; }
    public function getSlug(): string                  { return $this->slug; }
    public function getDescription(): ?string          { return $this->description; }
    public function getPricePerDay(): float            { return $this->pricePerDay; }
    public function isActive(): bool                   { return $this->isActive; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    /** @return PackItem[] */
    public function getItems(): array                  { return $this->items; }

    public function setName(string $name): void        { $this->name = $name; }
    public function setSlug(string $slug): void        { $this->slug = $slug; }
    public function setDescription(?string $d): void   { $this->description = $d; }
    public function setPricePerDay(float $p): void     { $this->pricePerDay = $p; }
    public function setIsActive(bool $active): void    { $this->isActive = $active; }
    public function toggle(): void                     { $this->isActive = !$this->isActive; }
    public function setUpdatedAt(\DateTimeImmutable $dt): void { $this->updatedAt = $dt; }

    /** @param PackItem[] $items */
    public function setItems(array $items): void       { $this->items = $items; }
}
