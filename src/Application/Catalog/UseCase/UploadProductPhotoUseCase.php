<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use RRB\Di\BindConfig;
use Rore\Application\Catalog\Port\FileUploaderInterface;
use Rore\Application\Catalog\Port\ImageManagerInterface;
use Rore\Domain\Catalog\Entity\ProductPhoto;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Infrastructure\File\FileUploaderAdapter;
use Rore\Infrastructure\File\ImageManagerAdapter;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use RRB\Di\BindAdapter;

class UploadProductPhotoUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepository::class)]
        private ProductRepositoryInterface $productRepository,
        #[BindAdapter(FileUploaderAdapter::class)]
        private FileUploaderInterface      $fileUploader,
        #[BindAdapter(ImageManagerAdapter::class)]
        private ImageManagerInterface      $imageManager,
        #[BindConfig('upload.max_width')]
        private int                        $maxWidth,
        #[BindConfig('upload.max_height')]
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
