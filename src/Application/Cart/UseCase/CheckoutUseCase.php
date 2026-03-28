<?php

declare(strict_types=1);

namespace Rore\Application\Cart\UseCase;

use Rore\Application\Reservation\UseCase\CreateReservationUseCase;
use Rore\Application\Cart\Port\CartServiceInterface;
use Rore\Application\Cart\Service\CartService;
use Rore\Application\Catalog\Port\PackRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Domain\Catalog\Service\PricingService;
use Rore\Domain\Catalog\Service\PricingServiceInterface;
use Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlPackRepositoryAdapter;
use RRB\Di\BindAdapter;

class CheckoutUseCase
{
    public function __construct(
        #[BindAdapter(CartService::class)]
        private CartServiceInterface                 $cart,
        #[BindAdapter(MySqlProductRepositoryAdapter::class)]
        private ProductRepositoryInterface $productRepository,
        #[BindAdapter(MySqlPackRepositoryAdapter::class)]
        private PackRepositoryInterface $packRepository,
        private CreateReservationUseCase   $createReservation,
        #[BindAdapter(PricingService::class)]
        private PricingServiceInterface $pricing,
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

        $start = $this->cart->getStartDate();
        $end   = $this->cart->getEndDate();

        // Calculer le prix unitaire par produit au moment du checkout
        $priceSnapshots = [];
        foreach ($this->cart->getItems() as $productId => $qty) {
            $product = $this->productRepository->findById((int) $productId);
            if ($product) {
                $priceSnapshots[$productId] = $this->pricing->calculate(
                    $product,
                    $start,
                    $end,
                );
            }
        }

        // Calculer le prix de chaque pack au moment du checkout
        $packSnapshots = [];
        foreach ($this->cart->getPacks() as $packId => $_) {
            $pack = $this->packRepository->findById((int) $packId);
            if ($pack && $pack->isActive()) {
                $packSnapshots[(int) $packId] = $this->pricing->calculate(
                    $pack,
                    $start,
                    $end,
                );
            }
        }

        $reservationId = $this->createReservation->execute(
            customerName:    $customerName,
            customerEmail:   $customerEmail,
            customerPhone:   $customerPhone,
            customerAddress: $customerAddress,
            eventAddress:    $eventAddress,
            startDate:       $start->format('Y-m-d'),
            endDate:         $end->format('Y-m-d'),
            items:           $this->cart->getItems(),
            packs:           $packSnapshots,
            notes:           $notes,
            priceSnapshots:  $priceSnapshots,
        );

        $this->cart->clear();

        return $reservationId;
    }
}
