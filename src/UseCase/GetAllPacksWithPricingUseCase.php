<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\PackRepositoryInterface;
use Rore\Port\ProductRepositoryInterface;
use Rore\Service\PricingService;
use Rore\Adapter\MySqlProductRepository;
use Rore\Adapter\MySqlPackRepository;
use RRB\Di\BindAdapter;

/**
 * Récupère tous les packs avec leurs prix (base 1 jour).
 * Utilisé notamment dans l'admin pour afficher les prix détail.
 */
final class GetAllPacksWithPricingUseCase
{
    public function __construct(
        #[BindAdapter(MySqlPackRepository::class)]
        private readonly PackRepositoryInterface $packRepo,
        #[BindAdapter(MySqlProductRepository::class)]
        private readonly ProductRepositoryInterface $productRepo,
        private readonly PricingService             $pricingService,
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
