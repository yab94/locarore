<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Entity;

class Category
{
    /** @var Category[] */
    private array $children = [];

    public function __construct(
        private ?int                $id,
        private ?int                $parentId,
        private string              $name,
        private string              $slug,
        private ?string             $descriptionShort,
        private ?string             $description,
        private bool                $isActive,
        private \DateTimeImmutable  $createdAt,
        private \DateTimeImmutable  $updatedAt,
    ) {}

    public function getId(): ?int                      { return $this->id; }
    public function getParentId(): ?int                { return $this->parentId; }
    public function getName(): string                  { return $this->name; }
    public function getSlug(): string                  { return $this->slug; }
    public function getDescriptionShort(): ?string     { return $this->descriptionShort; }
    public function getDescription(): ?string          { return $this->description; }
    public function isActive(): bool                   { return $this->isActive; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    /** @return Category[] */
    public function getChildren(): array               { return $this->children; }
    public function hasChildren(): bool                { return !empty($this->children); }

    public function setParentId(?int $id): void        { $this->parentId = $id; }
    public function setName(string $name): void        { $this->name = $name; }
    public function setSlug(string $slug): void        { $this->slug = $slug; }
    public function setDescriptionShort(?string $d): void { $this->descriptionShort = $d; }
    public function setDescription(?string $d): void   { $this->description = $d; }
    public function setIsActive(bool $active): void    { $this->isActive = $active; }
    public function toggle(): void                     { $this->isActive = !$this->isActive; }
    public function setUpdatedAt(\DateTimeImmutable $dt): void { $this->updatedAt = $dt; }

    /** @param Category[] $children */
    public function setChildren(array $children): void { $this->children = $children; }
}
