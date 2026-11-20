<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\MediaThumbnailGenerator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Signature('cms:retry-media-thumbnails')]
#[Description('Buat ulang thumbnail gambar yang gagal saat upload')]
class RetryMediaThumbnails extends Command
{
    public function handle(MediaThumbnailGenerator $thumbnailGenerator): int
    {
        $failed = 0;
        Media::query()->where('mime_type', 'like', 'image/%')->where('path', 'like', 'images/%')->orderBy('id')->chunkById(100, function ($mediaItems) use ($thumbnailGenerator, &$failed): void {
            foreach ($mediaItems as $media) {
                try {
                    $thumbnailGenerator->create($media);
                } catch (Throwable $exception) {
                    $failed++;
                    Log::warning('Retry thumbnail media gagal; akan dicoba pada jadwal berikutnya.', [
                        'media_id' => $media->id,
                        'path' => $media->path,
                        'exception' => $exception->getMessage(),
                    ]);
                }
            }
        });

        $this->info("Retry thumbnail selesai; kegagalan: {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
