<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use RRB\Bootstrap\Config;
use RRB\Di\Bind;
use RRB\File\FileManager;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use RRB\Di\BindAdapter;

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
