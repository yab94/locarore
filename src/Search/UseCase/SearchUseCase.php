<?php

declare(strict_types=1);

namespace Rore\Search\UseCase;

use Rore\Search\Port\SearchRepositoryInterface;
use Rore\Search\Adapter\MySqlSearchRepository;
use Rore\Framework\Di\BindAdapter;

class SearchUseCase
{
    public function __construct(
        #[BindAdapter(MySqlSearchRepository::class)]
        private readonly SearchRepositoryInterface $repository,
    ) {}

    /**
     * @return array{query: string, products: \Rore\Catalog\Entity\Product[], packs: \Rore\Catalog\Entity\Pack[]}
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