<?php

declare(strict_types=1);

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\PackItem;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Service\PricingService;

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
    private PricingService $calc;

    public function setUp(): void
    {
        $this->calc = new PricingService();
    }
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

    private function makePack(
        float $pricePerDay        = 50.0,
        float $priceExtraWeekend  = 0.0,
        float $priceExtraWeekday  = 0.0,
    ): Pack {
        $pack = new Pack(
            id:                 1,
            name:               'Pack Mariage',
            slug:               'pack-mariage',
            description:        null,
            pricePerDay:        $pricePerDay,
            priceExtraWeekend:  $priceExtraWeekend,
            priceExtraWeekday:  $priceExtraWeekday,
            isActive:           true,
            createdAt:          new \DateTimeImmutable('2025-01-01'),
            updatedAt:          new \DateTimeImmutable('2025-01-01'),
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

    // ── Prix du pack (forfait + suppléments) ──────────────────────────────

    public function testCalculateTotalForOneDay(): void
    {
        // 1 jour (lundi) → forfait base uniquement
        Assert::equals(50.0, $this->calc->calculate($this->makePack(), '2025-06-02', '2025-06-02'));
    }

    public function testCalculateTotalForFiveDaysWithExtraWeekday(): void
    {
        // pricePerDay=50 (forfait base 2j) + priceExtraWeekday=40
        // 5j → 3 jours au-delà du forfait → 50 + (3 × 40) = 170
        $pack = $this->makePack(pricePerDay: 50.0, priceExtraWeekday: 40.0);
        Assert::equals(170.0, $this->calc->calculate($pack, '2025-06-02', '2025-06-06')); // lun → ven
    }

    public function testCalculateTotalNoExtraIfWithinTwoDays(): void
    {
        // Forfait base couvre les 2 premiers jours, pas de supplément
        $pack = $this->makePack(pricePerDay: 50.0, priceExtraWeekday: 40.0);
        Assert::equals(50.0, $this->calc->calculate($pack, '2025-06-02', '2025-06-03')); // 2 jours
    }

    public function testCalculateTotalNbDaysZeroFallsBackToOneDay(): void
    {
        // 1 jour minimum même si passé comme même date
        Assert::equals(50.0, $this->calc->calculate($this->makePack(), '2025-06-02', '2025-06-02'));
    }

    public function testWeekendRateAppliedWhenSatAndSunAndLessThanFourDays(): void
    {
        // sam+dim dans ≤4j → supplément weekend (ici 10€/j, 0 extra day → total=50)
        $pack = $this->makePack(pricePerDay: 50.0, priceExtraWeekend: 10.0, priceExtraWeekday: 40.0);
        Assert::equals(50.0, $this->calc->calculate($pack, '2025-06-07', '2025-06-08')); // sam+dim = 2j ≤4j
    }

    public function testWeekdayRateAppliedWhenMoreThanFourDays(): void
    {
        // sam+dim présents mais 5j > 4j → tarif weekday
        $pack = $this->makePack(pricePerDay: 50.0, priceExtraWeekend: 10.0, priceExtraWeekday: 40.0);
        // mer 04 → dim 08 = 5 jours, contient sam+dim mais >4j → extraRate=40
        Assert::equals(50.0 + (3 * 40.0), $this->calc->calculate($pack, '2025-06-04', '2025-06-08'));
    }

    // ── Total théorique au détail ─────────────────────────────────────────

    public function testCalculateItemsTotalForOneDayRental(): void
    {
        // 4 × 20 + 6 × 30 = 260
        $total = $this->calc->calculateItemsTotal(
            $this->makePack(),
            [$this->makeProductA(), $this->makeProductB()],
            self::DATE_1J_START,
            self::DATE_1J_END,
        );
        Assert::equals(260.0, $total);
    }

    public function testCalculateItemsTotalSameForTwoDaysNoBeyondBase(): void
    {
        // Le forfait base couvre 1-2 jours, donc même résultat sur 2 jours
        $total = $this->calc->calculateItemsTotal(
            $this->makePack(),
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
        $packPrice  = $this->calc->calculate($pack, self::DATE_1J_START, self::DATE_1J_END); // 50 €
        $itemsTotal = $this->calc->calculateItemsTotal(
            $pack,
            [$this->makeProductA(), $this->makeProductB()],
            self::DATE_1J_START,
            self::DATE_1J_END,
        );                                         // 260 €
        Assert::true($packPrice < $itemsTotal);
    }
}
