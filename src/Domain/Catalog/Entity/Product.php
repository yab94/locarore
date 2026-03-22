<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Entity;

class Product
{
    /** @var ProductPhoto[] */
    private array $photos = [];

    /** @var int[] IDs de toutes les catégories (primaire incluse) */
    private array $categoryIds = [];

    public function __construct(
        private ?int                $id,
        private int                 $categoryId,   // catégorie principale (URL, breadcrumb)
        private string              $name,
        private string              $slug,
        private ?string             $description,
        private int                 $stock,
        private float               $pricePerDay,
        private bool                $isActive,
        private \DateTimeImmutable  $createdAt,
        private \DateTimeImmutable  $updatedAt,
    ) {}

    public function getId(): ?int                      { return $this->id; }
    public function getCategoryId(): int               { return $this->categoryId; }
    public function getName(): string                  { return $this->name; }
    public function getSlug(): string                  { return $this->slug; }
    public function getDescription(): ?string          { return $this->description; }
    public function getStock(): int                    { return $this->stock; }
    public function getPricePerDay(): float            { return $this->pricePerDay; }
    public function isActive(): bool                   { return $this->isActive; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    /** @return int[] */
    public function getCategoryIds(): array            { return $this->categoryIds ?: [$this->categoryId]; }
    /** @return ProductPhoto[] */
    public function getPhotos(): array                 { return $this->photos; }
    public function getMainPhoto(): ?ProductPhoto      { return $this->photos[0] ?? null; }

    public function setCategoryId(int $id): void       { $this->categoryId = $id; }
    public function setName(string $name): void        { $this->name = $name; }
    public function setSlug(string $slug): void        { $this->slug = $slug; }
    public function setDescription(?string $d): void   { $this->description = $d; }
    public function setStock(int $stock): void         { $this->stock = $stock; }
    public function setPricePerDay(float $p): void     { $this->pricePerDay = $p; }
    public function setIsActive(bool $active): void    { $this->isActive = $active; }
    public function toggle(): void                     { $this->isActive = !$this->isActive; }
    public function setUpdatedAt(\DateTimeImmutable $dt): void { $this->updatedAt = $dt; }

    /** @param ProductPhoto[] $photos */
    public function setPhotos(array $photos): void     { $this->photos = $photos; }
    /** @param int[] $ids */
    public function setCategoryIds(array $ids): void   { $this->categoryIds = $ids; }
}
