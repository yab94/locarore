<?php

declare(strict_types=1);

namespace Rore\Catalog\Entity;

class PackItem
{
    public function __construct(
        private ?int $id,
        private int  $packId,
        private ?int $productId,
        private ?int $categoryId,
        private int  $quantity,
    ) {}

    public function getId(): ?int       { return $this->id; }
    public function getPackId(): int    { return $this->packId; }
    public function getProductId(): ?int { return $this->productId; }
    public function getCategoryId(): ?int { return $this->categoryId; }
    public function getQuantity(): int  { return $this->quantity; }

    /** Produit fixe (non-sélectionnable) */
    public function isFixed(): bool { return $this->productId !== null; }

    /** Slot catégorie : l'utilisateur choisit un produit parmi la catégorie */
    public function isSlot(): bool { return $this->categoryId !== null; }

    public function setQuantity(int $q): void { $this->quantity = $q; }
}
