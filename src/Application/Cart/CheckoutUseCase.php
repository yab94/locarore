<?php

declare(strict_types=1);

namespace Rore\Application\Cart;

use Rore\Application\Reservation\CreateReservationUseCase;

class CheckoutUseCase
{
    public function __construct(
        private CartSession              $cart,
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
        );

        $this->cart->clear();

        return $reservationId;
    }
}
