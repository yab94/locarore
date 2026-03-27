<?php

declare(strict_types=1);

namespace Rore\Infrastructure\File;

use RRB\Bootstrap\Config;
use RRB\Di\Bind;
use RRB\Di\BindConfig;
use RRB\File\FileUploader;
use Rore\Application\Catalog\Port\FileUploaderInterface;

final class FileUploaderAdapter extends FileUploader implements FileUploaderInterface
{
    public function __construct(
        #[Bind(static function (Config $c): string {
            return $c->getString('upload.base_path') . $c->getString('upload.upload_path');
        })]
        string $baseDir,
        #[BindConfig('upload.max_size')]
        int $maxSize,
        #[BindConfig('upload.allowed_types')]
        string $allowedTypes,
    ) {
        parent::__construct($baseDir, $maxSize, $allowedTypes);
    }
}
