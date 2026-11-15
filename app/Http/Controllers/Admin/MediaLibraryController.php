<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaLibraryController extends Controller
{
    /** @var array<string, string> */
    private const LANDING_SECTIONS = [
        'home' => 'Beranda',
        'about' => 'Tentang',
        'products' => 'Produk',
        'documentation' => 'Dokumentasi',
        'careers' => 'Karir',
        'contact' => 'Kontak',
    ];

    public function index(Request $request): View
    {
        $sectionMediaIds = $this->sectionMediaIds();
        $selectedSection = $request->string('section')->toString();
        $media = Media::query()
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search->where('original_name', 'like', '%'.$request->string('q').'%')->orWhere('alt_text_id', 'like', '%'.$request->string('q').'%')->orWhere('alt_text_en', 'like', '%'.$request->string('q').'%')))
            ->when(in_array($request->input('format'), ['image/jpeg', 'image/png', 'image/webp'], true), fn ($query) => $query->where('mime_type', $request->input('format')))
            ->when(array_key_exists($selectedSection, self::LANDING_SECTIONS), fn ($query) => $query->whereIn('id', $sectionMediaIds[$selectedSection]))
            ->latest('created_at')->paginate(30)->withQueryString();

        $mediaIds = $media->getCollection()->modelKeys();
        $currentPageUsage = [];
        foreach ($sectionMediaIds as $section => $ids) {
            foreach (array_intersect($ids, $mediaIds) as $mediaId) {
                $currentPageUsage[$mediaId][] = self::LANDING_SECTIONS[$section];
            }
        }

        $media->getCollection()->transform(function (Media $item) use ($currentPageUsage): Media {
            $item->setAttribute('public_url', file_exists(public_path(ltrim($item->path, '/'))) ? asset(ltrim($item->path, '/')) : Storage::disk(config('cms.media.disk'))->url($item->path));
            $item->setAttribute('usage_sections', array_values(array_unique($currentPageUsage[$item->id] ?? [])));

            return $item;
        });

        $linkedMediaIds = collect($sectionMediaIds)->flatten()->unique();

        return view('admin.media.index', [
            'mediaItems' => $media,
            'mediaTotal' => Media::query()->count(),
            'mediaBytes' => (int) Media::query()->sum('size_bytes'),
            'linkedMediaCount' => $linkedMediaIds->count(),
            'unlinkedMediaCount' => max(0, Media::query()->count() - $linkedMediaIds->count()),
            'landingSections' => self::LANDING_SECTIONS,
        ]);
    }

    /** @return array<string, list<int>> */
    private function sectionMediaIds(): array
    {
        $sectionMediaIds = array_fill_keys(array_keys(self::LANDING_SECTIONS), []);

        $sectionMediaIds['home'] = array_merge($sectionMediaIds['home'], DB::table('brands')->where('status', 'published')->whereNull('deleted_at')->whereNotNull('logo_media_id')->pluck('logo_media_id')->map(fn ($id): int => (int) $id)->all());
        $sectionMediaIds['home'] = array_merge($sectionMediaIds['home'], DB::table('content_blocks')->whereNull('deleted_at')->where('status', 'published')->where('block_key', 'home_coverage')->whereNotNull('media_id')->pluck('media_id')->map(fn ($id): int => (int) $id)->all());

        $sectionMediaIds['about'] = array_merge($sectionMediaIds['about'],
            DB::table('certificates')->where('status', 'published')->whereNull('deleted_at')->whereNotNull('media_id')->pluck('media_id')->map(fn ($id): int => (int) $id)->all(),
            DB::table('content_blocks')->whereNull('deleted_at')->where('status', 'published')->where('block_key', 'about_company')->whereNotNull('media_id')->pluck('media_id')->map(fn ($id): int => (int) $id)->all(),
        );

        foreach (['media_id', 'logo_top_media_id', 'logo_bottom_media_id'] as $column) {
            $sectionMediaIds['products'] = array_merge($sectionMediaIds['products'], DB::table('products')->where('status', 'published')->whereNull('deleted_at')->whereNotNull($column)->pluck($column)->map(fn ($id): int => (int) $id)->all());
        }
        $sectionMediaIds['documentation'] = array_merge($sectionMediaIds['documentation'],
            DB::table('documentations')->where('status', 'published')->whereNull('deleted_at')->whereNotNull('cover_media_id')->pluck('cover_media_id')->map(fn ($id): int => (int) $id)->all(),
            DB::table('documentation_media')->join('documentations', 'documentations.id', '=', 'documentation_media.documentation_id')->where('documentations.status', 'published')->whereNull('documentations.deleted_at')->pluck('documentation_media.media_id')->map(fn ($id): int => (int) $id)->all(),
        );

        $sectionMediaIds['careers'] = array_merge($sectionMediaIds['careers'],
            DB::table('content_blocks')->whereNull('deleted_at')->where('status', 'published')->where('block_key', 'career_intro')->whereNotNull('media_id')->pluck('media_id')->map(fn ($id): int => (int) $id)->all(),
        );
        if (! ContentBlock::query()->where('block_key', 'career_intro')->where('status', 'published')->whereHas('media')->exists()) {
            $sectionMediaIds['careers'] = array_merge($sectionMediaIds['careers'], Media::query()->where('path', 'images/gambartentangmedika.jpeg')->pluck('id')->map(fn ($id): int => (int) $id)->all());
        }

        return array_map(fn (array $ids): array => array_values(array_unique($ids)), $sectionMediaIds);
    }
}
