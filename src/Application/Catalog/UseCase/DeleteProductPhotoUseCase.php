<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use RRB\Bootstrap\Config;
use RRB\Di\Bind;
use Rore\Application\Catalog\Port\FileManagerInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Infrastructure\File\FileManagerAdapter;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use RRB\Di\BindAdapter;

class DeleteProductPhotoUseCase
{
    public function __construct(
        #[BindAdapter(MySqlProductRepository::class)]
        private ProductRepositoryInterface $productRepository,
        #[Bind(static function (Config $c): FileManagerInterface {
            return new FileManagerAdapter(
                baseDir: $c->getString('upload.base_path') . $c->getString('upload.upload_path'),
            );
        })]
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
