<?php

declare(strict_types=1);

namespace Rore\Framework\Storage;

use Rore\Framework\Storage\FileManagerInterface;

class FileManager implements FileManagerInterface
{
    public function __construct(
        private string $baseDir,
        private int    $maxSize,
        private array  $allowedTypes,
    ) {
        if (!is_dir($this->baseDir)) {
            mkdir($this->baseDir, 0755, true);
        }
    }

    public function upload(array $file): string
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Erreur lors de l\'upload (code : ' . ($file['error'] ?? '?') . ').');
        }

        if ($file['size'] > $this->maxSize) {
            $max = number_format($this->maxSize / 1024 / 1024, 1);
            throw new \RuntimeException("Fichier trop volumineux. Maximum : {$max} Mo.");
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $this->allowedTypes, true)) {
            throw new \RuntimeException("Type de fichier non autorisé : $mimeType.");
        }

        $filename    = $file['name'];
        $destination = $this->baseDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Impossible de déplacer le fichier uploadé.');
        }

        return $filename;
    }

    public function delete(string $filename): void
    {
        $filePath = $this->baseDir . '/' . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
