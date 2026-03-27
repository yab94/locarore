<?php

declare(strict_types=1);

namespace Rore\Infrastructure\File;

use RRB\Bootstrap\Config;
use RRB\Di\Bind;
use RRB\File\ImageManager;
use Rore\Application\Catalog\Port\ImageManagerInterface;

final class ImageManagerAdapter extends ImageManager implements ImageManagerInterface
{
    public function __construct(
        #[Bind(static function (Config $c): string {
            return $c->getString('upload.base_path') . $c->getString('upload.upload_path');
        })]
        string $baseDir,
    ) {
        parent::__construct($baseDir);
    }
}
