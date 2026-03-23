<?php

declare(strict_types=1);

$statusValue = (string) ($status ?? '');

return match ($statusValue) {
    'pending'   => 'bg-yellow-100 text-yellow-800',
    'quoted'    => 'bg-orange-100 text-orange-800',
    'confirmed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800',
    default     => 'bg-gray-100 text-gray-800',
};
