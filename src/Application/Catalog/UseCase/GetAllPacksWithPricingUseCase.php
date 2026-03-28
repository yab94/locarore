<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Application\Catalog\Port\PackRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Domain\Catalog\Service\PricingService;
use Rore\Domain\Catalog\Service\PricingServiceInterface;
use Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlPackRepositoryAdapter;
use RRB\Di\BindAdapter;

/**
 * Récupère tous les packs avec leurs prix (base 1 jour).
 * Utilisé notamment dans l'admin pour afficher les prix détail.
 */
final class GetAllPacksWithPricingUseCase
{
    public function __construct(
        #[BindAdapter(MySqlPackRepositoryAdapter::class)]
        private readonly PackRepositoryInterface $packRepo,
        #[BindAdapter(MySqlProductRepositoryAdapter::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(PricingService::class)]
        private readonly PricingServiceInterface    $pricingService,
    ) {}

    /**
     * @return array{packs: array, products: array, retailPrices: array<int, float>}
     */
    public function execute(): array
    {
        $packs = $this->packRepo->findAll();
        $allProducts = $this->productRepo->findAll();
        
        $productsById = [];
        foreach ($allProducts as $p) {
            $productsById[$p->getId()] = $p;
        }

        // Calcul prix détail (base 1 jour)
        $start = new \DateTimeImmutable('2026-01-01');
        $end   = new \DateTimeImmutable('2026-01-01');
        $retailPrices = [];
        
        foreach ($packs as $pack) {
            $retailPrices[$pack->getId()] = $this->pricingService->calculateItemsTotal(
                $pack,
                $productsById,
                $start,
                $end
            );
        }

        return [
            'packs'        => $packs,
            'products'     => $productsById,
            'retailPrices' => $retailPrices,
        ];
    }
}
