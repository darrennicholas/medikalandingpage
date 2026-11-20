<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileInfo;

#[Signature('cms:migrate-public-media')]
#[Description('Pindahkan gambar, video, dan brosur lama ke storage/app/public')]
class MigratePublicMedia extends Command
{
    /** @var array<string, string> */
    private array $mediaPaths = [];

    public function handle(): int
    {
        $this->moveLegacyMedia();
        $this->moveSiteImages();
        $this->moveBrochures();
        $this->updateMediaRecords();
        $this->registerStoredMedia();
        $this->removeEmptySourceDirectories();

        $this->info('Migrasi aset selesai. File gambar, video, dan brosur sekarang berada di storage/app/public.');

        return self::SUCCESS;
    }

    private function moveLegacyMedia(): void
    {
        foreach ([
            public_path('images/media'),
            storage_path('app/public/media'),
            storage_path('app/private/cms-media'),
        ] as $sourceDirectory) {
            if (! is_dir($sourceDirectory)) {
                continue;
            }

            foreach ((new Filesystem)->allFiles($sourceDirectory) as $file) {
                $relativePath = str_replace('\\', '/', $file->getRelativePathname());
                if ($file->getFilename() === '.gitignore') {
                    continue;
                }

                $isThumbnail = str_starts_with($relativePath, 'thumbnails/');
                $targetPath = $isThumbnail
                    ? 'images/thumbnails/'.$file->getFilename()
                    : $this->mediaDirectory($file).'/'.$file->getFilename();
                $migratedPath = $this->moveFile($file->getPathname(), $targetPath);
                if (! $isThumbnail) {
                    $this->mediaPaths[$file->getFilename()] = $migratedPath;
                }
            }
        }
    }

    private function moveSiteImages(): void
    {
        $sourceDirectory = public_path('images');
        if (! is_dir($sourceDirectory)) {
            return;
        }

        foreach ((new Filesystem)->allFiles($sourceDirectory) as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            if ($file->getFilename() === '.gitignore' || str_starts_with($relativePath, 'media/')) {
                continue;
            }

            $this->moveFile($file->getPathname(), 'images/site-assets/'.$relativePath);
        }
    }

    private function moveBrochures(): void
    {
        $sourceDirectory = public_path('brochures');
        if (! is_dir($sourceDirectory)) {
            return;
        }

        $filesystem = new Filesystem;
        foreach ($filesystem->allFiles($sourceDirectory) as $file) {
            if ($file->getFilename() === '.gitignore') {
                continue;
            }

            $mimeType = $filesystem->mimeType($file->getPathname()) ?: '';
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $targetPath = str_starts_with($mimeType, 'image/')
                ? 'images/site-assets/brochures/'.$relativePath
                : 'brochures/'.$relativePath;
            $this->moveFile($file->getPathname(), $targetPath);
        }
    }

    private function moveFile(string $sourcePath, string $targetPath): string
    {
        $disk = Storage::disk('public');
        $targetPath = $this->uniqueTargetPath($targetPath, $sourcePath);
        $absoluteTarget = $disk->path($targetPath);
        (new Filesystem)->ensureDirectoryExists(dirname($absoluteTarget));

        if (is_file($absoluteTarget)) {
            if (hash_file('sha256', $sourcePath) === hash_file('sha256', $absoluteTarget)) {
                unlink($sourcePath);

                return $targetPath;
            }

            throw new RuntimeException('File tujuan berisi data berbeda: '.$targetPath);
        }

        if (! (new Filesystem)->copy($sourcePath, $absoluteTarget)) {
            throw new RuntimeException('Gagal menyalin file '.$sourcePath);
        }
        if (hash_file('sha256', $sourcePath) !== hash_file('sha256', $absoluteTarget)) {
            unlink($absoluteTarget);

            throw new RuntimeException('Verifikasi checksum gagal untuk '.$sourcePath);
        }
        unlink($sourcePath);

        return $targetPath;
    }

    private function uniqueTargetPath(string $targetPath, string $sourcePath): string
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($targetPath) || hash_file('sha256', $sourcePath) === hash_file('sha256', $disk->path($targetPath))) {
            return $targetPath;
        }

        $directory = dirname($targetPath);
        $extension = pathinfo($targetPath, PATHINFO_EXTENSION);
        $stem = pathinfo($targetPath, PATHINFO_FILENAME);
        $suffix = substr(hash_file('sha256', $sourcePath), 0, 10);

        return $directory.'/'.$stem.'-'.$suffix.($extension === '' ? '' : '.'.$extension);
    }

    private function mediaDirectory(SplFileInfo $file): string
    {
        $mimeType = (new Filesystem)->mimeType($file->getPathname()) ?: '';

        return str_starts_with($mimeType, 'video/') ? 'videos' : 'images';
    }

    private function updateMediaRecords(): void
    {
        DB::transaction(function (): void {
            Media::withTrashed()->orderBy('id')->chunkById(100, function ($records): void {
                foreach ($records as $media) {
                    $path = $this->mediaPaths[$media->file_name] ?? null;
                    if ($path === null) {
                        $path = $this->migratedStoredPath($media);
                    }
                    if ($path === null) {
                        $directory = str_starts_with($media->mime_type, 'video/') ? 'videos' : 'images';
                        $candidate = $directory.'/'.$media->file_name;
                        if (Storage::disk('public')->exists($candidate)) {
                            $path = $candidate;
                        }
                    }

                    if ($path !== null && $media->path !== $path) {
                        $media->forceFill(['path' => $path])->save();
                    }
                }
            });
        });
    }

    private function migratedStoredPath(Media $media): ?string
    {
        $disk = Storage::disk('public');
        $storedPath = str_replace('\\', '/', ltrim($media->path, '/'));
        if (str_starts_with($storedPath, 'private/')) {
            $storedPath = substr($storedPath, strlen('private/'));
        }

        if ($disk->exists($storedPath)) {
            return $storedPath;
        }

        foreach (['public/images/' => 'images/site-assets/', 'images/' => 'images/site-assets/'] as $prefix => $targetPrefix) {
            if (str_starts_with($storedPath, $prefix)) {
                $candidate = $targetPrefix.substr($storedPath, strlen($prefix));
                if ($disk->exists($candidate)) {
                    return $candidate;
                }
            }
        }

        foreach (['public/brochures/' => 'images/site-assets/brochures/', 'brochures/' => 'images/site-assets/brochures/'] as $prefix => $targetPrefix) {
            if (str_starts_with($storedPath, $prefix) && str_starts_with($media->mime_type, 'image/')) {
                $candidate = $targetPrefix.substr($storedPath, strlen($prefix));
                if ($disk->exists($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private function registerStoredMedia(): void
    {
        $disk = Storage::disk('public');
        foreach (['images', 'videos'] as $directory) {
            foreach ($disk->allFiles($directory) as $path) {
                if (str_starts_with($path, 'images/thumbnails/')) {
                    continue;
                }

                $mimeType = $disk->mimeType($path) ?: '';
                if (! str_starts_with($mimeType, 'image/') && ! str_starts_with($mimeType, 'video/')) {
                    continue;
                }

                $media = Media::withTrashed()->where('path', $path)->first();
                if ($media !== null) {
                    if ($media->trashed()) {
                        $media->restore();
                    }

                    continue;
                }

                $absolutePath = $disk->path($path);
                $dimensions = str_starts_with($mimeType, 'image/') ? @getimagesize($absolutePath) : false;
                $fileName = Str::slug(pathinfo($path, PATHINFO_FILENAME)).'-'.substr(hash('sha256', $path), 0, 10).'.'.pathinfo($path, PATHINFO_EXTENSION);
                Media::query()->create([
                    'file_name' => $fileName,
                    'original_name' => mb_substr(basename($path), 0, 255),
                    'path' => $path,
                    'mime_type' => $mimeType,
                    'size_bytes' => (int) $disk->size($path),
                    'width' => $dimensions[0] ?? null,
                    'height' => $dimensions[1] ?? null,
                    'uploaded_by' => null,
                ]);
            }
        }
    }

    private function removeEmptySourceDirectories(): void
    {
        $filesystem = new Filesystem;
        foreach ([public_path('images/media'), storage_path('app/public/media'), storage_path('app/private/cms-media')] as $directory) {
            if (is_dir($directory)) {
                $filesystem->deleteDirectory($directory);
            }
        }

        foreach ([public_path('images'), public_path('brochures')] as $directory) {
            if (is_dir($directory) && count(scandir($directory) ?: []) <= 2) {
                rmdir($directory);
            }
        }
    }
}
