<?php

declare(strict_types=1);

namespace Rore\Application\Cart;

interface CartSessionInterface
{
    // --- Dates ---

    public function hasDates(): bool;
    public function getStartDate(): ?string;
    public function getEndDate(): ?string;
    public function setDates(string $startDate, string $endDate): void;

    // --- Items ---

    /** @return array<int, int>  [productId => quantity] */
    public function getItems(): array;
    public function addItem(int $productId, int $quantity): void;
    public function removeItem(int $productId): void;
    public function isEmpty(): bool;
    public function getItemCount(): int;

    // --- Packs ---

    /** @return array<int, int>  [packId => 1] */
    public function getPacks(): array;
    public function addPack(int $packId): void;
    public function removePack(int $packId): void;

    /** @return array<int, int>  [slotItemId => productId] */
    public function getPackSelections(int $packId): array;
    public function setPackSelection(int $packId, int $slotItemId, int $productId): void;
    public function removePackSelection(int $packId, int $slotItemId): void;

    // --- Reset ---

    public function clear(): void;
}
