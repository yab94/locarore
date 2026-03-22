<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Entity;

class PackItem
{
    public function __construct(
        private ?int $id,
        private int  $packId,
        private int  $productId,
        private int  $quantity,
    ) {}

    public function getId(): ?int      { return $this->id; }
    public function getPackId(): int   { return $this->packId; }
    public function getProductId(): int { return $this->productId; }
    public function getQuantity(): int  { return $this->quantity; }

    public function setQuantity(int $q): void { $this->quantity = $q; }
}
