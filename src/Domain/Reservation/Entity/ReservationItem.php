<?php

declare(strict_types=1);

namespace Rore\Domain\Reservation\Entity;

class ReservationItem
{
    public function __construct(
        private ?int    $id,
        private int     $reservationId,
        private ?int    $productId,
        private int     $quantity,
        private ?int    $packId              = null,
        private ?float  $unitPriceSnapshot   = null,  // prix unitaire capturé au checkout
    ) {}

    public function getId(): ?int                  { return $this->id; }
    public function getReservationId(): int         { return $this->reservationId; }
    public function getProductId(): ?int            { return $this->productId; }
    public function getQuantity(): int              { return $this->quantity; }
    public function getPackId(): ?int               { return $this->packId; }
    public function getUnitPriceSnapshot(): ?float  { return $this->unitPriceSnapshot; }
    public function getTotalSnapshot(): ?float
    {
        return $this->unitPriceSnapshot !== null
            ? $this->unitPriceSnapshot * $this->quantity
            : null;
    }
}
