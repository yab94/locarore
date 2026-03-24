<?php

declare(strict_types=1);

namespace Rore\Application\Cart;

use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\Service\PricingService;

/**
 * Récupère les données du panier avec les prix calculés.
 */
final class GetCartDataUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface  $productRepo,
        private readonly PackRepositoryInterface     $packRepo,
        private readonly CategoryRepositoryInterface $categoryRepo,
        private readonly PricingService              $pricingService,
    ) {}

    /**
     * @param array<int, int> $cartItems [productId => quantity]
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
        foreach ($cartPacks as $packId => $_) {
            $pack = $this->packRepo->findById((int) $packId);
            if ($pack) {
                $cartPacksArray[] = $pack;
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
