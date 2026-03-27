<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\ProductRepositoryInterface;
use Rore\Adapter\MySqlProductRepository;
use RRB\Di\BindAdapter;

/**
 * Récupère tous les produits du catalogue.
 */
final class GetAllProductsUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepository::class)]
        private readonly ProductRepositoryInterface $productRepo,
    ) {}

    /**
     * @return array<int, \Rore\Entity\Product>
     */
    public function execute(): array
    {
        return $this->productRepo->findAll();
    }
}
