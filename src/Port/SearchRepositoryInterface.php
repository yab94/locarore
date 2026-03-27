<?php

declare(strict_types=1);

namespace Rore\Port;

use Rore\Entity\Pack;
use Rore\Entity\Product;
use Rore\Adapter\MySqlSearchRepository;

interface SearchRepositoryInterface
{
    /**
     * @return array{products: Product[], packs: Pack[]}
     */
    public function search(string $query): array;
}