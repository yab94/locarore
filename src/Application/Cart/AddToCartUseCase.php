<?php

declare(strict_types=1);

namespace Rore\Application\Cart;

use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Reservation\Service\AvailabilityService;

class AddToCartUseCase
{
    public function __construct(
        private CartSession                $cart,
        private ProductRepositoryInterface $productRepository,
        private AvailabilityService        $availabilityService,
    ) {}

    public function execute(int $productId, int $quantity): void
    {
        if (!$this->cart->hasDates()) {
            throw new \RuntimeException("Veuillez d'abord choisir vos dates.");
        }
        if ($quantity < 1) {
            throw new \InvalidArgumentException("La quantité doit être au moins 1.");
        }

        $product = $this->productRepository->findById($productId);
        if ($product === null || !$product->isActive()) {
            throw new \RuntimeException("Produit indisponible.");
        }

        $start = new \DateTimeImmutable($this->cart->getStartDate());
        $end   = new \DateTimeImmutable($this->cart->getEndDate());

        // Quantité déjà dans le panier pour ce produit
        $alreadyInCart = $this->cart->getItems()[$productId] ?? 0;
        $totalRequested = $alreadyInCart + $quantity;

        if (!$this->availabilityService->isAvailable(
            $product,
            $totalRequested,
            $start,
            $end,
        )) {
            $available = $this->availabilityService->getAvailableQuantity($product, $start, $end);
            throw new \RuntimeException(
                "Stock insuffisant. Disponible : $available."
            );
        }

        $this->cart->addItem($productId, $quantity);
    }
}
