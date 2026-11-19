<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function show(Media $media): Response
    {
        $diskName = config('cms.media.disk');
        $disk = Storage::disk($diskName);

        $headers = [
            'Content-Type' => $media->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=86400',
            'Accept-Ranges' => 'bytes',
        ];

        if ($disk->exists($media->path)) {
            if (config('filesystems.disks.'.$diskName.'.driver') === 'local') {
                return response()->file($disk->path($media->path), $headers);
            }

            return $disk->response($media->path, $media->original_name, $headers, 'inline');
        }

        $publicRoot = realpath(public_path());
        $publicFile = realpath(public_path(ltrim(str_replace('\\', '/', $media->path), '/')));
        abort_unless(
            $publicRoot !== false
                && $publicFile !== false
                && is_file($publicFile)
                && str_starts_with($publicFile, $publicRoot.DIRECTORY_SEPARATOR),
            404,
        );

        return response()->file($publicFile, $headers);
    }
}
