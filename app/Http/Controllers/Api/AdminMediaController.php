<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminMediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $media = Media::query()->when($request->filled('q'), fn ($query) => $query->where('original_name', 'like', '%'.$request->string('q').'%'))
            ->latest('created_at')->paginate(25);

        return response()->json(['success' => true, 'data' => $media, 'message' => 'Media berhasil dimuat.']);
    }

    public function store(Request $request, ActivityLogger $activityLogger): JsonResponse
    {
        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'alt_text_id' => ['nullable', 'array'],
            'alt_text_en' => ['nullable', 'array'],
        ]);

        $records = [];

        foreach ($request->file('files', []) as $index => $file) {
            $mime = $file->getMimeType();
            abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 422, 'File harus berupa JPG, PNG, atau WebP yang valid.');
            $record = $this->storeOptimizedImage($file, $request, $index);
            $activityLogger->log($request->user(), 'upload', Media::class, $record->id, null, $record->toArray());
            $records[] = $record;
        }

        return response()->json(['success' => true, 'data' => $records, 'message' => 'Media berhasil diunggah.'], 201);
    }

    public function update(Request $request, Media $media, ActivityLogger $activityLogger): JsonResponse
    {
        $validated = $request->validate([
            'alt_text_id' => ['nullable', 'string', 'max:200'],
            'alt_text_en' => ['nullable', 'string', 'max:200'],
        ]);
        $oldValues = $media->only(['alt_text_id', 'alt_text_en']);
        $media->fill($validated)->save();
        $activityLogger->log($request->user(), 'update', Media::class, $media->id, $oldValues, $media->only(['alt_text_id', 'alt_text_en']));

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

        return $usages;
    }

    private function storeOptimizedImage(UploadedFile $file, Request $request, int $index): Media
    {
        $contents = file_get_contents($file->getRealPath());
        $source = imagecreatefromstring($contents ?: '');
        abort_if($source === false, 422, 'Isi file bukan gambar yang valid.');
        $width = imagesx($source);
        $height = imagesy($source);
        $disk = Storage::disk(config('cms.media.disk'));
        $extension = match ($file->getMimeType()) {
            'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg',
        };
        $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'-'.Str::uuid().'.'.$extension;
        $path = 'media/'.$fileName;
        $optimized = $this->resizeAndEncode($source, $width, $height, 1600, $extension);
        $disk->put($path, $optimized);
        $thumbnail = $this->resizeAndEncode($source, $width, $height, 640, $extension);
        $disk->put('media/thumbnails/'.$fileName, $thumbnail);
        imagedestroy($source);

        return Media::query()->create([
            'file_name' => $fileName,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => strlen($optimized),
            'width' => max(1, (int) round($width * min(1, 1600 / max($width, $height)))),
            'height' => max(1, (int) round($height * min(1, 1600 / max($width, $height)))),
            'alt_text_id' => $request->input('alt_text_id.'.$index),
            'alt_text_en' => $request->input('alt_text_en.'.$index),
            'uploaded_by' => $request->user()->id,
        ]);
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
