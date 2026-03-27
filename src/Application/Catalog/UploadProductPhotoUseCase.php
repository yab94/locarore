<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Framework\Bootstrap\Config;
use Rore\Framework\Di\Bind;
use Rore\Framework\Storage\FileUploader;
use Rore\Framework\Storage\ImageManager;
use Rore\Domain\Catalog\Entity\ProductPhoto;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;

class UploadProductPhotoUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        #[Bind('baseDir', static function (Config $c): string { return $c->getString('upload.base_path') . $c->getString('upload.upload_path'); })]
        #[Bind('maxSize', static function (Config $c): int { return $c->getInt('upload.max_size'); })]
        #[Bind('allowedTypes', static function (Config $c): string { return $c->getString('upload.allowed_types'); })]
        private FileUploader               $fileUploader,
        #[Bind('baseDir', static function (Config $c): string { return $c->getString('upload.base_path') . $c->getString('upload.upload_path'); })]
        private ImageManager               $imageManager,
        #[Bind(static function (Config $c): int { return $c->getInt('upload.max_width'); })]
        private int                        $maxWidth,
        #[Bind(static function (Config $c): int { return $c->getInt('upload.max_height'); })]
        private int                        $maxHeight,
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

        $ext      = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $filename = 'photo_'.bin2hex(random_bytes(8)) . '.' . $ext;
        $this->fileUploader->upload($file, $filename);

        $this->imageManager->resize($filename, $this->maxWidth, $this->maxHeight);
        $filename = $this->imageManager->convertToWebp($filename);

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
