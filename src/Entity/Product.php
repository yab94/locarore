<?php

declare(strict_types=1);

namespace Rore\Entity;

use RRB\Type\Castable;

class Product implements PricableInterface
{
    use Castable;
    /** @var ProductPhoto[] */
    private array $photos = [];

    /** @var int[] IDs de toutes les catégories (primaire incluse) */
    private array $categoryIds = [];

    /** @var Tag[] */
    private array $tags = [];

    public function __construct(
        private ?int                $id,
        private int                 $categoryId,   // catégorie principale (URL, breadcrumb)
        private string              $name,
        private string              $slug,
        private ?string             $description,
        private ?string             $descriptionShort,
        private int                 $stock,
        private int                 $stockOnDemand,   // unités fabricables à la commande
        private float               $fabricationTimeDays, // temps de fabrication unitaire (jours)
        private float               $priceBase,       // forfait de base (couvre 1-2 jours)
        private float               $priceExtraWeekend, // supplément/jour si WE (sam+dim, ≤4j)
        private float               $priceExtraWeekday, // supplément/jour sinon
        private bool                $isActive,
        private \DateTimeImmutable  $createdAt,
        private \DateTimeImmutable  $updatedAt,
    ) {}

    public function getId(): ?int                      { return $this->id; }
    public function getCategoryId(): int               { return $this->categoryId; }
    public function getName(): string                  { return $this->name; }
    public function getSlug(): string                  { return $this->slug; }
    public function getDescription(): ?string          { return $this->description; }
    public function getDescriptionShort(): ?string      { return $this->descriptionShort; }
    public function getStock(): int                    { return $this->stock; }
    public function getStockOnDemand(): int            { return $this->stockOnDemand; }
    public function getFabricationTimeDays(): float    { return $this->fabricationTimeDays; }
    /** Stock physique + fabricable = capacité totale à disposer */
    public function getTotalStock(): int               { return $this->stock + $this->stockOnDemand; }
    public function getPriceBase(): float              { return $this->priceBase; }
    public function getPriceExtraWeekend(): float        { return $this->priceExtraWeekend; }
    public function getPriceExtraWeekday(): float      { return $this->priceExtraWeekday; }
    public function isActive(): bool                   { return $this->isActive; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    /** Implémente PricableInterface : alias de getPriceBase() pour le service de calcul. */
    public function getBasePrice(): float { return $this->priceBase; }

    /** @return int[] */
    public function getCategoryIds(): array            { return $this->categoryIds ?: [$this->categoryId]; }
    /** @return ProductPhoto[] */
    public function getPhotos(): array                 { return $this->photos; }
    public function getMainPhoto(): ?ProductPhoto      { return $this->photos[0] ?? null; }
    /** @return Tag[] */
    public function getTags(): array                   { return $this->tags; }

    public function setCategoryId(int $id): void       { $this->categoryId = $id; }
    public function setName(string $name): void        { $this->name = $name; }
    public function setSlug(string $slug): void        { $this->slug = $slug; }
    public function setDescription(?string $d): void   { $this->description = $d; }
    public function setDescriptionShort(?string $d): void { $this->descriptionShort = $d; }
    public function setStock(int $stock): void         { $this->stock = $stock; }
    public function setStockOnDemand(int $s): void     { $this->stockOnDemand = $s; }
    public function setFabricationTimeDays(float $d): void  { $this->fabricationTimeDays = max(0.0, $d); }
    public function setPriceBase(float $p): void       { $this->priceBase = $p; }
    public function setPriceExtraWeekend(float $p): void  { $this->priceExtraWeekend = $p; }
    public function setPriceExtraWeekday(float $p): void { $this->priceExtraWeekday = $p; }
    public function setIsActive(bool $active): void    { $this->isActive = $active; }
    public function toggle(): void                     { $this->isActive = !$this->isActive; }
    public function setUpdatedAt(\DateTimeImmutable $dt): void { $this->updatedAt = $dt; }

    /** @param ProductPhoto[] $photos */
    public function setPhotos(array $photos): void     { $this->photos = $photos; }
    /** @param int[] $ids */
    public function setCategoryIds(array $ids): void   { $this->categoryIds = $ids; }
    /** @param Tag[] $tags */
    public function setTags(array $tags): void         { $this->tags = $tags; }
}
