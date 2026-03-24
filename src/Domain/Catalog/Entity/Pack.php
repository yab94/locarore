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

    /**
     * Prix du pack pour un nombre de jours donné.
     */
    public function calculateTotal(int $nbDays): float
    {
        return $this->pricePerDay * max(1, $nbDays);
    }

    /**
     * Prix théorique des articles au détail (quantité × prix unitaire du produit
     * sur la même période). Permet d'afficher la « valeur » et l'économie réalisée.
     *
     * @param Product[] $products Tous les produits du pack (dans n'importe quel ordre)
     */
    public function calculateItemsTotal(
        array $products,
        \DateTimeImmutable|string $start,
        \DateTimeImmutable|string $end,
    ): float {
        $byId = [];
        foreach ($products as $product) {
            $byId[$product->getId()] = $product;
        }

        $total = 0.0;
        foreach ($this->items as $item) {
            $product = $byId[$item->getProductId()] ?? null;
            if ($product !== null) {
                $total += $item->getQuantity() * $product->calculatePrice($start, $end);
            }
        }

        return $total;
    }
}
