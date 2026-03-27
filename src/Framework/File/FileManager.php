<?php

declare(strict_types=1);

namespace Rore\Framework\File;

class FileManager
{
    public function __construct(protected readonly string $baseDir) {}

    /**
     * Détecte le type MIME depuis un chemin absolu. Usage interne et sous-classes.
     */
    protected function mimeType(string $absolutePath): string
    {
        return (new \finfo(FILEINFO_MIME_TYPE))->file($absolutePath);
    }

    /**
     * Détecte le type MIME d'un fichier par son chemin relatif à baseDir.
     */
    public function getMimeType(string $relativePath): string
    {
        return $this->mimeType($this->baseDir . '/' . $relativePath);
    }

    /**
     * Retourne l'extension (sans le point) d'un fichier par son chemin relatif à baseDir.
     */
    public function getExtension(string $relativePath): string
    {
        return strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
    }

    /**
     * Supprime un fichier par son chemin relatif à baseDir.
     * Vérifie via realpath que le fichier résolu appartient bien à baseDir (protection traversal).
     */
    public function delete(string $relativePath): void
    {
        $resolved = realpath($this->baseDir . '/' . $relativePath);

        if ($resolved === false || !str_starts_with($resolved, $this->baseDir . '/')) {
            throw new \RuntimeException("Chemin non autorisé : $relativePath.");
        }

        unlink($resolved);
    }

    /**
     * Renomme un fichier. Les deux chemins sont relatifs à baseDir.
     * Vérifie via realpath que source et destination appartiennent bien à baseDir (protection traversal).
     */
    public function rename(string $oldRelativePath, string $newRelativePath): void
    {
        $src = realpath($this->baseDir . '/' . $oldRelativePath);

        if ($src === false || !str_starts_with($src, $this->baseDir . '/')) {
            throw new \RuntimeException("Chemin source non autorisé : $oldRelativePath.");
        }

        $dst    = $this->baseDir . '/' . $newRelativePath;
        $dstDir = realpath(dirname($dst));

        if ($dstDir === false || !str_starts_with($dstDir . '/', $this->baseDir . '/')) {
            throw new \RuntimeException("Chemin destination non autorisé : $newRelativePath.");
        }

        if (!rename($src, $dst)) {
            throw new \RuntimeException("Impossible de renommer $oldRelativePath en $newRelativePath.");
        }
    }
}
