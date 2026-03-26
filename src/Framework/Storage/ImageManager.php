<?php

declare(strict_types=1);

namespace Rore\Framework\Storage;

class ImageManager
{
    private function getGDImage($imagePath): ?\GdImage
    {
        if (!extension_loaded('gd')) {
            return null;
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($imagePath);

        return match($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($imagePath),
            'image/png'  => imagecreatefrompng($imagePath),
            'image/bmp'  => function_exists('imagecreatefrombmp') ? imagecreatefrombmp($imagePath) : throw new \RuntimeException("Format d'image non supporté : $mimeType."),
            'image/gif'  => imagecreatefromgif($imagePath),
            'image/tiff' => function_exists('imagecreatefromtiff') ? imagecreatefromtiff($imagePath) : throw new \RuntimeException("Format d'image non supporté : $mimeType."), 
            default      => throw new \RuntimeException("Format d'image non supporté : $mimeType."),
        };
    }

    public function resizeImage(string $imagePath, int $maxWidth, int $maxHeight): void
    {
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($imagePath);
        
        [$width, $height] = getimagesize($imagePath);
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return; // Pas besoin de resize
        }

        $srcImg = $this->getGDImage($imagePath);
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
            'image/jpeg' => imagejpeg($dstImg, $imagePath, 90),
            'image/png'  => imagepng($dstImg, $imagePath, 6),
            'image/bmp'  => function_exists('imagebmp') ? imagebmp($dstImg, $imagePath) : throw new \RuntimeException("Format d'image non supporté : $mimeType."),
            'image/gif'  => imagegif($dstImg, $imagePath),
            'image/tiff' => null, // Pas de support natif
        };
    }

    public function convertToWebp(string $imagePath): string
    {
        $img = $this->getGDImage($imagePath);
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($imagePath);

        // Préserver la transparence PNG et GIF
        if (in_array($mimeType, ['image/png', 'image/gif'], true)) {
            imagepalettetotruecolor($img);
            imagealphablending($img, true);
            imagesavealpha($img, true);
        }

        $webpPath = preg_replace('/\.([a-zA-Z0-9]+)$/i', '.webp', $imagePath);
        imagewebp($img, $webpPath, 82);
        unlink($imagePath);

        return basename((string) $webpPath);
    }
}
