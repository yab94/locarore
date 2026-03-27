<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Framework\Bootstrap\Config;
use Rore\Framework\Di\Bind;
use Rore\Framework\File\FileManager;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;

class DeleteProductPhotoUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        #[Bind('baseDir', static function (Config $c): string { return $c->getString('upload.base_path') . $c->getString('upload.upload_path'); })]
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
