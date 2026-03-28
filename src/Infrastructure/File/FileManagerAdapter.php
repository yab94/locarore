<?php

declare(strict_types=1);

namespace Rore\Infrastructure\File;

use RRB\Di\BindConfig;
use RRB\File\FileManager;
use Rore\Application\Catalog\Port\FileManagerInterface;

final class FileManagerAdapter extends FileManager implements FileManagerInterface
{
    public function __construct(
        #[BindConfig('upload.base_path')]
        string $basePath,
        #[BindConfig('upload.upload_path')]
        string $uploadPath,
    ) {
        parent::__construct($basePath . $uploadPath);
    }
}
