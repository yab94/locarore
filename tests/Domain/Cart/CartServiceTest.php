<?php

declare(strict_types=1);

use Rore\Cart\Adapter\CartService;
use Rore\Framework\Session\SessionInterface;

// ─── Stub SessionStorage in-memory ────────────────────────────────────────────

final class InMemorySession implements SessionInterface
{
    private array $data = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }
}

// ─── Tests ───────────────────────────────────────────────────────────────────

final class CartServiceTest
{
    private CartService $cart;
    private InMemorySession $session;

    public function setUp(): void
    {
        $this->session = new InMemorySession();
        $this->cart    = new CartService($this->session);
    }

    // --- État initial ---

    public function testInitialCartHasNoDates(): void
    {
        Assert::false($this->cart->hasDates());
        Assert::null($this->cart->getStartDate());
        Assert::null($this->cart->getEndDate());
    }

    public function testInitialCartIsEmpty(): void
    {
        Assert::true($this->cart->isEmpty());
        Assert::equals([], $this->cart->getItems());
        Assert::equals([], $this->cart->getPacks());
        Assert::equals(0, $this->cart->getItemCount());
    }

    // --- Dates ---

    public function testSetDatesStoresDates(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');

        Assert::true($this->cart->hasDates());
        Assert::equals('2026-04-01', $this->cart->getStartDate());
        Assert::equals('2026-04-07', $this->cart->getEndDate());
    }

    public function testSetDatesSameDatesKeepsItems(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addItem(1, 2);

        $this->cart->setDates('2026-04-01', '2026-04-07');

        Assert::equals([1 => 2], $this->cart->getItems());
    }

    public function testSetDatesChangedClearsItemsWhenItemsExist(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addItem(1, 2);

        $this->cart->setDates('2026-05-01', '2026-05-07');

        Assert::equals([], $this->cart->getItems());
        Assert::equals('2026-05-01', $this->cart->getStartDate());
    }

    public function testSetDatesChangedClearsPacksWhenPacksExist(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addPack(10);

        $this->cart->setDates('2026-05-01', '2026-05-07');

        Assert::equals([], $this->cart->getPacks());
    }

    public function testSetDatesFirstTimeDoesNotClear(): void
    {
        // Premier appel : pas de dates existantes → pas de vidage
        $this->cart->setDates('2026-04-01', '2026-04-07');

        Assert::true($this->cart->hasDates());
    }

    // --- Items ---

    public function testAddItemIncreasesQuantity(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addItem(1, 3);

        Assert::equals([1 => 3], $this->cart->getItems());
        Assert::equals(3, $this->cart->getItemCount());
    }

    public function testAddItemAccumulatesQuantity(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addItem(1, 2);
        $this->cart->addItem(1, 3);

        Assert::equals([1 => 5], $this->cart->getItems());
    }

    public function testAddMultipleItems(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addItem(1, 1);
        $this->cart->addItem(2, 4);

        Assert::equals([1 => 1, 2 => 4], $this->cart->getItems());
        Assert::equals(5, $this->cart->getItemCount());
    }

    public function testRemoveItemRemovesProduct(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addItem(1, 2);
        $this->cart->addItem(2, 1);
        $this->cart->removeItem(1);

        Assert::equals([2 => 1], $this->cart->getItems());
    }

    public function testRemoveItemNonExistentDoesNotThrow(): void
    {
        $this->cart->removeItem(99);
        Assert::equals([], $this->cart->getItems());
    }

    public function testIsEmptyFalseWhenItemsExist(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addItem(1, 1);

        Assert::false($this->cart->isEmpty());
    }

    // --- Packs ---

    public function testAddPackStoresPack(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addPack(10);

        $packs = $this->cart->getPacks();
        Assert::true(isset($packs[10]));
        Assert::equals([], $this->cart->getPackSelections(10));
    }

    public function testAddPackIdempotent(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addPack(10);
        $this->cart->setPackSelection(10, 1, 42);
        $this->cart->addPack(10); // ne doit pas écraser les sélections

        Assert::equals([1 => 42], $this->cart->getPackSelections(10));
    }

    public function testRemovePackRemovesPack(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addPack(10);
        $this->cart->removePack(10);

        Assert::equals([], $this->cart->getPacks());
    }

    public function testIsEmptyFalseWhenPacksExist(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addPack(10);

        Assert::false($this->cart->isEmpty());
    }

    // --- Sélections de pack ---

    public function testSetPackSelectionStoresSelection(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addPack(10);
        $this->cart->setPackSelection(10, 1, 42);

        Assert::equals([1 => 42], $this->cart->getPackSelections(10));
    }

    public function testSetPackSelectionCreatesPackIfMissing(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->setPackSelection(10, 1, 42); // sans addPack préalable

        Assert::equals([1 => 42], $this->cart->getPackSelections(10));
    }

    public function testSetMultiplePackSelections(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addPack(10);
        $this->cart->setPackSelection(10, 1, 42);
        $this->cart->setPackSelection(10, 2, 99);

        Assert::equals([1 => 42, 2 => 99], $this->cart->getPackSelections(10));
    }

    public function testRemovePackSelectionRemovesSlot(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addPack(10);
        $this->cart->setPackSelection(10, 1, 42);
        $this->cart->setPackSelection(10, 2, 99);
        $this->cart->removePackSelection(10, 1);

        Assert::equals([2 => 99], $this->cart->getPackSelections(10));
    }

    public function testGetPackSelectionsUnknownPackReturnsEmpty(): void
    {
        Assert::equals([], $this->cart->getPackSelections(999));
    }

    // --- Clear ---

    public function testClearEmptiesEverything(): void
    {
        $this->cart->setDates('2026-04-01', '2026-04-07');
        $this->cart->addItem(1, 2);
        $this->cart->addPack(10);

        $this->cart->clear();

        Assert::true($this->cart->isEmpty());
        Assert::equals([], $this->cart->getItems());
        Assert::equals([], $this->cart->getPacks());
        Assert::false($this->cart->hasDates());
    }
}
