<?php

declare(strict_types=1);

use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Service\PricingCalculator;

class ProductPriceTest
{
    private PricingCalculator $calc;

    public function setUp(): void
    {
        $this->calc = new PricingCalculator();
    }
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
        Assert::equals(80.0, $this->calc->calculate($p, '2026-06-01', '2026-06-01'));
    }

    public function testTwoDaysReturnsPriceBase(): void
    {
        $p = $this->makeProduct();
        // Lun-Mar : 2 jours, pas d'extra
        Assert::equals(80.0, $this->calc->calculate($p, '2026-06-01', '2026-06-02'));
    }

    // ── Supplément semaine ────────────────────────────────────────────────

    public function testThreeDaysWeekdayHasOneExtraDay(): void
    {
        $p = $this->makeProduct(priceExtraWeekday: 15.0);
        // Lun-Mer (3 jours, pas de WE complet)
        Assert::equals(95.0, $this->calc->calculate($p, '2026-06-01', '2026-06-03'));
    }

    public function testFiveDaysWeekday(): void
    {
        $p = $this->makeProduct(priceExtraWeekday: 15.0);
        // Lun-Ven (5 jours, 3 extras × 15)
        Assert::equals(125.0, $this->calc->calculate($p, '2026-06-01', '2026-06-05'));
    }

    // ── Forfait WE (sam+dim ≤ 4j, extra = 0) ─────────────────────────────

    public function testWeekendTwoDaysNoExtra(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 0.0, priceExtraWeekday: 15.0);
        // Sam-Dim 2026-06-06/07 (2 jours WE, dans forfait base)
        Assert::equals(80.0, $this->calc->calculate($p, '2026-06-06', '2026-06-07'));
    }

    public function testWeekendThreeDaysNoExtra(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 0.0, priceExtraWeekday: 15.0);
        // Ven-Dim 2026-06-05/07 : 3 jours, contient sam+dim, ≤4j → WE rate = 0
        // extraDays = 1, extraRate = 0 → 80
        Assert::equals(80.0, $this->calc->calculate($p, '2026-06-05', '2026-06-07'));
    }

    public function testWeekendFourDaysNoExtra(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 0.0, priceExtraWeekday: 15.0);
        // Jeu-Dim 2026-06-04/07 : 4 jours, sam+dim inclus, ≤4j → WE rate = 0
        // extraDays = 2, extraRate = 0 → 80
        Assert::equals(80.0, $this->calc->calculate($p, '2026-06-04', '2026-06-07'));
    }

    public function testWeekendFiveDaysFallsBackToWeekdayRate(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 0.0, priceExtraWeekday: 15.0);
        // Mer-Dim 2026-06-03/07 : 5 jours avec sam+dim → > 4j donc pas WE
        // extraDays = 3, extraRate = 15 → 80 + 45 = 125
        Assert::equals(125.0, $this->calc->calculate($p, '2026-06-03', '2026-06-07'));
    }

    public function testWeekendWithWeekendSurcharge(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 20.0, priceExtraWeekday: 15.0);
        // Sam-Lun 2026-06-06/08 : 3 jours, sam+dim ≤4j → WE rate = 20
        // extraDays = 1 × 20 = 20 → 100
        Assert::equals(100.0, $this->calc->calculate($p, '2026-06-06', '2026-06-08'));
    }

    // ── Sam seul ou dim seul ≠ forfait WE ────────────────────────────────

    public function testSaturdayAloneIsNotWeekend(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 0.0, priceExtraWeekday: 15.0);
        // Jeu-Sam 2026-06-04/06 : 3 jours, sam présent mais dim absent → weekday
        // extraDays = 1 × 15 = 15 → 95
        Assert::equals(95.0, $this->calc->calculate($p, '2026-06-04', '2026-06-06'));
    }

    public function testSundayAloneIsNotWeekend(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 0.0, priceExtraWeekday: 15.0);
        // Dim-Mar 2026-06-07/09 : 3 jours, dim présent mais sam absent → weekday
        // extraDays = 1 × 15 = 15 → 95
        Assert::equals(95.0, $this->calc->calculate($p, '2026-06-07', '2026-06-09'));
    }

    // ── > 4 jours avec priceExtraWeekend > 0 ─────────────────────────────

    public function testFiveDaysWithSatSunUsesWeekdayRateNotWeekendRate(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 50.0, priceExtraWeekday: 15.0);
        // Mer-Dim 2026-06-03/07 : 5 jours, sam+dim inclus MAIS >4j → weekday
        // extraDays = 3 × 15 = 45 → 80 + 45 = 125  (pas 50€ de WE)
        Assert::equals(125.0, $this->calc->calculate($p, '2026-06-03', '2026-06-07'));
    }

    // ── > 4 jours sans aucun WE ──────────────────────────────────────────

    public function testSevenDaysWeekdayOnly(): void
    {
        $p = $this->makeProduct(priceExtraWeekday: 10.0);
        // Lun-Dim 2026-06-01/07 : 7 jours, sam+dim présents MAIS >4j → weekday
        // extraDays = 5 × 10 = 50 → 80 + 50 = 130
        Assert::equals(130.0, $this->calc->calculate($p, '2026-06-01', '2026-06-07'));
    }

    // ── 4 jours sans sam+dim → weekday ───────────────────────────────────

    public function testFourDaysNoWeekendUsesWeekdayRate(): void
    {
        $p = $this->makeProduct(priceExtraWeekend: 0.0, priceExtraWeekday: 15.0);
        // Lun-Jeu 2026-06-01/04 : 4 jours, aucun sam/dim → weekday
        // extraDays = 2 × 15 = 30 → 80 + 30 = 110
        Assert::equals(110.0, $this->calc->calculate($p, '2026-06-01', '2026-06-04'));
    }

    // ── Accepte string ou DateTimeImmutable ──────────────────────────────

    public function testAcceptsDateTimeImmutable(): void
    {
        $p = $this->makeProduct();
        $start = new \DateTimeImmutable('2026-06-01');
        $end   = new \DateTimeImmutable('2026-06-01');
        Assert::equals(80.0, $this->calc->calculate($p, $start, $end));
    }
}
