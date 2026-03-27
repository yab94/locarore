<?php

declare(strict_types=1);

namespace Rore\Cart\Port;

interface CartServiceInterface
{
    public function hasDates(): bool;
    public function getStartDate(): ?string;
    public function getEndDate(): ?string;
    public function setDates(string $startDate, string $endDate): void;

    /** @return array<int, int>  [productId => quantity] */
    public function getItems(): array;
    public function addItem(int $productId, int $quantity): void;
    public function removeItem(int $productId): void;
    public function isEmpty(): bool;
    public function getItemCount(): int;

    /** @return array<int, mixed>  [packId => [...]] */
    public function getPacks(): array;
    public function addPack(int $packId): void;
    public function removePack(int $packId): void;

    /** @return array<int, int>  [slotItemId => productId] */
    public function getPackSelections(int $packId): array;
    public function setPackSelection(int $packId, int $slotItemId, int $productId): void;
    public function removePackSelection(int $packId, int $slotItemId): void;

    public function clear(): void;
}
