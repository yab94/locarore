<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Infrastructure\Config\Config;

class DeleteProductPhotoUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private Config $config,
    ) {}

    public function execute(int $photoId): void
    {
        $photo = $this->productRepository->findPhotoById($photoId);
        if ($photo === null) {
            throw new \RuntimeException("Photo introuvable ($photoId).");
        }

        // Suppression du fichier physique
        $filePath = $this->config->getParam('app.root_dir') . '/public/assets/uploads/products/' . $photo->getFilename();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->productRepository->deletePhoto($photoId);
    }
}
