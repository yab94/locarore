<?php

declare(strict_types=1);

namespace Rore\Application\Reservation;

use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\Service\PricingService;
use Rore\Domain\Reservation\Repository\ReservationRepositoryInterface;

final class GetReservationDetailsUseCase
{
    public function __construct(
        private readonly ReservationRepositoryInterface $reservationRepo,
        private readonly ProductRepositoryInterface     $productRepo,
        private readonly PackRepositoryInterface        $packRepo,
        private readonly PricingService                 $pricing,
    ) {}

    /**
     * @return array{
     *   reservation: \Rore\Domain\Reservation\Entity\Reservation,
     *   products: array,
     *   packs: array,
     *   productCurrentPrices: array,
     * }
     */
    public function execute(int $id): array
    {
        $reservation = $this->reservationRepo->findById($id);
        if ($reservation === null) {
            throw new \RuntimeException("Réservation introuvable ($id).");
        }

        $products             = [];
        $packs                = [];
        $productCurrentPrices = [];

        foreach ($reservation->getItems() as $item) {
            if ($item->getPackId() !== null) {
                $pack = $this->packRepo->findById($item->getPackId());
                if ($pack) $packs[$pack->getId()] = $pack;
            } else {
                $product = $this->productRepo->findById($item->getProductId());
                $products[$item->getProductId()] = $product;
                if ($product) {
                    $productCurrentPrices[$item->getProductId()] = $this->pricing->calculate(
                        $product,
                        $reservation->getStartDate(),
                        $reservation->getEndDate(),
                    );
                }
            }
        }

        return [
            'reservation'          => $reservation,
            'products'             => $products,
            'packs'                => $packs,
            'productCurrentPrices' => $productCurrentPrices,
        ];
    }
}
