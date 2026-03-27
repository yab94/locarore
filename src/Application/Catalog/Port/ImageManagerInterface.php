<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\Port;

interface ImageManagerInterface extends FileManagerInterface
{
    public function resize(string $filename, int $maxWidth, int $maxHeight): void;

    /**
     * Convertit en WebP, supprime l'original et retourne le nouveau nom de fichier.
     */
    public function convertToWebp(string $filename): string;
}
