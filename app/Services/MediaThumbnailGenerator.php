<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaThumbnailGenerator
{
    public function thumbnailPath(Media $media): string
    {
        return 'images/thumbnails/'.basename($media->path);
    }

    public function create(Media $media): bool
    {
        $disk = Storage::disk('public');
        $thumbnailPath = $this->thumbnailPath($media);
        if ($disk->exists($thumbnailPath)) {
            return true;
        }

        $source = imagecreatefromstring($disk->get($media->path));
        if ($source === false) {
            throw new RuntimeException('Gambar sumber tidak dapat dibaca untuk dibuatkan thumbnail.');
        }

        try {
            $width = imagesx($source);
            $height = imagesy($source);
            $extension = match ($media->mime_type) {
                'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg',
            };
            $scale = min(1, 640 / max($width, $height));
            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));
            $target = imagecreatetruecolor($targetWidth, $targetHeight);

            if ($extension !== 'jpg') {
                imagealphablending($target, false);
                imagesavealpha($target, true);
                imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
            }

            imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
            ob_start();
            match ($extension) {
                'png' => imagepng($target, null, 8),
                'webp' => imagewebp($target, null, 82),
                default => imagejpeg($target, null, 85),
            };
            $encoded = (string) ob_get_clean();
            imagedestroy($target);
            if ($encoded === '') {
                throw new RuntimeException('Thumbnail gagal dienkode.');
            }

            if (! $disk->put($thumbnailPath, $encoded)) {
                throw new RuntimeException('Thumbnail gagal disimpan ke Media Library.');
            }

            return true;
        } finally {
            imagedestroy($source);
        }
    }
}
