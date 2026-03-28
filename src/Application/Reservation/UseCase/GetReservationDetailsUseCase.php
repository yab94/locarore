<?php

declare(strict_types=1);

namespace Rore\Application\Reservation\UseCase;

use Rore\Application\Catalog\Port\PackRepositoryInterface;
use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Domain\Catalog\Service\PricingService;
use Rore\Domain\Catalog\Service\PricingServiceInterface;
use Rore\Application\Reservation\Port\ReservationRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlPackRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlReservationRepositoryAdapter;
use RRB\Di\BindAdapter;

final class GetReservationDetailsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlReservationRepositoryAdapter::class)]
        private readonly ReservationRepositoryInterface $reservationRepo,
        #[BindAdapter(MySqlProductRepositoryAdapter::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlPackRepositoryAdapter::class)]
        private readonly PackRepositoryInterface $packRepo,
        #[BindAdapter(PricingService::class)]
        private readonly PricingServiceInterface        $pricing,
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
