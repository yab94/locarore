<?php

declare(strict_types=1);

use Rore\Domain\Catalog\Entity\Product;

class ProductPriceTest
{
    private function makeProduct(
        float $priceBase          = 80.0,
        float $priceExtraWeekend  = 0.0,
        float $priceExtraWeekday  = 15.0,
    ): Product {
        return new Product(
            id:                  1,
            categoryId:          1,
            name:                'Test',
            slug:                'test',
            description:         null,
            stock:               10,
            stockOnDemand:       0,
            fabricationTimeDays: 0.0,
            priceBase:           $priceBase,
            priceExtraWeekend:   $priceExtraWeekend,
            priceExtraWeekday:   $priceExtraWeekday,
            isActive:            true,
            createdAt:           new \DateTimeImmutable('2025-01-01'),
            updatedAt:           new \DateTimeImmutable('2025-01-01'),
        );
    }

    // ── Forfait de base ───────────────────────────────────────────────────

    public function testOneDayReturnsPriceBase(): void
    {
        $p = $this->makeProduct();
        // Lundi 2026-06-01 (1 jour → forfait base, 0 extra)
        Assert::equals(80.0, $p->calculatePrice('2026-06-01', '2026-06-01'));
    }

    public function testTwoDaysReturnsPriceBase(): void
    {
        $p = $this->makeProduct();
        // Lun-Mar : 2 jours, pas d'extra
        Assert::equals(80.0, $p->calculatePrice('2026-06-01', '2026-06-02'));
    }

    // ── Supplément semaine ────────────────────────────────────────────────

    public function testThreeDaysWeekdayHasOneExtraDay(): void
    {
        $p = $this->makeProduct(priceExtraWeekday: 15.0);
        // Lun-Mer (3 jours, pas de WE complet)
        Assert::equals(95.0, $p->calculatePrice('2026-06-01', '2026-06-03'));
    }

    public function testFiveDaysWeekday(): void
    {
        $p = $this->makeProduct(priceExtraWeekday: 15.0);
        // Lun-Ven (5 jours, 3 extras × 15)
        Assert::equals(125.0, $p->calculatePrice('2026-06-01', '2026-06-05'));
    }

    // ── Forfait WE (sam+dim ≤ 4j, extra = 0) ─────────────────────────────

    public function testWeekendTwoDaysNoExtra(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 0.0, priceExtraWeekday: 15.0);
        // Sam-Dim 2026-06-06/07 (2 jours WE, dans forfait base)
        Assert::equals(80.0, $p->calculatePrice('2026-06-06', '2026-06-07'));
    }

    public function testWeekendThreeDaysNoExtra(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 0.0, priceExtraWeekday: 15.0);
        // Ven-Dim 2026-06-05/07 : 3 jours, contient sam+dim, ≤4j → WE rate = 0
        // extraDays = 1, extraRate = 0 → 80
        Assert::equals(80.0, $p->calculatePrice('2026-06-05', '2026-06-07'));
    }

    public function testWeekendFourDaysNoExtra(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 0.0, priceExtraWeekday: 15.0);
        // Jeu-Dim 2026-06-04/07 : 4 jours, sam+dim inclus, ≤4j → WE rate = 0
        // extraDays = 2, extraRate = 0 → 80
        Assert::equals(80.0, $p->calculatePrice('2026-06-04', '2026-06-07'));
    }

    public function testWeekendFiveDaysFallsBackToWeekdayRate(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 0.0, priceExtraWeekday: 15.0);
        // Mer-Dim 2026-06-03/07 : 5 jours avec sam+dim → > 4j donc pas WE
        // extraDays = 3, extraRate = 15 → 80 + 45 = 125
        Assert::equals(125.0, $p->calculatePrice('2026-06-03', '2026-06-07'));
    }

    public function testWeekendWithWeekendSurcharge(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 20.0, priceExtraWeekday: 15.0);
        // Sam-Lun 2026-06-06/08 : 3 jours, sam+dim ≤4j → WE rate = 20
        // extraDays = 1 × 20 = 20 → 100
        Assert::equals(100.0, $p->calculatePrice('2026-06-06', '2026-06-08'));
    }

    // ── Accepte string ou DateTimeImmutable ──────────────────────────────

    public function testAcceptsDateTimeImmutable(): void
    {
        $p = $this->makeProduct();
        $start = new \DateTimeImmutable('2026-06-01');
        $end   = new \DateTimeImmutable('2026-06-01');
        Assert::equals(80.0, $p->calculatePrice($start, $end));
    }
}
