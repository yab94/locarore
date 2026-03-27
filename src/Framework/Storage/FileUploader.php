<?php

declare(strict_types=1);

namespace Rore\Framework\Storage;

class FileUploader implements FileManagerInterface
{
    private array $allowedTypes;

    public function __construct(
        private string $uploadDir,
        private int    $maxSize,
        string         $allowedTypes,
    ) {
        $this->allowedTypes = array_map('trim', explode(',', $allowedTypes));

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Valide et déplace le fichier uploadé. Retourne le chemin absolu du fichier.
     *
     * @param  array $file  Entrée $_FILES['field']
     * @return string       Chemin absolu du fichier déposé
     * @throws \RuntimeException
     */
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

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($ext === '') {
            throw new \RuntimeException("Impossible de déterminer l'extension du fichier.");
        }

        $filename    = uniqid('file_', true) . '.' . $ext;
        $destination = $this->uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Impossible de déplacer le fichier uploadé.');
        }

        return $destination;
    }

    public function delete(string $filename): void
    {
        $filePath = $this->uploadDir . '/' . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
