<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\ActivityLogger;
use App\Services\MediaThumbnailGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AdminMediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $media = Media::query()->when($request->filled('q'), fn ($query) => $query->where('original_name', 'like', '%'.$request->string('q').'%'))
            ->latest('created_at')->paginate(25);

        return response()->json(['success' => true, 'data' => $media, 'message' => 'Media berhasil dimuat.']);
    }

    public function store(Request $request, ActivityLogger $activityLogger, MediaThumbnailGenerator $thumbnailGenerator): JsonResponse
    {
        foreach ($request->file('files', []) as $file) {
            if ($file instanceof UploadedFile && $file->getError() === UPLOAD_ERR_INI_SIZE) {
                abort(413, 'Ukuran file melebihi batas yang diizinkan server.');
            }
        }

        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file'],
            'alt_text_id' => ['nullable', 'array'],
            'alt_text_en' => ['nullable', 'array'],
            'alt_text_zh' => ['nullable', 'array'],
        ]);

        $files = $request->file('files', []);
        foreach ($files as $file) {
            $mime = $file->getMimeType();
            if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                abort_if($file->getSize() > 2 * 1024 * 1024, 413, 'Ukuran gambar maksimal 2 MB per file.');
            } elseif (in_array($mime, ['video/mp4', 'video/webm'], true)) {
                abort_if($file->getSize() > 50 * 1024 * 1024, 413, 'Ukuran video maksimal 50 MB per file.');
            } else {
                abort(415, 'File harus berupa JPG, PNG, WebP, MP4, atau WebM yang valid.');
            }
        }

        $storedPaths = [];
        try {
            $records = DB::transaction(function () use ($files, $request, $activityLogger, $thumbnailGenerator, &$storedPaths): array {
                $records = [];

                foreach ($files as $index => $file) {
                    $mime = $file->getMimeType();
                    $record = in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)
                        ? $this->storeOptimizedImage($file, $request, $index, $storedPaths, $thumbnailGenerator)
                        : $this->storeVideo($file, $request, $index, $mime, $storedPaths);
                    $record->setAttribute('admin_preview_url', $this->mediaUrl($record));
                    $records[] = $record;
                    $activityLogger->log($request->user(), 'upload', Media::class, $record->id, null, $record->toArray());
                }

                return $records;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }

        return response()->json(['success' => true, 'data' => $records, 'message' => 'Media berhasil diunggah.'], 201);
    }

    public function update(Request $request, Media $media, ActivityLogger $activityLogger): JsonResponse
    {
        $validated = $request->validate([
            'alt_text_id' => ['nullable', 'string', 'max:200'],
            'alt_text_en' => ['nullable', 'string', 'max:200'],
            'alt_text_zh' => ['nullable', 'string', 'max:200'],
        ]);
        $oldValues = $media->only(['alt_text_id', 'alt_text_en', 'alt_text_zh']);
        $media->fill($validated)->save();
        $activityLogger->log($request->user(), 'update', Media::class, $media->id, $oldValues, $media->only(['alt_text_id', 'alt_text_en', 'alt_text_zh']));

        return response()->json(['success' => true, 'data' => $media, 'message' => 'Teks alternatif berhasil diperbarui.']);
    }

    public function destroy(Request $request, Media $media, ActivityLogger $activityLogger): JsonResponse
    {
        $usages = $this->usages($media);
        if ($usages !== []) {
            return response()->json(['success' => false, 'data' => ['usages' => $usages], 'message' => 'Media masih digunakan dan tidak dapat dihapus.'], 422);
        }

        $media->delete();
        $activityLogger->log($request->user(), 'delete', Media::class, $media->id, $media->toArray());

        return response()->json(['success' => true, 'data' => null, 'message' => 'Media berhasil dihapus.']);
    }

    /** @return list<string> */
    public function usages(Media $media): array
    {
        if (str_starts_with($media->path, 'images/site-assets/')) {
            return ['Aset website bawaan'];
        }

        $checks = [
            'Hero slide' => ['hero_slides', 'media_id'], 'Brand' => ['brands', 'logo_media_id'],
            'Sertifikat' => ['certificates', 'media_id'], 'Produk' => ['products', 'media_id'],
            'Logo atas produk' => ['products', 'logo_top_media_id'], 'Logo bawah produk' => ['products', 'logo_bottom_media_id'],
            'Dokumentasi cover' => ['documentations', 'cover_media_id'], 'Blok konten' => ['content_blocks', 'media_id'],
            'SEO Open Graph' => ['seo_metas', 'og_media_id'], 'Galeri dokumentasi' => ['documentation_media', 'media_id'],
        ];
        $usages = [];

        foreach ($checks as $label => [$table, $column]) {
            if (DB::table($table)->where($column, $media->id)->exists()) {
                $usages[] = $label;
            }
        }

        foreach (['hero_slides' => 'Draft perubahan Hero', 'content_blocks' => 'Draft perubahan blok konten'] as $table => $label) {
            foreach (DB::table($table)->whereNotNull('draft_payload')->pluck('draft_payload') as $payload) {
                $draft = json_decode($payload, true);
                if ((int) ($draft['attributes']['media_id'] ?? 0) === (int) $media->id) {
                    $usages[] = $label;
                    break;
                }
            }
        }

        return $usages;
    }

    /** @param list<string> $storedPaths */
    private function storeOptimizedImage(UploadedFile $file, Request $request, int $index, array &$storedPaths, MediaThumbnailGenerator $thumbnailGenerator): Media
    {
        $contents = file_get_contents($file->getRealPath());
        $source = imagecreatefromstring($contents ?: '');
        abort_if($source === false, 415, 'Isi file bukan gambar yang valid.');
        $width = imagesx($source);
        $height = imagesy($source);
        $disk = Storage::disk('public');
        $extension = match ($file->getMimeType()) {
            'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg',
        };
        $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'-'.Str::uuid().'.'.$extension;
        $storagePath = 'images/'.$fileName;
        $path = $storagePath;
        $optimized = $this->resizeAndEncode($source, $width, $height, 1600, $extension);
        imagedestroy($source);
        $storedPaths[] = $storagePath;
        abort_if(! $disk->put($storagePath, $optimized), 500, 'Gambar gagal disimpan ke Media Library. Periksa kapasitas dan izin storage.');
        $media = Media::query()->create([
            'file_name' => $fileName,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => strlen($optimized),
            'width' => max(1, (int) round($width * min(1, 1600 / max($width, $height)))),
            'height' => max(1, (int) round($height * min(1, 1600 / max($width, $height)))),
            'alt_text_id' => $request->input('alt_text_id.'.$index),
            'alt_text_en' => $request->input('alt_text_en.'.$index),
            'alt_text_zh' => $request->input('alt_text_zh.'.$index),
            'uploaded_by' => $request->user()->id,
        ]);

        try {
            $thumbnailGenerator->create($media);
            $storedPaths[] = $thumbnailGenerator->thumbnailPath($media);
        } catch (Throwable $exception) {
            Log::warning('Gagal membuat thumbnail media; file asli tetap disimpan dan proses retry akan mencoba kembali.', [
                'media_id' => $media->id,
                'path' => $media->path,
                'exception' => $exception->getMessage(),
            ]);
        }

        return $media;
    }

    /** @param list<string> $storedPaths */
    private function storeVideo(UploadedFile $file, Request $request, int $index, string $mime, array &$storedPaths): Media
    {
        $extension = $mime === 'video/webm' ? 'webm' : 'mp4';
        $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'-'.Str::uuid().'.'.$extension;
        $storedPaths[] = 'videos/'.$fileName;
        $storedPath = $file->storeAs('videos', $fileName, 'public');
        abort_if($storedPath === false, 500, 'Video gagal disimpan ke Media Library. Periksa kapasitas dan izin storage.');
        $path = $storedPath;

        return Media::query()->create([
            'file_name' => $fileName,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'path' => $path,
            'mime_type' => $mime,
            'size_bytes' => (int) $file->getSize(),
            'alt_text_id' => $request->input('alt_text_id.'.$index),
            'alt_text_en' => $request->input('alt_text_en.'.$index),
            'alt_text_zh' => $request->input('alt_text_zh.'.$index),
            'uploaded_by' => $request->user()->id,
        ]);
    }

    private function mediaUrl(Media $media): string
    {
        return route('media.stream', ['media' => $media->id]);
    }

    private function resizeAndEncode(\GdImage $source, int $width, int $height, int $maximumWidth, string $extension): string
    {
        $scale = min(1, $maximumWidth / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($extension === 'png' || $extension === 'webp') {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefill($target, 0, 0, $transparent);
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

        return $encoded;
    }
}
