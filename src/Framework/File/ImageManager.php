<?php

declare(strict_types=1);

namespace Rore\Framework\File;

class ImageManager extends FileManager
{
    /**
     * Redimensionne une image si elle dépasse les dimensions max (conserve le ratio).
     *
     * @param string $filename  Nom du fichier relatif à baseDir
     */
    public function resize(string $filename, int $maxWidth, int $maxHeight): void
    {
        $filePath = $this->baseDir . '/' . $filename;
        $mimeType = $this->mimeType($filePath);
        [$width, $height] = getimagesize($filePath);

        if ($width <= $maxWidth && $height <= $maxHeight) {
            return;
        }

        $src = $this->loadGdImage($filePath, $mimeType);
        if ($src === null) return;

        $ratio     = min($maxWidth / $width, $maxHeight / $height);
        $newWidth  = (int) round($width * $ratio);
        $newHeight = (int) round($height * $ratio);

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        if (in_array($mimeType, ['image/png', 'image/gif'], true)) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        match($mimeType) {
            'image/jpeg' => imagejpeg($dst, $filePath, 90),
            'image/png'  => imagepng($dst, $filePath, 6),
            'image/bmp'  => function_exists('imagebmp') ? imagebmp($dst, $filePath) : null,
            'image/gif'  => imagegif($dst, $filePath),
            'image/tiff' => null,
        };
    }

    /**
     * Convertit une image en WebP. Supprime l'original et retourne le nouveau nom de fichier.
     * Si l'image est déjà en WebP, retourne simplement le nom sans rien faire.
     *
     * @param string $filename  Nom du fichier relatif à baseDir
     * @return string           Nom du fichier WebP résultant
     */
    public function convertToWebp(string $filename): string
    {
        $filePath = $this->baseDir . '/' . $filename;
        $mimeType = $this->mimeType($filePath);

        if ($mimeType === 'image/webp') {
            return $filename;
        }

        $img = $this->loadGdImage($filePath, $mimeType);
        if ($img === null) {
            return $filename;
        }

        if (in_array($mimeType, ['image/png', 'image/gif'], true)) {
            imagepalettetotruecolor($img);
            imagealphablending($img, true);
            imagesavealpha($img, true);
        }

        $webpPath = preg_replace('/\.[a-zA-Z0-9]+$/', '.webp', $filePath);
        imagewebp($img, $webpPath, 82);
        unlink($filePath);

        return basename((string) $webpPath);
    }

    private function loadGdImage(string $filePath, string $mimeType): ?\GdImage
    {
        $img = match($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($filePath),
            'image/png'  => imagecreatefrompng($filePath),
            'image/bmp'  => function_exists('imagecreatefrombmp') ? imagecreatefrombmp($filePath) : null,
            'image/gif'  => imagecreatefromgif($filePath),
            'image/tiff' => function_exists('imagecreatefromtiff') ? imagecreatefromtiff($filePath) : null,
            default      => null,
        };

        return ($img instanceof \GdImage) ? $img : null;
    }
}
