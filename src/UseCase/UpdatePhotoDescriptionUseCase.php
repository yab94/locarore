<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Port\ProductRepositoryInterface;
use Rore\Adapter\MySqlProductRepository;
use RRB\Di\BindAdapter;

class UpdatePhotoDescriptionUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepository::class)]
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function execute(int $photoId, string $description): int
    {
        $photo = $this->productRepository->findPhotoById($photoId);
        if ($photo === null) {
            throw new \RuntimeException("Photo introuvable ($photoId).");
        }

        $this->productRepository->updatePhotoDescription($photoId, $description);

        return $photo->getProductId();
    }
}
