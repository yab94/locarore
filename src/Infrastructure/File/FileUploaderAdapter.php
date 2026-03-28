<?php

declare(strict_types=1);

namespace Rore\Infrastructure\File;

use RRB\Di\BindConfig;
use RRB\File\FileUploader;
use Rore\Application\Catalog\Port\FileUploaderInterface;

final class FileUploaderAdapter extends FileUploader implements FileUploaderInterface
{
    public function __construct(
        #[BindConfig('upload.base_path')]
        string $basePath,
        #[BindConfig('upload.upload_path')]
        string $uploadPath,
        #[BindConfig('upload.max_size')]
        int $maxSize,
        #[BindConfig('upload.allowed_types')]
        string $allowedTypes,
    ) {
        parent::__construct($basePath . $uploadPath, $maxSize, $allowedTypes);
    }
}
