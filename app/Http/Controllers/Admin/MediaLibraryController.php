<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $sectionMediaIds = $this->mediaUsage();
        $selectedSection = $request->string('section')->toString();
        $media = Media::query()
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search->where('original_name', 'like', '%'.$request->string('q').'%')->orWhere('alt_text_id', 'like', '%'.$request->string('q').'%')->orWhere('alt_text_en', 'like', '%'.$request->string('q').'%')->orWhere('alt_text_zh', 'like', '%'.$request->string('q').'%')))
            ->when(in_array($request->input('format'), ['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'video/webm'], true), fn ($query) => $query->where('mime_type', $request->input('format')))
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
            $item->setAttribute('public_url', route('media.stream', ['media' => $item->id]));
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
    private function mediaUsage(): array
    {
        $sections = array_fill_keys(array_keys(self::LANDING_SECTIONS), []);
        $add = function (mixed $id, string $section) use (&$sections): void {
            if (! $id) {
                return;
            }
            $id = (int) $id;
            $sections[$section][] = $id;
        };

        foreach ([
            ['hero_slides', 'media_id', 'home'],
            ['brands', 'logo_media_id', 'home'],
            ['certificates', 'media_id', 'about'],
            ['products', 'media_id', 'products'],
            ['products', 'logo_top_media_id', 'products'],
            ['products', 'logo_bottom_media_id', 'products'],
            ['documentations', 'cover_media_id', 'documentation'],
        ] as [$table, $column, $section]) {
            foreach (DB::table($table)->whereNotNull($column)->pluck($column) as $mediaId) {
                $add($mediaId, $section);
            }
        }

        $blockSections = ['home_coverage' => 'home', 'home_cta' => 'home', 'about_company' => 'about', 'about_vision' => 'about', 'career_intro' => 'careers', 'footer_about' => 'contact'];
        foreach (DB::table('content_blocks')->whereNotNull('media_id')->get(['media_id', 'block_key']) as $block) {
            $add($block->media_id, $blockSections[$block->block_key] ?? 'home');
        }
        foreach (DB::table('hero_slides')->whereNotNull('draft_payload')->get(['draft_payload']) as $hero) {
            $draft = json_decode($hero->draft_payload, true);
            $add($draft['attributes']['media_id'] ?? null, 'home');
        }
        foreach (DB::table('content_blocks')->whereNotNull('draft_payload')->get(['draft_payload', 'block_key']) as $block) {
            $draft = json_decode($block->draft_payload, true);
            $add($draft['attributes']['media_id'] ?? null, $blockSections[$block->block_key] ?? 'home');
        }

        foreach (DB::table('documentation_media')->pluck('media_id') as $mediaId) {
            $add($mediaId, 'documentation');
        }
        $seoSections = ['home' => 'home', 'about' => 'about', 'products' => 'products', 'documentation' => 'documentation', 'careers' => 'careers', 'contact' => 'contact'];
        foreach (DB::table('seo_metas')->whereNotNull('og_media_id')->get(['og_media_id', 'page_key']) as $seo) {
            $add($seo->og_media_id, $seoSections[$seo->page_key] ?? 'home');
        }

        return array_map(fn (array $ids): array => array_values(array_unique($ids)), $sections);
    }
}
