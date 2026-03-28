<?php

declare(strict_types=1);

namespace Rore\Domain\Reservation\ValueObject;

enum ReservationStatus: string
{
    case Pending   = 'pending';
    case Quoted    = 'quoted';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'En attente',
            self::Quoted    => 'Devis envoyé',
            self::Confirmed => 'Confirmée',
            self::Cancelled => 'Annulée',
        };
    }
}
