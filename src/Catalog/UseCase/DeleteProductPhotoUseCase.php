<?php

declare(strict_types=1);

namespace Rore\Catalog\UseCase;

use Rore\Framework\Bootstrap\Config;
use Rore\Framework\Di\Bind;
use Rore\Framework\File\FileManager;
use Rore\Catalog\Port\ProductRepositoryInterface;
use Rore\Catalog\Adapter\MySqlProductRepository;
use Rore\Framework\Di\BindAdapter;

class DeleteProductPhotoUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepository::class)]
        private ProductRepositoryInterface $productRepository,
        #[Bind(static function (Config $c): FileManager {
            return new FileManager(
                baseDir: $c->getString('upload.base_path') . $c->getString('upload.upload_path'),
            );
        })]
        private FileManager                $fileManager,
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
