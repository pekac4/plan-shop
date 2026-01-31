<?php

namespace App\Services\Recipes;

use App\ImageResizer;
use App\Models\Recipe;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecipeCoverService
{
    public function ensureCover(Recipe $recipe): void
    {
        if ($recipe->cover_image_path || ! function_exists('imagecreatetruecolor')) {
            return;
        }

        $disk = Storage::disk('public');
        $directory = 'recipes/'.$recipe->id;
        $disk->makeDirectory($directory);

        $coverPath = $directory.'/cover.jpg';
        $thumbPath = $directory.'/cover-thumb.jpg';

        $coverFullPath = $disk->path($coverPath);
        $thumbFullPath = $disk->path($thumbPath);

        $image = imagecreatetruecolor(1200, 800);
        $bg = imagecolorallocate($image, 232, 245, 236);
        $leaf = imagecolorallocate($image, 90, 171, 116);
        $leafDark = imagecolorallocate($image, 51, 120, 77);
        $accent = imagecolorallocate($image, 248, 213, 126);

        imagefilledrectangle($image, 0, 0, 1200, 800, $bg);
        imagefilledellipse($image, 260, 260, 340, 380, $leaf);
        imagefilledellipse($image, 460, 320, 280, 330, $leafDark);
        imagefilledellipse($image, 940, 240, 320, 360, $leaf);
        imagefilledellipse($image, 860, 360, 260, 280, $leafDark);
        imagefilledellipse($image, 640, 560, 360, 220, $accent);

        $hash = Str::of($recipe->title)->upper()->substr(0, 14);
        imagestring($image, 5, 36, 36, $hash->toString(), $leafDark);

        imagejpeg($image, $coverFullPath, 86);
        imagedestroy($image);

        ImageResizer::resizeToFit($coverFullPath, $thumbFullPath, 720, 480);

        $recipe->forceFill([
            'cover_image_path' => $coverPath,
            'cover_thumbnail_path' => $thumbPath,
        ])->save();
    }
}
