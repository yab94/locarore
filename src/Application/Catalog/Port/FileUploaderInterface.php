<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\Port;

interface FileUploaderInterface extends FileManagerInterface
{
    /**
     * @param array  $file                Entrée $_FILES['field']
     * @param string $relativeDestination Chemin relatif à baseDir
     */
    public function upload(array $file, string $relativeDestination): void;
}
