<?php

declare(strict_types=1);

namespace Rore\Application\Reservation\UseCase;

use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Application\Reservation\Port\ReservationRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter;
use Rore\Infrastructure\Persistence\MySqlReservationRepositoryAdapter;
use RRB\Di\BindAdapter;

final class GetCalendarDataUseCase
{
    public function __construct(
        #[BindAdapter(MySqlReservationRepositoryAdapter::class)]
        private readonly ReservationRepositoryInterface $reservationRepo,
        #[BindAdapter(MySqlProductRepositoryAdapter::class)]
        private readonly ProductRepositoryInterface $productRepo,
    ) {}

    /**
     * @return array{
     *   reservations: array,
     *   products: array,
     *   month: int,
     *   year: int,
     *   start: \DateTimeImmutable,
     *   end: \DateTimeImmutable,
     * }
     */
    public function execute(int $month, int $year): array
    {
        $start = new \DateTimeImmutable("$year-$month-01");
        $end   = $start->modify('last day of this month');

        $reservations = $this->reservationRepo->findConfirmedOverlapping($start, $end);

        $products = [];
        foreach ($reservations as $r) {
            foreach ($r->getItems() as $item) {
                $pid = $item->getProductId();
                if ($pid && !isset($products[$pid])) {
                    $products[$pid] = $this->productRepo->findById($pid);
                }
            }
        }

        return [
            'reservations' => $reservations,
            'products'     => $products,
            'month'        => $month,
            'year'         => $year,
            'start'        => $start,
            'end'          => $end,
        ];
    }
}
