<?php

namespace App;

class ImageResizer
{
    public static function resizeToFit(string $sourcePath, string $targetPath, int $maxWidth, int $maxHeight, int $quality = 82): bool
    {
        $imageInfo = @getimagesize($sourcePath);

        if ($imageInfo === false) {
            return false;
        }

        [$width, $height] = $imageInfo;
        $mime = $imageInfo['mime'] ?? null;

        if (! $mime) {
            return false;
        }

        $ratio = min($maxWidth / max(1, $width), $maxHeight / max(1, $height), 1);
        $targetWidth = (int) round($width * $ratio);
        $targetHeight = (int) round($height * $ratio);

        $source = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default => null,
        };

        if (! $source) {
            return false;
        }

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        if (in_array($mime, ['image/png', 'image/webp'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height
        );

        $directory = dirname($targetPath);
        if (! is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        $saved = match ($mime) {
            'image/jpeg', 'image/jpg' => imagejpeg($canvas, $targetPath, $quality),
            'image/png' => imagepng($canvas, $targetPath, 6),
            'image/webp' => imagewebp($canvas, $targetPath, $quality),
            default => false,
        };

        imagedestroy($source);
        imagedestroy($canvas);

        return $saved;
    }
}
