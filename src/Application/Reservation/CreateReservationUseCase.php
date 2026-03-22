<?php

declare(strict_types=1);

namespace Rore\Application\Reservation;

use Rore\Domain\Reservation\Entity\Reservation;
use Rore\Domain\Reservation\Entity\ReservationItem;
use Rore\Domain\Reservation\Repository\ReservationRepositoryInterface;

class CreateReservationUseCase
{
    public function __construct(
        private ReservationRepositoryInterface $reservationRepository,
    ) {}

    /**
     * @param array<int, int>   $items        [productId => quantity]
     * @param array<int, float> $priceSnapshots [productId => unitPrice]
     */
    public function execute(
        string  $customerName,
        string  $customerEmail,
        ?string $customerPhone,
        ?string $customerAddress,
        ?string $eventAddress,
        string  $startDate,
        string  $endDate,
        array   $items,
        ?string $notes          = null,
        array   $priceSnapshots = [],
    ): int {
        $now = new \DateTimeImmutable();

        $reservation = new Reservation(
            id:              null,
            customerName:    $customerName,
            customerEmail:   $customerEmail,
            customerPhone:   $customerPhone,
            customerAddress: $customerAddress,
            eventAddress:    $eventAddress,
            startDate:       new \DateTimeImmutable($startDate),
            endDate:         new \DateTimeImmutable($endDate),
            status:          'pending',
            notes:           $notes,
            createdAt:       $now,
            updatedAt:       $now,
        );

        $reservationItems = [];
        foreach ($items as $productId => $quantity) {
            $reservationItems[] = new ReservationItem(
                id:                null,
                reservationId:     0,
                productId:         (int) $productId,
                quantity:          (int) $quantity,
                unitPriceSnapshot: $priceSnapshots[$productId] ?? null,
            );
        }
        $reservation->setItems($reservationItems);

        return $this->reservationRepository->save($reservation);
    }
}
