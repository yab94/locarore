<?php

declare(strict_types=1);

namespace Rore\Framework\Storage;

use Rore\Framework\Storage\FileManagerInterface;

class FileUploader implements FileManagerInterface
{
    private array $allowedTypes;

    /**
     * @param string $uploadDir
     * @param int $maxSize
     * @param string $allowedTypes
     * @param array|null $maxDimensions [width, height] (ex: [1200, 1200])
     */
    public function __construct(
        private string $uploadDir,
        private int    $maxSize,
        string         $allowedTypes,
        private int $maxWidth,
        private int $maxHeight,
    ) {
        $this->allowedTypes = array_map('trim', explode(',', $allowedTypes));

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

            $ext = match($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/bmp'  => 'bmp',
                'image/gif'  => 'gif',
                'image/tiff' => 'tiff',
                default      => throw new \RuntimeException("Extension inconnue pour $mimeType."),
            };

        $filename    = uniqid('photo_', true) . '.' . $ext;
        $destination = $this->uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Impossible de déplacer le fichier uploadé.');
        }

        // Resize image if needed (except webp, already optimized)
            if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/bmp', 'image/gif', 'image/tiff'], true)) {
                $this->resizeImage($destination, $mimeType, $this->maxWidth, $this->maxHeight);
            }

        // Conversion WebP via GD natif (sauf si déjà webp)
        if ($mimeType !== 'image/webp') {
            $filename = $this->convertToWebp($destination, $mimeType);
        }

        return $filename;
    }
    /**
     * Redimensionne une image à l'intérieur des dimensions max (conserve le ratio).
     */
    private function resizeImage(string $filePath, string $mimeType, int $maxWidth, int $maxHeight): void
    {
        [$width, $height] = getimagesize($filePath);
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return; // Pas besoin de resize
        }

        $srcImg = match($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($filePath),
            'image/png'  => imagecreatefrompng($filePath),
            'image/bmp'  => function_exists('imagecreatefrombmp') ? imagecreatefrombmp($filePath) : null,
            'image/gif'  => imagecreatefromgif($filePath),
            'image/tiff' => function_exists('imagecreatefromtiff') ? imagecreatefromtiff($filePath) : null, // GD ne supporte pas nativement TIFF
            default      => null,
        };
        if (!$srcImg) return;

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth  = (int) round($width * $ratio);
        $newHeight = (int) round($height * $ratio);

        $dstImg = imagecreatetruecolor($newWidth, $newHeight);
        if ($mimeType === 'image/png') {
            imagealphablending($dstImg, false);
            imagesavealpha($dstImg, true);
        }
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        match($mimeType) {
            'image/jpeg' => imagejpeg($dstImg, $filePath, 90),
            'image/png'  => imagepng($dstImg, $filePath, 6),
            'image/bmp'  => function_exists('imagebmp') ? imagebmp($dstImg, $filePath) : null,
            'image/gif'  => imagegif($dstImg, $filePath),
            'image/tiff' => null, // Pas de support natif
        };
    }

    /**
     * Convertit une image JPG/PNG en WebP, supprime l'original.
     * Retourne le nouveau nom de fichier (*.webp).
     */
    private function convertToWebp(string $sourcePath, string $mimeType): string
    {
        $img = match($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png'  => imagecreatefrompng($sourcePath),
            'image/bmp'  => function_exists('imagecreatefrombmp') ? imagecreatefrombmp($sourcePath) : null,
            'image/gif'  => imagecreatefromgif($sourcePath),
            'image/tiff' => function_exists('imagecreatefromtiff') ? imagecreatefromtiff($sourcePath) : null, // GD ne supporte pas nativement TIFF
            default      => null,
        };

        if ($img === null || $img === false) {
            // GD indisponible ou format inconnu — on garde l'original
            return basename($sourcePath);
        }

        // Préserver la transparence PNG et GIF
        if (in_array($mimeType, ['image/png', 'image/gif'], true)) {
            imagepalettetotruecolor($img);
            imagealphablending($img, true);
            imagesavealpha($img, true);
        }

        $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $sourcePath);
        imagewebp($img, $webpPath, 82);
        unlink($sourcePath);

        return basename((string) $webpPath);
    }

    public function delete(string $filename): void
    {
        $filePath = $this->uploadDir . '/' . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
