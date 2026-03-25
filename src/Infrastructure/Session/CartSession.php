<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Session;

use Rore\Application\Cart\CartSessionInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Support\Castable;

final class CartSession implements CartSessionInterface
{
    use Castable;
    private const KEY = 'rore_cart';

    public function __construct(
        private readonly SessionStorageInterface $session,
    ) {}

    // --- Dates -------------------------------------------------------------------

    public function hasDates(): bool
    {
        $cart = $this->getCart();
        return isset($cart['start_date'], $cart['end_date']);
    }

    public function getStartDate(): ?string
    {
        $cart = $this->getCart();
        $v = $cart['start_date'] ?? null;
        return is_string($v) ? $v : null;
    }

    public function getEndDate(): ?string
    {
        $cart = $this->getCart();
        $v = $cart['end_date'] ?? null;
        return is_string($v) ? $v : null;
    }

    /**
     * Définit les dates. Si des items existent déjà et que les dates changent,
     * le panier est vidé.
     */
    public function setDates(string $startDate, string $endDate): void
    {
        $currentStart = $this->getStartDate();
        $currentEnd   = $this->getEndDate();

        if (
            $currentStart !== null
            && ($currentStart !== $startDate || $currentEnd !== $endDate)
            && (!empty($this->getItems()) || !empty($this->getPacks()))
        ) {
            $this->setCart([]);
        }

        $cart = $this->getCart();
        $cart['start_date'] = $startDate;
        $cart['end_date']   = $endDate;
        if (!isset($cart['items']) || !is_array($cart['items'])) {
            $cart['items'] = [];
        }
        $this->setCart($cart);
    }

    // --- Items -------------------------------------------------------------------

    /** @return array<int, int>  [productId => quantity] */
    public function getItems(): array
    {
        $cart  = $this->getCart();
        $items = $cart['items'] ?? [];
        return is_array($items) ? $items : [];
    }

    public function addItem(int $productId, int $quantity): void
    {
        $items = $this->getItems();
        $items[$productId] = ($items[$productId] ?? 0) + $quantity;
        $cart = $this->getCart();
        $cart['items'] = $items;
        $this->setCart($cart);
    }

    public function removeItem(int $productId): void
    {
        $items = $this->getItems();
        unset($items[$productId]);
        $cart = $this->getCart();
        $cart['items'] = $items;
        $this->setCart($cart);
    }

    public function isEmpty(): bool
    {
        return empty($this->getItems()) && empty($this->getPacks());
    }

    public function getItemCount(): int
    {
        return array_sum($this->getItems());
    }

    // --- Packs -------------------------------------------------------------------

    /** @return array<int, int>  [packId => 1] */
    public function getPacks(): array
    {
        $cart  = $this->getCart();
        $packs = $cart['packs'] ?? [];
        return is_array($packs) ? $packs : [];
    }

    public function addPack(int $packId): void
    {
        $packs = $this->getPacks();
        if (!isset($packs[$packId])) {
            $packs[$packId] = ['selections' => []];
        }
        $cart = $this->getCart();
        $cart['packs'] = $packs;
        $this->setCart($cart);
    }

    public function removePack(int $packId): void
    {
        $packs = $this->getPacks();
        unset($packs[$packId]);
        $cart = $this->getCart();
        $cart['packs'] = $packs;
        $this->setCart($cart);
    }

    /** @return array<int, int>  [slotItemId => productId] */
    public function getPackSelections(int $packId): array
    {
        $packs      = $this->getPacks();
        $selections = $packs[$packId]['selections'] ?? [];
        return is_array($selections) ? $selections : [];
    }

    public function setPackSelection(int $packId, int $slotItemId, int $productId): void
    {
        $packs = $this->getPacks();
        if (!isset($packs[$packId])) {
            $packs[$packId] = ['selections' => []];
        }
        $packs[$packId]['selections'][$slotItemId] = $productId;
        $cart = $this->getCart();
        $cart['packs'] = $packs;
        $this->setCart($cart);
    }

    public function removePackSelection(int $packId, int $slotItemId): void
    {
        $packs = $this->getPacks();
        unset($packs[$packId]['selections'][$slotItemId]);
        $cart = $this->getCart();
        $cart['packs'] = $packs;
        $this->setCart($cart);
    }

    // --- Reset -------------------------------------------------------------------

    public function clear(): void
    {
        $this->setCart([]);
    }

    // --- Private -----------------------------------------------------------------

    /** @return array<string, mixed> */
    private function getCart(): array
    {
        $cart = $this->session->get(self::KEY, []);
        return is_array($cart) ? $cart : [];
    }

    /** @param array<string, mixed> $cart */
    private function setCart(array $cart): void
    {
        $this->session->set(self::KEY, $cart);
    }
}
