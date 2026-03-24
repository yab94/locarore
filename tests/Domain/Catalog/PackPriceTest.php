<?php

declare(strict_types=1);

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\PackItem;
use Rore\Domain\Catalog\Entity\Product;

/**
 * Pack composé de 4× Produit A (20 €/base) + 6× Produit B (30 €/base).
 * pricePerDay du pack = 50 €.
 *
 * Total théorique au détail : 4×20 + 6×30 = 80 + 180 = 260 €
 * Prix pack (1 jour)        : 50 €
 * Prix pack (5 jours)       : 250 €
 */
class PackPriceTest
{
    private const DATE_1J_START = '2025-06-02'; // lundi
    private const DATE_1J_END   = '2025-06-02'; // même jour → 1 jour
    private const DATE_2J_END   = '2025-06-03'; // mardi   → 2 jours

    // ── Helpers ──────────────────────────────────────────────────────────

    private function makeProductA(): Product
    {
        return new Product(
            id:                  1,
            categoryId:          1,
            name:                'Produit A',
            slug:                'produit-a',
            description:         null,
            stock:               10,
            stockOnDemand:       0,
            fabricationTimeDays: 0.0,
            priceBase:           20.0,
            priceExtraWeekend:   0.0,
            priceExtraWeekday:   0.0,
            isActive:            true,
            createdAt:           new \DateTimeImmutable('2025-01-01'),
            updatedAt:           new \DateTimeImmutable('2025-01-01'),
        );
    }

    private function makeProductB(): Product
    {
        return new Product(
            id:                  2,
            categoryId:          1,
            name:                'Produit B',
            slug:                'produit-b',
            description:         null,
            stock:               10,
            stockOnDemand:       0,
            fabricationTimeDays: 0.0,
            priceBase:           30.0,
            priceExtraWeekend:   0.0,
            priceExtraWeekday:   0.0,
            isActive:            true,
            createdAt:           new \DateTimeImmutable('2025-01-01'),
            updatedAt:           new \DateTimeImmutable('2025-01-01'),
        );
    }

    private function makePack(): Pack
    {
        $pack = new Pack(
            id:          1,
            name:        'Pack Mariage',
            slug:        'pack-mariage',
            description: null,
            pricePerDay: 50.0,
            isActive:    true,
            createdAt:   new \DateTimeImmutable('2025-01-01'),
            updatedAt:   new \DateTimeImmutable('2025-01-01'),
        );

        $pack->setItems([
            new PackItem(id: null, packId: 1, productId: 1, quantity: 4),
            new PackItem(id: null, packId: 1, productId: 2, quantity: 6),
        ]);

        return $pack;
    }

    // ── Composition du pack ───────────────────────────────────────────────

    public function testPackHasTwoItemLines(): void
    {
        Assert::equals(2, count($this->makePack()->getItems()));
    }

    public function testProductAQuantityIsFour(): void
    {
        $items = $this->makePack()->getItems();
        $itemA = array_filter($items, fn($i) => $i->getProductId() === 1);
        Assert::equals(4, array_values($itemA)[0]->getQuantity());
    }

    public function testProductBQuantityIsSix(): void
    {
        $items = $this->makePack()->getItems();
        $itemB = array_filter($items, fn($i) => $i->getProductId() === 2);
        Assert::equals(6, array_values($itemB)[0]->getQuantity());
    }

    public function testTotalUnitCountIsTen(): void
    {
        $total = array_sum(
            array_map(fn($i) => $i->getQuantity(), $this->makePack()->getItems())
        );
        Assert::equals(10, $total);
    }

    // ── Prix du pack (forfait journalier) ─────────────────────────────────

    public function testCalculateTotalForOneDay(): void
    {
        Assert::equals(50.0, $this->makePack()->calculateTotal(1));
    }

    public function testCalculateTotalForFiveDays(): void
    {
        Assert::equals(250.0, $this->makePack()->calculateTotal(5));
    }

    public function testCalculateTotalNbDaysZeroFallsBackToOneDay(): void
    {
        // nbDays ≤ 0 → on facture au minimum 1 jour
        Assert::equals(50.0, $this->makePack()->calculateTotal(0));
    }

    // ── Total théorique au détail ─────────────────────────────────────────

    public function testCalculateItemsTotalForOneDayRental(): void
    {
        // 4 × 20 + 6 × 30 = 260
        $total = $this->makePack()->calculateItemsTotal(
            [$this->makeProductA(), $this->makeProductB()],
            self::DATE_1J_START,
            self::DATE_1J_END,
        );
        Assert::equals(260.0, $total);
    }

    public function testCalculateItemsTotalSameForTwoDaysNoBeyondBase(): void
    {
        // Le forfait base couvre 1-2 jours, donc même résultat sur 2 jours
        $total = $this->makePack()->calculateItemsTotal(
            [$this->makeProductA(), $this->makeProductB()],
            self::DATE_1J_START,
            self::DATE_2J_END,
        );
        Assert::equals(260.0, $total);
    }

    public function testPackPriceIsBelowItemsTotal(): void
    {
        // Le pack offre un tarif avantageux vs la somme au détail
        $pack       = $this->makePack();
        $packPrice  = $pack->calculateTotal(1);   // 50 €
        $itemsTotal = $pack->calculateItemsTotal(
            [$this->makeProductA(), $this->makeProductB()],
            self::DATE_1J_START,
            self::DATE_1J_END,
        );                                         // 260 €
        Assert::true($packPrice < $itemsTotal);
    }
}
