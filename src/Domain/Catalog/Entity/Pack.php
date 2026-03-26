<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Entity;

use Rore\Framework\Support\Castable;

class Pack implements PricableInterface
{
    use Castable;
    /** @var PackItem[] */
    private array $items = [];

    public function __construct(
        private ?int                $id,
        private string              $name,
        private string              $slug,
        private ?string             $description,
        private ?string             $descriptionShort,
        private float               $pricePerDay,
        private float               $priceExtraWeekend,
        private float               $priceExtraWeekday,
        private bool                $isActive,
        private \DateTimeImmutable  $createdAt,
        private \DateTimeImmutable  $updatedAt,
    ) {}

    public function getId(): ?int                      { return $this->id; }
    public function getName(): string                  { return $this->name; }
    public function getSlug(): string                  { return $this->slug; }
    public function getDescription(): ?string          { return $this->description; }
    public function getDescriptionShort(): ?string      { return $this->descriptionShort; }
    public function getPricePerDay(): float            { return $this->pricePerDay; }
    public function getPriceExtraWeekend(): float      { return $this->priceExtraWeekend; }
    public function getPriceExtraWeekday(): float      { return $this->priceExtraWeekday; }
    public function isActive(): bool                   { return $this->isActive; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    /** @return PackItem[] */
    public function getItems(): array                  { return $this->items; }

    public function setName(string $name): void        { $this->name = $name; }
    public function setSlug(string $slug): void        { $this->slug = $slug; }
    public function setDescription(?string $d): void   { $this->description = $d; }
    public function setDescriptionShort(?string $d): void { $this->descriptionShort = $d; }
    public function setPricePerDay(float $p): void     { $this->pricePerDay = $p; }
    public function setPriceExtraWeekend(float $p): void { $this->priceExtraWeekend = $p; }
    public function setPriceExtraWeekday(float $p): void { $this->priceExtraWeekday = $p; }
    public function setIsActive(bool $active): void    { $this->isActive = $active; }
    public function toggle(): void                     { $this->isActive = !$this->isActive; }
    public function setUpdatedAt(\DateTimeImmutable $dt): void { $this->updatedAt = $dt; }

    /** @param PackItem[] $items */
    public function setItems(array $items): void       { $this->items = $items; }

    /**
     * Retourne l'ID du produit "principal" du pack :
     * celui dont priceBase × quantity est le plus élevé.
     * Sert à déterminer la catégorie canonique et la photo principale.
     *
     * @param array<int, Product> $productsById  [id => Product]
     */
    public function getMainProductId(array $productsById): ?int
    {
        $best      = null;
        $bestScore = -1.0;

        foreach ($this->items as $item) {
            // Les slots catégorie n'ont pas de produit fixe → ignorer
            if (!$item->isFixed()) {
                continue;
            }
            $product = $productsById[$item->getProductId()] ?? null;
            if ($product === null) {
                continue;
            }
            $score = $product->getPriceBase() * $item->getQuantity();
            if ($score > $bestScore) {
                $bestScore = $score;
                $best      = $item->getProductId();
            }
        }

        return $best;
    }

    /** Implémente PricableInterface : alias de getPricePerDay() pour le service de calcul. */
    public function getBasePrice(): float { return $this->pricePerDay; }
}
