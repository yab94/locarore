<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Catalog\Port\ProductRepositoryInterface;
use Rore\Catalog\Adapter\MySqlProductRepository;
use Rore\Framework\Di\BindAdapter;

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
     * @return array<int, \Rore\Catalog\Entity\Product>
     */
    public function execute(): array
    {
        return $this->productRepo->findAll();
    }
}
