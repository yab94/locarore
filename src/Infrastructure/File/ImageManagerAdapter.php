<?php

declare(strict_types=1);

namespace Rore\Infrastructure\File;

use RRB\File\ImageManager;
use Rore\Application\Catalog\Port\ImageManagerInterface;

final class ImageManagerAdapter extends ImageManager implements ImageManagerInterface
{
    public function __construct(
        string $basePath,
        string $uploadPath,
    ) {
        parent::__construct($basePath . $uploadPath);
    }
}
