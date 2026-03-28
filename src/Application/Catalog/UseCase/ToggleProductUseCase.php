<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Application\Catalog\Port\ProductRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlProductRepositoryAdapter;
use RRB\Di\BindAdapter;

class ToggleProductUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepositoryAdapter::class)]
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function execute(int $id): void
    {
        $product = $this->productRepository->findById($id);
        if ($product === null) {
            throw new \RuntimeException("Produit introuvable ($id).");
        }

        $product->toggle();
        $product->setUpdatedAt(new \DateTimeImmutable());

        $this->productRepository->save($product);
    }
}
