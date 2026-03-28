<?php

declare(strict_types=1);

namespace Rore\Application\Search\UseCase;

use Rore\Application\Search\Port\SearchRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlSearchRepositoryAdapter;
use RRB\Di\BindAdapter;

class SearchUseCase
{
    public function __construct(
        #[BindAdapter(MySqlSearchRepositoryAdapter::class)]
        private readonly SearchRepositoryInterface $repository,
    ) {}

    /**
     * @return array{query: string, products: \Rore\Domain\Catalog\Entity\Product[], packs: \Rore\Domain\Catalog\Entity\Pack[]}
     */
    public function execute(string $query): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 2) {
            return ['query' => $query, 'products' => [], 'packs' => [], 'productsById' => []];
        }

        $results = $this->repository->search($query);

        return [
            'query'        => $query,
            'products'     => $results['products'],
            'packs'        => $results['packs'],
            'productsById' => $results['productsById'],
        ];
    }
}