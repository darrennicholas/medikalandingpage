<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaLibraryController extends Controller
{
    public function index(Request $request): View
    {
        $media = Media::query()->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search->where('original_name', 'like', '%'.$request->string('q').'%')->orWhere('alt_text_id', 'like', '%'.$request->string('q').'%')->orWhere('alt_text_en', 'like', '%'.$request->string('q').'%')))
            ->when(in_array($request->input('format'), ['image/jpeg', 'image/png', 'image/webp'], true), fn ($query) => $query->where('mime_type', $request->input('format')))
            ->latest('created_at')->paginate(30)->withQueryString();
        $usageLabels = [];
        $references = [
            'Hero' => ['hero_slides', 'media_id'], 'Brand' => ['brands', 'logo_media_id'],
            'Sertifikat' => ['certificates', 'media_id'], 'Produk' => ['products', 'media_id'],
            'Logo atas produk' => ['products', 'logo_top_media_id'], 'Logo bawah produk' => ['products', 'logo_bottom_media_id'],
            'Dokumentasi' => ['documentations', 'cover_media_id'], 'Konten' => ['content_blocks', 'media_id'],
            'SEO' => ['seo_metas', 'og_media_id'], 'Galeri' => ['documentation_media', 'media_id'],
        ];
        $mediaIds = $media->getCollection()->modelKeys();
        foreach ($references as $label => [$table, $column]) {
            foreach (DB::table($table)->whereIn($column, $mediaIds)->distinct()->pluck($column) as $mediaId) {
                $usageLabels[$mediaId][] = $label;
            }
        }
        $media->getCollection()->transform(function (Media $item) use ($usageLabels): Media {
            $item->setAttribute('public_url', file_exists(public_path(ltrim($item->path, '/'))) ? asset(ltrim($item->path, '/')) : Storage::disk(config('cms.media.disk'))->url($item->path));
            $item->setAttribute('usage_labels', $usageLabels[$item->id] ?? []);

            return $item;
        });

        $linkedMediaIds = DB::table('hero_slides')->whereNotNull('media_id')->select('media_id')
            ->union(DB::table('brands')->whereNotNull('logo_media_id')->select('logo_media_id'))
            ->union(DB::table('certificates')->whereNotNull('media_id')->select('media_id'))
            ->union(DB::table('products')->whereNotNull('media_id')->select('media_id'))
            ->union(DB::table('products')->whereNotNull('logo_top_media_id')->select('logo_top_media_id'))
            ->union(DB::table('products')->whereNotNull('logo_bottom_media_id')->select('logo_bottom_media_id'))
            ->union(DB::table('documentations')->whereNotNull('cover_media_id')->select('cover_media_id'))
            ->union(DB::table('content_blocks')->whereNotNull('media_id')->select('media_id'))
            ->union(DB::table('seo_metas')->whereNotNull('og_media_id')->select('og_media_id'))
            ->union(DB::table('documentation_media')->select('media_id'));
        $linkedMediaCount = DB::query()->fromSub($linkedMediaIds, 'linked_media')->distinct()->count('media_id');

        return view('admin.media.index', [
            'mediaItems' => $media,
            'mediaTotal' => Media::query()->count(),
            'mediaBytes' => (int) Media::query()->sum('size_bytes'),
            'linkedMediaCount' => $linkedMediaCount,
            'unlinkedMediaCount' => max(0, Media::query()->count() - $linkedMediaCount),
        ]);
    }
}
