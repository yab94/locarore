<?php

declare(strict_types=1);

namespace Rore\Application\Cart;

use Rore\Application\Reservation\CreateReservationUseCase;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;

class CheckoutUseCase
{
    public function __construct(
        private CartSession              $cart,
        private ProductRepositoryInterface $productRepository,
        private CreateReservationUseCase $createReservation,
    ) {}

    public function execute(
        string  $customerName,
        string  $customerEmail,
        ?string $customerPhone,
        ?string $customerAddress,
        ?string $eventAddress,
        ?string $notes,
    ): int {
        if (!$this->cart->hasDates()) {
            throw new \RuntimeException("Aucune date de réservation sélectionnée.");
        }
        if ($this->cart->isEmpty()) {
            throw new \RuntimeException("Le panier est vide.");
        }

        // Calculer le prix unitaire par produit au moment du checkout
        $priceSnapshots = [];
        foreach ($this->cart->getItems() as $productId => $qty) {
            $product = $this->productRepository->findById((int) $productId);
            if ($product) {
                $priceSnapshots[$productId] = $product->calculatePrice(
                    $this->cart->getStartDate(),
                    $this->cart->getEndDate(),
                );
            }
        }

        $reservationId = $this->createReservation->execute(
            customerName:    $customerName,
            customerEmail:   $customerEmail,
            customerPhone:   $customerPhone,
            customerAddress: $customerAddress,
            eventAddress:    $eventAddress,
            startDate:       $this->cart->getStartDate(),
            endDate:         $this->cart->getEndDate(),
            items:           $this->cart->getItems(),
            notes:           $notes,
            priceSnapshots:  $priceSnapshots,
        );

        $this->cart->clear();

        return $reservationId;
    }
}
