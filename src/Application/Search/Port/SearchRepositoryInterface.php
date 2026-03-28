<?php

declare(strict_types=1);

namespace Rore\Application\Search\Port;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\Product;

interface SearchRepositoryInterface
{
    /**
     * @return array{products: Product[], packs: Pack[]}
     */
    public function search(string $query): array;
}
