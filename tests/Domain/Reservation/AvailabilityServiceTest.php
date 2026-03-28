<?php

declare(strict_types=1);

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\PackItem;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Reservation\Entity\Reservation;
use Rore\Domain\Reservation\Entity\ReservationItem;
use Rore\Application\Reservation\Port\ReservationRepositoryInterface;
use Rore\Application\Reservation\Service\AvailabilityService;

// ─── Stub ReservationRepository in-memory ───────────────────────────────────

final class StubReservationRepository implements ReservationRepositoryInterface
{
    /** @param Reservation[] $confirmed */
    public function __construct(private array $confirmed = []) {}

    public function findAll(): array { return []; }
    public function findById(int $id): ?Reservation { return null; }
    public function findByStatus(string $status): array { return []; }
    public function save(Reservation $r): int { return 0; }
    public function update(Reservation $r): void {}
    public function findByCustomerEmail(string $email): array { return []; }
    public function getReservedPeriodsByProduct(int $productId): array { return []; }

    public function countReservedQtyForProduct(int $productId, string $startDate, string $endDate): int 
    { 
        return 0; 
    }

    public function findConfirmedOverlapping(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ): array {
        // Filtre les réservations dont la plage chevauche [start, end]
        return array_values(array_filter(
            $this->confirmed,
            fn(Reservation $r) => $r->getStartDate() <= $end && $r->getEndDate() >= $start
        ));
    }
}

// ─── Factory helpers ─────────────────────────────────────────────────────────

final class AvailabilityServiceTest
{
    private function makeProduct(
        int   $stock               = 5,
        int   $stockOnDemand       = 10,
        float $fabricationTimeDays = 3.0,
    ): Product {
        return new Product(
            id:                  42,
            categoryId:          1,
            name:                'Vase',
            slug:                'vase',
            description:         null,
            descriptionShort:    null,
            stock:               $stock,
            stockOnDemand:       $stockOnDemand,
            fabricationTimeDays: $fabricationTimeDays,
            priceBase:           80.0,
            priceExtraWeekend:   0.0,
            priceExtraWeekday:   15.0,
            isActive:            true,
            createdAt:           new \DateTimeImmutable('2025-01-01'),
            updatedAt:           new \DateTimeImmutable('2025-01-01'),
        );
    }

    private function makeConfirmedReservation(
        int                $productId,
        int                $qty,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ): Reservation {
        $now = new \DateTimeImmutable();
        $r   = new Reservation(
            id:              1,
            customerName:    'Test',
            customerEmail:   'test@test.fr',
            customerPhone:   null,
            customerAddress: null,
            eventAddress:    null,
            startDate:       $start,
            endDate:         $end,
            status:          'confirmed',
            notes:           null,
            createdAt:       $now,
            updatedAt:       $now,
        );
        $r->setItems([
            new ReservationItem(id: 1, reservationId: 1, productId: $productId, quantity: $qty),
        ]);
        return $r;
    }

    // ─── Scénario central ────────────────────────────────────────────────
    //
    // Produit : stock=5, onDemand=10, fabrication=3j/unité
    // Demande : 8 unités
    //   → 5 du stock physique + 3 du on-demand
    //   → délai minimum = 3 unités × 3j = 9 jours
    //
    // start dans 7j  → impossible  (7 < 9)
    // start dans 10j → possible    (floor(10/3) = 3 buildable, 3 suffisent)

    public function testIsNotAvailableWhenStartIsTooSoon(): void
    {
        $now     = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start   = $now->modify('+7 days');
        $end     = $start->modify('+1 day');
        $product = $this->makeProduct();
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::false(
            $service->isAvailable($product, 8, $start, $end, $now),
            '8 unités dans 7j : 3 on-demand × 3j = 9j requis, impossible en 7j'
        );
    }

    public function testIsAvailableWhenStartHasEnoughLeadTime(): void
    {
        $now     = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start   = $now->modify('+10 days');
        $end     = $start->modify('+1 day');
        $product = $this->makeProduct();
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::true(
            $service->isAvailable($product, 8, $start, $end, $now),
            '8 unités dans 10j : 3 on-demand × 3j = 9j requis, possible en 10j'
        );
    }

    // ─── getAvailableQuantity selon le délai ─────────────────────────────

    public function testAvailableQuantityIn7Days(): void
    {
        // floor(7 / 3) = 2 fabricables → 5 stock + 2 = 7
        $now     = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start   = $now->modify('+7 days');
        $end     = $start->modify('+1 day');
        $product = $this->makeProduct();
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::equals(7, $service->getAvailableQuantity($product, $start, $end, $now),
            '5 stock + floor(7/3)=2 fabricables = 7'
        );
    }

    public function testAvailableQuantityIn10Days(): void
    {
        // floor(10 / 3) = 3 fabricables → 5 stock + 3 = 8
        $now     = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start   = $now->modify('+10 days');
        $end     = $start->modify('+1 day');
        $product = $this->makeProduct();
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::equals(8, $service->getAvailableQuantity($product, $start, $end, $now),
            '5 stock + floor(10/3)=3 fabricables = 8'
        );
    }

    // ─── Avec une réservation confirmée existante ─────────────────────────

    public function testExistingConfirmedReservationReducesAvailableStock(): void
    {
        // Réservation confirmée de 3 → consomme 3 du stock physique
        // Il reste : availableDurable=2, remainingOnDemand=10
        // floor(10/3)=3 buildable → disponible = 2 + 3 = 5
        $now     = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start   = $now->modify('+10 days');
        $end     = $start->modify('+1 day');
        $product = $this->makeProduct();

        $existing = $this->makeConfirmedReservation(42, 3, $start, $end);
        $service  = new AvailabilityService(new StubReservationRepository([$existing]));

        Assert::equals(5, $service->getAvailableQuantity($product, $start, $end, $now),
            'Résa confirmée de 3 : durable=2 + buildable=3 = 5'
        );
    }

    public function testExistingConfirmedReservationSpillsIntoOnDemand(): void
    {
        // Réservation confirmée de 8 → consomme 5 stock + 3 on-demand
        // remainingOnDemand = 10 - 3 = 7, floor(10/3)=3 buildable
        // start 10j → disponible = 0 + min(7,3) = 3
        $now     = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start   = $now->modify('+10 days');
        $end     = $start->modify('+1 day');
        $product = $this->makeProduct();

        $existing = $this->makeConfirmedReservation(42, 8, $start, $end);
        $service  = new AvailabilityService(new StubReservationRepository([$existing]));

        Assert::equals(3, $service->getAvailableQuantity($product, $start, $end, $now),
            'Résa de 8 : durable=0 + min(7,floor(10/3)=3) = 3'
        );
    }

    // ─── Cas limites ─────────────────────────────────────────────────────

    public function testIsNotAvailableWhenStartIsNow(): void
    {
        $now     = new \DateTimeImmutable('2026-01-01 00:00:00');
        $product = $this->makeProduct();
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::false(
            $service->isAvailable($product, 8, $now, $now->modify('+1 day'), $now),
            'Start = maintenant : aucun délai de fabrication possible'
        );
    }

    public function testPureStockNeedsNoLeadTime(): void
    {
        // Demande ≤ stock physique → pas de délai on-demand
        $now     = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start   = $now->modify('+1 day');
        $product = $this->makeProduct();
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::true(
            $service->isAvailable($product, 5, $start, $start->modify('+1 day'), $now),
            '5 unités = stock physique exact, aucun on-demand requis'
        );
    }

    public function testExactMinimumLeadTime(): void
    {
        // 3 on-demand × 3j = 9j exactement → doit passer (>=)
        $now     = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start   = $now->modify('+9 days');
        $end     = $start->modify('+1 day');
        $product = $this->makeProduct();
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::true(
            $service->isAvailable($product, 8, $start, $end, $now),
            'Exactement 9j = délai requis : doit être disponible'
        );
    }

    public function testOneLessThanMinimumLeadTime(): void
    {
        // 8j 23h 59m → inférieur strict à 9j → doit échouer
        $now     = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start   = $now->modify('+8 days')->modify('+23 hours')->modify('+59 minutes');
        $end     = $start->modify('+1 day');
        $product = $this->makeProduct();
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::false(
            $service->isAvailable($product, 8, $start, $end, $now),
            '8j 23h59 < 9j requis : doit être indisponible'
        );
    }

    // ─── Tests de disponibilité des packs ────────────────────────────────

    private function makePack(int $id = 1): Pack
    {
        $pack = new Pack(
            id:                 $id,
            name:               'Pack Test',
            slug:               'pack-test',
            description:        null,
            descriptionShort:   null,
            pricePerDay:        100.0,
            priceExtraWeekend:  0.0,
            priceExtraWeekday:  0.0,
            isActive:           true,
            createdAt:          new \DateTimeImmutable('2025-01-01'),
            updatedAt:          new \DateTimeImmutable('2025-01-01'),
        );
        // Pack contient : 2× produit 1 + 3× produit 2
        $pack->setItems([
            new PackItem(id: 1, packId: $id, productId: 1, categoryId: null, quantity: 2),
            new PackItem(id: 2, packId: $id, productId: 2, categoryId: null, quantity: 3),
        ]);
        return $pack;
    }

    public function testPackIsAvailableWhenAllProductsAreAvailable(): void
    {
        $now   = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start = $now->modify('+10 days');
        $end   = $start->modify('+1 day');

        // Produit 1 : stock=5 (besoin de 2) → OK
        $product1 = new Product(
            id: 1, categoryId: 1, name: 'P1', slug: 'p1', description: null,
            descriptionShort: null,
            stock: 5, stockOnDemand: 0, fabricationTimeDays: 0.0,
            priceBase: 10.0, priceExtraWeekend: 0.0, priceExtraWeekday: 0.0,
            isActive: true,
            createdAt: new \DateTimeImmutable(), updatedAt: new \DateTimeImmutable(),
        );

        // Produit 2 : stock=10 (besoin de 3) → OK
        $product2 = new Product(
            id: 2, categoryId: 1, name: 'P2', slug: 'p2', description: null,
            descriptionShort: null,
            stock: 10, stockOnDemand: 0, fabricationTimeDays: 0.0,
            priceBase: 20.0, priceExtraWeekend: 0.0, priceExtraWeekday: 0.0,
            isActive: true,
            createdAt: new \DateTimeImmutable(), updatedAt: new \DateTimeImmutable(),
        );

        $pack = $this->makePack();
        $productsById = [1 => $product1, 2 => $product2];
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::true(
            $service->isPackAvailable($pack, $productsById, $start, $end, $now),
            'Pack dispo : P1 a 5 (besoin 2) + P2 a 10 (besoin 3)'
        );
    }

    public function testPackIsNotAvailableWhenOneProductHasInsufficientStock(): void
    {
        $now   = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start = $now->modify('+10 days');
        $end   = $start->modify('+1 day');

        // Produit 1 : stock=5 (besoin de 2) → OK
        $product1 = new Product(
            id: 1, categoryId: 1, name: 'P1', slug: 'p1', description: null,
            descriptionShort: null,
            stock: 5, stockOnDemand: 0, fabricationTimeDays: 0.0,
            priceBase: 10.0, priceExtraWeekend: 0.0, priceExtraWeekday: 0.0,
            isActive: true,
            createdAt: new \DateTimeImmutable(), updatedAt: new \DateTimeImmutable(),
        );

        // Produit 2 : stock=2 (besoin de 3) → INSUFFISANT
        $product2 = new Product(
            id: 2, categoryId: 1, name: 'P2', slug: 'p2', description: null,
            descriptionShort: null,
            stock: 2, stockOnDemand: 0, fabricationTimeDays: 0.0,
            priceBase: 20.0, priceExtraWeekend: 0.0, priceExtraWeekday: 0.0,
            isActive: true,
            createdAt: new \DateTimeImmutable(), updatedAt: new \DateTimeImmutable(),
        );

        $pack = $this->makePack();
        $productsById = [1 => $product1, 2 => $product2];
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::false(
            $service->isPackAvailable($pack, $productsById, $start, $end, $now),
            'Pack indispo : P2 a stock=2 mais besoin de 3'
        );
    }

    public function testPackIsNotAvailableWhenOneProductIsMissing(): void
    {
        $now   = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start = $now->modify('+10 days');
        $end   = $start->modify('+1 day');

        // Seulement produit 1, produit 2 manquant
        $product1 = new Product(
            id: 1, categoryId: 1, name: 'P1', slug: 'p1', description: null,
            descriptionShort: null,
            stock: 5, stockOnDemand: 0, fabricationTimeDays: 0.0,
            priceBase: 10.0, priceExtraWeekend: 0.0, priceExtraWeekday: 0.0,
            isActive: true,
            createdAt: new \DateTimeImmutable(), updatedAt: new \DateTimeImmutable(),
        );

        $pack = $this->makePack();
        $productsById = [1 => $product1]; // produit 2 manquant !
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::false(
            $service->isPackAvailable($pack, $productsById, $start, $end, $now),
            'Pack indispo : produit 2 introuvable'
        );
    }

    public function testPackIsNotAvailableWhenOneProductIsInactive(): void
    {
        $now   = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start = $now->modify('+10 days');
        $end   = $start->modify('+1 day');

        $product1 = new Product(
            id: 1, categoryId: 1, name: 'P1', slug: 'p1', description: null,
            descriptionShort: null,
            stock: 5, stockOnDemand: 0, fabricationTimeDays: 0.0,
            priceBase: 10.0, priceExtraWeekend: 0.0, priceExtraWeekday: 0.0,
            isActive: true,
            createdAt: new \DateTimeImmutable(), updatedAt: new \DateTimeImmutable(),
        );

        // Produit 2 inactif
        $product2 = new Product(
            id: 2, categoryId: 1, name: 'P2', slug: 'p2', description: null,
            descriptionShort: null,
            stock: 10, stockOnDemand: 0, fabricationTimeDays: 0.0,
            priceBase: 20.0, priceExtraWeekend: 0.0, priceExtraWeekday: 0.0,
            isActive: false,
            createdAt: new \DateTimeImmutable(), updatedAt: new \DateTimeImmutable(),
        );

        $pack = $this->makePack();
        $productsById = [1 => $product1, 2 => $product2];
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::false(
            $service->isPackAvailable($pack, $productsById, $start, $end, $now),
            'Pack indispo : produit 2 inactif'
        );
    }

    public function testPackUsesOnDemandStockWhenNeeded(): void
    {
        $now   = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start = $now->modify('+10 days');
        $end   = $start->modify('+1 day');

        // Produit 1 : stock=1 (besoin 2) → doit utiliser 1 on-demand (délai 3j)
        $product1 = new Product(
            id: 1, categoryId: 1, name: 'P1', slug: 'p1', description: null,
            descriptionShort: null,
            stock: 1, stockOnDemand: 5, fabricationTimeDays: 3.0,
            priceBase: 10.0, priceExtraWeekend: 0.0, priceExtraWeekday: 0.0,
            isActive: true,
            createdAt: new \DateTimeImmutable(), updatedAt: new \DateTimeImmutable(),
        );

        // Produit 2 : stock suffisant
        $product2 = new Product(
            id: 2, categoryId: 1, name: 'P2', slug: 'p2', description: null,
            descriptionShort: null,
            stock: 10, stockOnDemand: 0, fabricationTimeDays: 0.0,
            priceBase: 20.0, priceExtraWeekend: 0.0, priceExtraWeekday: 0.0,
            isActive: true,
            createdAt: new \DateTimeImmutable(), updatedAt: new \DateTimeImmutable(),
        );

        $pack = $this->makePack();
        $productsById = [1 => $product1, 2 => $product2];
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::true(
            $service->isPackAvailable($pack, $productsById, $start, $end, $now),
            'Pack dispo : P1 utilise 1 stock + 1 on-demand (délai 3j < 10j disponibles)'
        );
    }

    public function testPackIsNotAvailableWhenLeadTimeInsufficient(): void
    {
        $now   = new \DateTimeImmutable('2026-01-01 00:00:00');
        $start = $now->modify('+2 days'); // Seulement 2j de délai
        $end   = $start->modify('+1 day');

        // Produit 1 : stock=1 (besoin 2) → doit fabriquer 1 (délai 3j requis)
        $product1 = new Product(
            id: 1, categoryId: 1, name: 'P1', slug: 'p1', description: null,
            descriptionShort: null,
            stock: 1, stockOnDemand: 5, fabricationTimeDays: 3.0,
            priceBase: 10.0, priceExtraWeekend: 0.0, priceExtraWeekday: 0.0,
            isActive: true,
            createdAt: new \DateTimeImmutable(), updatedAt: new \DateTimeImmutable(),
        );

        $product2 = new Product(
            id: 2, categoryId: 1, name: 'P2', slug: 'p2', description: null,
            descriptionShort: null,
            stock: 10, stockOnDemand: 0, fabricationTimeDays: 0.0,
            priceBase: 20.0, priceExtraWeekend: 0.0, priceExtraWeekday: 0.0,
            isActive: true,
            createdAt: new \DateTimeImmutable(), updatedAt: new \DateTimeImmutable(),
        );

        $pack = $this->makePack();
        $productsById = [1 => $product1, 2 => $product2];
        $service = new AvailabilityService(new StubReservationRepository());

        Assert::false(
            $service->isPackAvailable($pack, $productsById, $start, $end, $now),
            'Pack indispo : P1 besoin 1 on-demand × 3j = 3j requis, mais seulement 2j disponibles'
        );
    }
}
