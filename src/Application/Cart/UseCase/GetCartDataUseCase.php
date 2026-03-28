<?php

declare(strict_types=1);

namespace Rore\Application\Cart\UseCase;

use Rore\Application\Catalog\Port\CategoryRepositoryInterface;
use Rore\Application\Catalog\Port\PackRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Domain\Catalog\Service\PricingService;
use Rore\Domain\Catalog\Service\PricingServiceInterface;
use Rore\Infrastructure\Persistence\MySqlCategoryRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlPackRepositoryAdapter;
use RRB\Di\BindAdapter;

/**
 * Récupère les données du panier avec les prix calculés.
 */
final class GetCartDataUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepositoryAdapter::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlPackRepositoryAdapter::class)]
        private readonly PackRepositoryInterface $packRepo,
        #[BindAdapter(MySqlCategoryRepositoryAdapter::class)]
        private readonly CategoryRepositoryInterface $categoryRepo,
        #[BindAdapter(PricingService::class)]
        private readonly PricingServiceInterface     $pricingService,
    ) {}

    /**
     * @param array<int, int>   $cartItems [productId => quantity]
     * @param array<int, mixed> $cartPacks [packId => data]
     * @return array{cartProducts: array, cartPacks: array, productPrices: array, packPrices: array, allCategories: array}
     */
    public function execute(
        array $cartItems,
        array $cartPacks,
        ?\DateTimeImmutable $startDate,
        ?\DateTimeImmutable $endDate
    ): array {
        $cartProducts  = [];
        $productPrices = [];

        foreach ($cartItems as $productId => $quantity) {
            $product = $this->productRepo->findById((int) $productId);
            if ($product) {
                $cartProducts[] = ['product' => $product, 'quantity' => $quantity];
                if ($startDate && $endDate) {
                    $productPrices[$product->getId()] = $this->pricingService->calculate(
                        $product,
                        $startDate,
                        $endDate,
                    );
                }
            }
        }

        $cartPacksArray = [];
        $packPrices     = [];
        foreach ($cartPacks as $packId => $packData) {
            $pack = $this->packRepo->findById((int) $packId);
            if ($pack) {
                // Résoudre les produits sélectionnés pour les slots
                $selections   = is_array($packData) ? ($packData['selections'] ?? []) : [];
                $slotProducts = [];
                foreach ($pack->getItems() as $item) {
                    if (!$item->isSlot()) continue;
                    $selectedProductId = $selections[$item->getId()] ?? null;
                    if ($selectedProductId) {
                        $product = $this->productRepo->findById((int) $selectedProductId);
                        if ($product) {
                            $slotProducts[$item->getId()] = $product;
                        }
                    }
                }

                $cartPacksArray[] = [
                    'pack'         => $pack,
                    'slotProducts' => $slotProducts,
                ];

                if ($startDate && $endDate) {
                    $packPrices[$pack->getId()] = $this->pricingService->calculate(
                        $pack,
                        $startDate,
                        $endDate,
                    );
                }
            }
        }

        $allCategories = $this->categoryRepo->findAllActive();

        return [
            'cartProducts'  => $cartProducts,
            'cartPacks'     => $cartPacksArray,
            'productPrices' => $productPrices,
            'packPrices'    => $packPrices,
            'allCategories' => $allCategories,
        ];
    }
}
