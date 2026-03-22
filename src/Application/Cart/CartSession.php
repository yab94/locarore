<?php

declare(strict_types=1);

namespace Rore\Application\Cart;

/**
 * Gestion du panier en session.
 *
 * Structure session :
 * $_SESSION['rore_cart'] = [
 *   'start_date' => 'YYYY-MM-DD',
 *   'end_date'   => 'YYYY-MM-DD',
 *   'items'      => [ productId => quantity, ... ],
 * ]
 */
class CartSession
{
    private const KEY = 'rore_cart';

    private static ?self $instance = null;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // --- Dates -----------------------------------------------------------

    public function hasDates(): bool
    {
        return isset($_SESSION[self::KEY]['start_date'], $_SESSION[self::KEY]['end_date']);
    }

    public function getStartDate(): ?string
    {
        return $_SESSION[self::KEY]['start_date'] ?? null;
    }

    public function getEndDate(): ?string
    {
        return $_SESSION[self::KEY]['end_date'] ?? null;
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
            && !empty($this->getItems())
        ) {
            // Les dates changent avec un panier non vide → on vide le panier
            $_SESSION[self::KEY] = [];
        }

        $_SESSION[self::KEY]['start_date'] = $startDate;
        $_SESSION[self::KEY]['end_date']   = $endDate;

        if (!isset($_SESSION[self::KEY]['items'])) {
            $_SESSION[self::KEY]['items'] = [];
        }
    }

    // --- Items -----------------------------------------------------------

    /** @return array<int, int>  [productId => quantity] */
    public function getItems(): array
    {
        return $_SESSION[self::KEY]['items'] ?? [];
    }

    public function addItem(int $productId, int $quantity): void
    {
        $items = $this->getItems();
        $items[$productId] = ($items[$productId] ?? 0) + $quantity;
        $_SESSION[self::KEY]['items'] = $items;
    }

    public function removeItem(int $productId): void
    {
        $items = $this->getItems();
        unset($items[$productId]);
        $_SESSION[self::KEY]['items'] = $items;
    }

    public function isEmpty(): bool
    {
        return empty($this->getItems());
    }

    public function getItemCount(): int
    {
        return array_sum($this->getItems());
    }

    // --- Reset -----------------------------------------------------------

    public function clear(): void
    {
        $_SESSION[self::KEY] = [];
    }
}
