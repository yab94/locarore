<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Framework\Storage\FileManagerInterface;
use Rore\Framework\Storage\ImageManager;
use Rore\Domain\Catalog\Entity\ProductPhoto;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;

class UploadProductPhotoUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private FileManagerInterface       $fileManager,
        private ImageManager               $imageManager,
    ) {}

    /**
     * @param array $file  Entrée $_FILES
     */
    public function execute(int $productId, array $file, string $description = ''): void
    {
        $product = $this->productRepository->findById($productId);
        if ($product === null) {
            throw new \RuntimeException("Produit introuvable ($productId).");
        }

        $existingPhotos = $this->productRepository->findPhotosByProductId($productId);
        $sortOrder = count($existingPhotos);

        $filePath = $this->fileManager->upload($file);

        $this->imageManager->resize($filePath, 1200, 1200);
        $filePath = rtrim(dirname($filePath), '/') . '/' . $this->imageManager->convertToWebp($filePath);
        $filename = basename($filePath);

        $photo = new ProductPhoto(
            id:          null,
            productId:   $productId,
            filename:    $filename,
            sortOrder:   $sortOrder,
            createdAt:   new \DateTimeImmutable(),
            description: $description !== '' ? $description : null,
        );

        $this->productRepository->savePhoto($photo);
    }
}
