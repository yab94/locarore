<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Storage;

use Rore\Infrastructure\Config\Config;

class FileUploader
{
    private string $uploadDir;
    private int    $maxSize;
    private array  $allowedTypes;

    public function __construct(Config $config)
    {
        $this->uploadDir    = BASE_PATH . '/public' . $config->getStringParam('upload.upload_path');
        $this->maxSize      = (int) $config->getStringParam('upload.max_size');
        $this->allowedTypes = array_map('trim', explode(',', $config->getStringParam('upload.allowed_types')));

         // Crée le dossier d'upload s'il n'existe pas
         if (!is_dir($this->uploadDir)) {
             mkdir($this->uploadDir, 0755, true);
         }
    }

    /**
     * Traite un fichier uploadé et retourne son nom final.
     *
     * @param  array $file  Entrée $_FILES['field']
     * @return string       Nom du fichier (sans chemin)
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

        $ext = match($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => throw new \RuntimeException("Extension inconnue pour $mimeType."),
        };

        $filename    = uniqid('photo_', true) . '.' . $ext;
        $destination = $this->uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Impossible de déplacer le fichier uploadé.');
        }

        return $filename;
    }
}
