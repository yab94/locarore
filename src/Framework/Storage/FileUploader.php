<?php

declare(strict_types=1);

namespace Rore\Framework\Storage;

class FileUploader extends FileManager
{
    private array $allowedTypes;

    public function __construct(
        string $baseDir,
        private int $maxSize,
        string $allowedTypes,
    ) {
        parent::__construct($baseDir);
        $this->allowedTypes = array_map('trim', explode(',', $allowedTypes));

        if (!is_dir($this->baseDir)) {
            mkdir($this->baseDir, 0755, true);
        }
    }

    /**
     * Valide et déplace le fichier uploadé vers la destination indiquée.
     *
     * @param  array  $file                Entrée $_FILES['field']
     * @param  string $relativeDestination Chemin relatif à baseDir
     * @throws \RuntimeException
     */
    public function upload(array $file, string $relativeDestination): void
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Erreur lors de l\'upload (code : ' . ($file['error'] ?? '?') . ').');
        }

        if ($file['size'] > $this->maxSize) {
            $max = number_format($this->maxSize / 1024 / 1024, 1);
            throw new \RuntimeException("Fichier trop volumineux. Maximum : {$max} Mo.");
        }

        $mimeType = $this->mimeType($file['tmp_name']);

        if (!in_array($mimeType, $this->allowedTypes, true)) {
            throw new \RuntimeException("Type de fichier non autorisé : $mimeType.");
        }

        $absoluteDestination = $this->baseDir . '/' . $relativeDestination;
        $dstDir = realpath(dirname($absoluteDestination));

        if ($dstDir === false || !str_starts_with($dstDir . '/', $this->baseDir . '/')) {
            throw new \RuntimeException("Destination non autorisée : $relativeDestination.");
        }

        if (!move_uploaded_file($file['tmp_name'], $absoluteDestination)) {
            throw new \RuntimeException('Impossible de déplacer le fichier uploadé.');
        }
    }
}
