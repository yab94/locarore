<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\ProductRepositoryInterface;
use Rore\Adapter\MySqlProductRepository;
use RRB\Di\BindAdapter;

class ToggleProductUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepository::class)]
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
