<?php

declare(strict_types=1);

namespace Rore\Reservation\UseCase;

use Rore\Catalog\Port\PackRepositoryInterface;
use Rore\Catalog\Port\ProductRepositoryInterface;
use Rore\Catalog\Service\PricingService;
use Rore\Reservation\Port\ReservationRepositoryInterface;
use Rore\Catalog\Adapter\MySqlProductRepository;
use Rore\Catalog\Adapter\MySqlPackRepository;
use Rore\Reservation\Adapter\MySqlReservationRepository;
use Rore\Framework\Di\BindAdapter;

final class GetReservationDetailsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlReservationRepository::class)]
        private readonly ReservationRepositoryInterface $reservationRepo,
        #[BindAdapter(MySqlProductRepository::class)]
        private readonly ProductRepositoryInterface $productRepo,
        #[BindAdapter(MySqlPackRepository::class)]
        private readonly PackRepositoryInterface $packRepo,
        private readonly PricingService                 $pricing,
    ) {}

    /**
     * @return array{
     *   reservation: \Rore\Reservation\Entity\Reservation,
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
