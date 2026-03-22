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
        private float               $priceBase,       // forfait de base (couvre 1-2 jours)
        private float               $priceExtraWe,    // supplément/jour si WE (sam+dim, ≤4j)
        private float               $priceExtraSem,   // supplément/jour sinon
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
    public function getPriceBase(): float              { return $this->priceBase; }
    public function getPriceExtraWe(): float           { return $this->priceExtraWe; }
    public function getPriceExtraSem(): float          { return $this->priceExtraSem; }
    public function isActive(): bool                   { return $this->isActive; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    /**
     * Calcule le prix total pour une période.
     * - Forfait base couvre les 2 premiers jours.
     * - WE (0€/j supp) : contient sam+dim ET ≤ 4 jours.
     * - SEM (priceExtraSem €/j supp) : sinon.
     */
    public function calculatePrice(\DateTimeImmutable|string $start, \DateTimeImmutable|string $end): float
    {
        if (is_string($start)) $start = new \DateTimeImmutable($start);
        if (is_string($end))   $end   = new \DateTimeImmutable($end);
        $days = (int) $start->diff($end)->days + 1;
        $days = max(1, $days);

        $hasSat = false;
        $hasSun = false;
        $cur    = $start;
        while ($cur <= $end) {
            $dow = (int) $cur->format('N'); // 1=Lun … 7=Dim
            if ($dow === 6) $hasSat = true;
            if ($dow === 7) $hasSun = true;
            $cur = $cur->modify('+1 day');
        }

        $isWeekend  = $hasSat && $hasSun && $days <= 4;
        $extraRate  = $isWeekend ? $this->priceExtraWe : $this->priceExtraSem;
        $extraDays  = max(0, $days - 2);

        return $this->priceBase + ($extraDays * $extraRate);
    }

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
    public function setPriceBase(float $p): void       { $this->priceBase = $p; }
    public function setPriceExtraWe(float $p): void    { $this->priceExtraWe = $p; }
    public function setPriceExtraSem(float $p): void   { $this->priceExtraSem = $p; }
    public function setIsActive(bool $active): void    { $this->isActive = $active; }
    public function toggle(): void                     { $this->isActive = !$this->isActive; }
    public function setUpdatedAt(\DateTimeImmutable $dt): void { $this->updatedAt = $dt; }

    /** @param ProductPhoto[] $photos */
    public function setPhotos(array $photos): void     { $this->photos = $photos; }
    /** @param int[] $ids */
    public function setCategoryIds(array $ids): void   { $this->categoryIds = $ids; }
}
