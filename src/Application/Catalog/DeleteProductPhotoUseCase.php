<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Framework\FileManagerInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;

class DeleteProductPhotoUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private FileManagerInterface       $fileManager,
    ) {}

    public function execute(int $photoId): int
    {
        $photo = $this->productRepository->findPhotoById($photoId);
        if ($photo === null) {
            throw new \RuntimeException("Photo introuvable ($photoId).");
        }

        $this->fileManager->delete($photo->getFilename());
        $this->productRepository->deletePhoto($photoId);

        return $photo->getProductId();
    }
}
