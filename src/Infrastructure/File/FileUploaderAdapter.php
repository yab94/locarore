<?php

declare(strict_types=1);

namespace Rore\Infrastructure\File;

use RRB\File\FileUploader;
use Rore\Application\Catalog\Port\FileUploaderInterface;

final class FileUploaderAdapter extends FileUploader implements FileUploaderInterface
{
    public function __construct(
        string $basePath,
        string $uploadPath,
        int $maxSize,
        string $allowedTypes,
    ) {
        parent::__construct($basePath . $uploadPath, $maxSize, $allowedTypes);
    }
}
