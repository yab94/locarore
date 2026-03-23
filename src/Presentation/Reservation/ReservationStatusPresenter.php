<?php

declare(strict_types=1);

namespace Rore\Presentation\Reservation;

/**
 * Présentation des statuts de réservation (libellés + classes CSS Tailwind).
 */
final class ReservationStatusPresenter
{
    public static function label(string $status): string
    {
        return match ($status) {
            'pending'   => 'En attente',
            'quoted'    => 'Devis envoyé',
            'confirmed' => 'Confirmée',
            'cancelled' => 'Annulée',
            default     => $status,
        };
    }

    public static function badgeClass(string $status): string
    {
        return match ($status) {
            'pending'   => 'bg-yellow-100 text-yellow-800',
            'quoted'    => 'bg-orange-100 text-orange-800',
            'confirmed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default     => 'bg-gray-100 text-gray-800',
        };
    }
}
