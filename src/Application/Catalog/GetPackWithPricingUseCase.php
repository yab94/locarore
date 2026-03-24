<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\Service\PricingService;

/**
 * Récupère un pack avec les infos de pricing (prix pack + prix détail).
 */
final class GetPackWithPricingUseCase
{
    public function __construct(
        private readonly PackRepositoryInterface    $packRepo,
        private readonly ProductRepositoryInterface $productRepo,
        private readonly PricingService             $pricingService,
    ) {}

    /**
     * Retourne un tableau avec le pack et ses prix calculés.
     *
     * @return array{pack: Pack, products: array<int, Product>, packPrice: float, retailPrice: float}|null
     */
    public function execute(
        int $packId,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ): ?array {
        $pack = $this->packRepo->findById($packId);
        if ($pack === null) {
            return null;
        }

        $allProducts = $this->productRepo->findAll();
        $productsById = [];
        foreach ($allProducts as $p) {
            $productsById[$p->getId()] = $p;
        }

        $packPrice = $this->pricingService->calculate($pack, $start, $end);
        $retailPrice = $this->pricingService->calculateItemsTotal($pack, $productsById, $start, $end);

        return [
            'pack'        => $pack,
            'products'    => $productsById,
            'packPrice'   => $packPrice,
            'retailPrice' => $retailPrice,
        ];
    }
}
