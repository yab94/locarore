<?php

declare(strict_types=1);

namespace Rore\Domain\Cart\ValueObject;

use Rore\Framework\Type\Castable;

/**
 * Snapshot read-only de l'état du panier, destiné aux vues.
 * Construit par GetCartStateUseCase — aucun effet de bord possible.
 */
final class CartState
{
    use Castable;

    /**
     * @param array<int, int>   $items  [productId => quantity]
     * @param array<int, mixed> $packs  [packId => ['selections' => [...]]]
     */
    public function __construct(
        private readonly bool    $hasDates,
        private readonly ?string $startDate,
        private readonly ?string $endDate,
        private readonly bool    $isEmpty,
        private readonly int     $itemCount,
        private readonly array   $items,
        private readonly array   $packs,
    ) {}

    public function hasDates(): bool    { return $this->hasDates;  }
    public function getStartDate(): ?string { return $this->startDate; }
    public function getEndDate(): ?string   { return $this->endDate;   }
    public function isEmpty(): bool     { return $this->isEmpty;   }
    public function getItemCount(): int { return $this->itemCount; }

    /** @return array<int, int> [productId => quantity] */
    public function getItems(): array   { return $this->items;     }

    /** @return array<int, mixed> [packId => ['selections' => [...]]] */
    public function getPacks(): array   { return $this->packs;     }

    /** @return array<int, int> [slotItemId => productId] */
    public function getPackSelections(int $packId): array
    {
        $selections = $this->packs[$packId]['selections'] ?? [];
        return is_array($selections) ? $selections : [];
    }
}
