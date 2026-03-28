<?php

declare(strict_types=1);

namespace Rore\Infrastructure\File;

use RRB\Di\BindConfig;
use RRB\File\ImageManager;
use Rore\Application\Catalog\Port\ImageManagerInterface;

final class ImageManagerAdapter extends ImageManager implements ImageManagerInterface
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
