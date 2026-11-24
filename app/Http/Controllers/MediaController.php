<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function show(Request $request, Media $media): Response
    {
        $isPublic = $this->isUsedByPublishedContent($media);
        $user = $request->user();
        $isCmsUser = $user?->status === 'active'
            && ! $user->must_change_password
            && $user->canAccessCms();
        abort_unless($isPublic || $isCmsUser, 404);

        $isPrivate = str_starts_with($media->path, 'private/');
        $diskName = $isPrivate ? 'cms_media_private' : 'public';
        $path = $isPrivate ? substr($media->path, strlen('private/')) : $media->path;
        $disk = Storage::disk($diskName);
        if ($request->query('variant') === 'thumbnail' && str_starts_with($media->mime_type, 'image/')) {
            $thumbnailPath = 'images/thumbnails/'.basename($media->path);
            if (Storage::disk('public')->exists($thumbnailPath)) {
                $diskName = 'public';
                $disk = Storage::disk($diskName);
                $path = $thumbnailPath;
            }
        }

        $headers = [
            'Content-Type' => $media->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => $isPublic ? 'public, max-age=86400' : 'private, no-store',
            'Accept-Ranges' => 'bytes',
        ];

        if ($disk->exists($path)) {
            if (config('filesystems.disks.'.$diskName.'.driver') === 'local') {
                return response()->file($disk->path($path), $headers);
            }

            return $disk->response($path, $media->original_name, $headers, 'inline');
        }

        abort(404);
    }

    private function isUsedByPublishedContent(Media $media): bool
    {
        foreach ([
            ['hero_slides', 'media_id'],
            ['content_blocks', 'media_id'],
            ['brands', 'logo_media_id'],
            ['certificates', 'media_id'],
            ['products', 'media_id'],
            ['products', 'logo_top_media_id'],
            ['products', 'logo_bottom_media_id'],
            ['documentations', 'cover_media_id'],
            ['social_links', 'preview_media_id'],
        ] as [$table, $column]) {
            if (DB::table($table)->where($column, $media->id)->where('status', 'published')->whereNull('deleted_at')->exists()) {
                return true;
            }
        }

        return DB::table('documentation_media')
            ->join('documentations', 'documentations.id', '=', 'documentation_media.documentation_id')
            ->where('documentation_media.media_id', $media->id)
            ->where('documentations.status', 'published')
            ->whereNull('documentations.deleted_at')
            ->exists()
            || DB::table('seo_metas')->where('og_media_id', $media->id)->exists();
    }
}
