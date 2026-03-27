<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Repository;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Infrastructure\Persistence\MySqlSearchRepository;

interface SearchRepositoryInterface
{
    /**
     * @return array{products: Product[], packs: Pack[]}
     */
    public function search(string $query): array;
}