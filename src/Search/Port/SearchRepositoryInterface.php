<?php

declare(strict_types=1);

namespace Rore\Search\Port;

use Rore\Catalog\Entity\Pack;
use Rore\Catalog\Entity\Product;
use Rore\Search\Adapter\MySqlSearchRepository;

interface SearchRepositoryInterface
{
    /**
     * @return array{products: Product[], packs: Pack[]}
     */
    public function search(string $query): array;
}