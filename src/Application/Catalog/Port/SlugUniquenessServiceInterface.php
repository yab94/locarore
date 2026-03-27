<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\Port;

interface SlugUniquenessServiceInterface
{
    public function isTaken(string $slug, string $type, ?int $excludeId = null): bool;
}
