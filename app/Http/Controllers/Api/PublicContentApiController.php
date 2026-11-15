<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Certificate;
use App\Models\CompanySummary;
use App\Models\ContactInfo;
use App\Models\ContentBlock;
use App\Models\Documentation;
use App\Models\HeroSlide;
use App\Models\JobOpening;
use App\Models\MissionItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SeoMeta;
use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PublicContentApiController extends Controller
{
    public function home(Request $request): JsonResponse
    {
        $lang = $this->language($request);
        $slides = HeroSlide::query()->where('status', 'published')->with(['media', 'translations'])->orderBy('display_order')->get();

        return $this->success([
            'hero_mode' => $slides->count() > 1 ? 'slider' : ($slides->isEmpty() ? 'hidden' : 'single'),
            'hero_slides' => $slides->map(fn (HeroSlide $slide): array => $this->translated($slide, $lang, 'media')),
            'coverage' => $this->block('home_coverage', $lang),
            'cta' => $this->block('home_cta', $lang),
            'brands' => Brand::query()->where('status', 'published')->with(['logo', 'translations'])->orderBy('display_order')->get()
                ->map(fn (Brand $brand): array => ['id' => $brand->id, 'name' => $brand->name, 'website_url' => $brand->website_url, 'logo' => $this->mediaUrl($brand->logo)]),
        ]);
    }

    public function about(Request $request): JsonResponse
    {
        $lang = $this->language($request);
        $summary = CompanySummary::query()->first();

        return $this->success([
            'company' => $this->block('about_company', $lang),
            'vision' => $this->block('about_vision', $lang),
            'missions' => MissionItem::query()->where('status', 'published')->with('translations')->orderBy('display_order')->get()
                ->map(fn (MissionItem $item): array => $this->translated($item, $lang)),
            'summary' => $summary ? [
                'established_year' => $summary->established_year,
                'years_established' => now()->year - $summary->established_year,
                'partner_count' => $summary->partner_count,
                'province_count' => $summary->province_count,
            ] : null,
            'certificates' => Certificate::query()->where('status', 'published')->with(['media', 'translations'])->orderBy('display_order')->get()
                ->map(fn (Certificate $item): array => [...$this->translated($item, $lang, 'media'), 'issued_year' => $item->issued_year]),
        ]);
    }

    public function productCategories(Request $request): JsonResponse
    {
        $lang = $this->language($request);
        $categories = ProductCategory::query()->where('status', 'published')->whereHas('products', fn (Builder $query): Builder => $query->where('status', 'published'))
            ->with(['translations', 'products' => fn (Builder $query): Builder => $query->where('status', 'published')])
            ->orderBy('display_order')->get();

        return $this->success($categories->map(fn (ProductCategory $category): array => $this->translated($category, $lang)));
    }

    public function featuredProducts(Request $request): JsonResponse
    {
        $lang = $this->language($request);
        $categoryId = $request->integer('category') ?: null;
        $categories = ProductCategory::query()->where('status', 'published')->whereHas('products', fn (Builder $query): Builder => $query->where('status', 'published'))
            ->when($categoryId, fn (Builder $query): Builder => $query->whereKey($categoryId))
            ->with(['translations', 'featuredProducts' => fn (Builder $query): Builder => $query->where('status', 'published')->with(['translations', 'media'])])
            ->orderBy('display_order')->get();
        $items = $categories->map(function (ProductCategory $category) use ($lang): array {
            $products = $category->featuredProducts->take(6);
            if ($products->isEmpty()) {
                $products = $category->products()->where('status', 'published')->with(['translations', 'media'])->orderBy('display_order')->limit(6)->get();
            }

            return [
                'category' => $this->translated($category, $lang),
                'products' => $products->map(fn (Product $product): array => $this->productData($product, $lang)),
            ];
        });

        return $this->success($items);
    }

    public function products(Request $request): JsonResponse
    {
        $lang = $this->language($request);
        $query = Product::query()->where('status', 'published')->with(['translations', 'media', 'category.translations'])
            ->when($request->filled('category'), fn (Builder $builder): Builder => $builder->whereHas('category.translations', fn (Builder $translation): Builder => $translation->where('slug', $request->string('category')->toString())->whereIn('lang', [$lang, 'id'])))
            ->when($request->filled('q'), fn (Builder $builder): Builder => $builder->whereHas('translations', fn (Builder $translation): Builder => $translation->where('name', 'like', '%'.$request->string('q')->toString().'%')))
            ->orderBy('category_id')->orderBy('display_order');
        $perPage = min(max($request->integer('per_page', (int) config('cms.products.catalog_per_page')), 1), 50);
        $page = $query->paginate($perPage);

        return $this->success($page->getCollection()->map(fn (Product $product): array => $this->productData($product, $lang)), 'Produk berhasil dimuat.', [
            'current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage(),
        ]);
    }

    public function documentations(Request $request): JsonResponse
    {
        $lang = $this->language($request);
        $query = Documentation::query()->where('status', 'published')->with(['translations', 'cover', 'gallery'])
            ->when($request->filled('type'), fn (Builder $builder): Builder => $builder->where('type', $request->string('type')->toString()))
            ->orderByDesc('event_date');
        $page = $query->paginate((int) config('cms.documentation.per_page'));

        return $this->success($page->getCollection()->map(fn (Documentation $item): array => $this->documentationData($item, $lang, false)), 'Dokumentasi berhasil dimuat.', [
            'current_page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage(),
        ]);
    }

    public function documentation(Request $request, string $slug): JsonResponse
    {
        $lang = $this->language($request);
        $item = Documentation::query()->where('status', 'published')->where('type', 'article')
            ->whereHas('translations', fn (Builder $query): Builder => $query->where('slug', $slug)->whereIn('lang', [$lang, 'id']))
            ->with(['translations', 'cover', 'gallery'])->firstOrFail();

        return $this->success($this->documentationData($item, $lang, true));
    }

    public function careers(Request $request): JsonResponse
    {
        $lang = $this->language($request);
        $items = JobOpening::query()->where('status', 'open')->where(fn (Builder $query): Builder => $query->whereNull('closing_date')->orWhereDate('closing_date', '>=', today()))
            ->with('translations')->orderByDesc('posted_at')->get();

        return $this->success($items->map(fn (JobOpening $item): array => $this->translated($item, $lang) + [
            'employment_type' => $item->employment_type, 'location' => $item->location,
            'posted_at' => $item->posted_at?->toDateString(), 'closing_date' => $item->closing_date?->toDateString(),
        ]));
    }

    public function career(Request $request, int $id): JsonResponse
    {
        $lang = $this->language($request);
        $item = JobOpening::query()->where('status', 'open')->where(fn (Builder $query): Builder => $query->whereNull('closing_date')->orWhereDate('closing_date', '>=', today()))
            ->with('translations')->findOrFail($id);

        return $this->success($this->translated($item, $lang) + [
            'employment_type' => $item->employment_type, 'location' => $item->location,
            'posted_at' => $item->posted_at?->toDateString(), 'closing_date' => $item->closing_date?->toDateString(), 'apply_url' => $item->apply_url,
        ]);
    }

    public function contact(Request $request): JsonResponse
    {
        $contact = ContactInfo::query()->with('translations')->first();

        return $this->success($contact ? $this->translated($contact, $this->language($request)) + [
            'phone' => $contact->phone, 'email' => $contact->email, 'maps_url' => $contact->maps_url,
            'latitude' => $contact->latitude, 'longitude' => $contact->longitude,
        ] : null);
    }

    public function footer(Request $request): JsonResponse
    {
        $lang = $this->language($request);
        $contact = ContactInfo::query()->with('translations')->first();
        $about = $this->block('footer_about', $lang);

        return $this->success([
            'about' => $about,
            'contact' => $contact ? $this->translated($contact, $lang) + ['phone' => $contact->phone, 'email' => $contact->email] : null,
            'social_links' => SocialLink::query()->where('status', 'published')->orderBy('display_order')->get(['platform', 'url']),
            'copyright_text' => data_get($about, 'translation.copyright_text'),
        ]);
    }

    public function seo(Request $request, string $pageKey): JsonResponse
    {
        abort_unless(in_array($pageKey, ['home', 'about', 'products', 'documentation', 'careers', 'contact'], true), 404);
        $lang = $this->language($request);
        $seo = SeoMeta::query()->where('page_key', $pageKey)->where('lang', $lang)->with('ogImage')->first();

        return $this->success($seo ?? [
            'page_key' => $pageKey, 'lang' => $lang, 'meta_title' => null, 'meta_description' => null,
            'canonical_url' => null, 'robots_index' => true, 'og_image_url' => null,
        ]);
    }

    private function block(string $key, string $lang): ?array
    {
        $block = ContentBlock::query()->where('block_key', $key)->where('status', 'published')->with(['translations', 'media'])->first();

        return $block ? $this->translated($block, $lang, 'media') : null;
    }

    /** @return array<string, mixed> */
    private function translated(object $record, string $lang, ?string $mediaRelation = null): array
    {
        $translations = $record->translations ?? collect();
        $translation = $this->localizedTranslation($translations, $lang);
        $data = ['id' => $record->id, 'status' => $record->status, 'translation' => $translation];

        if ($mediaRelation !== null) {
            $data['image'] = $this->mediaUrl($record->{$mediaRelation});
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function productData(Product $product, string $lang): array
    {
        return [
            'id' => $product->id,
            'category' => $this->translated($product->category, $lang),
            'translation' => $this->localizedTranslation($product->translations, $lang),
            'image' => $this->mediaUrl($product->media),
            'brochure_url' => $product->brochure_path ? $this->fileUrl($product->brochure_path) : null,
        ];
    }

    /** @return array<string, mixed> */
    private function documentationData(Documentation $item, string $lang, bool $includeBody): array
    {
        $translation = $this->localizedTranslation($item->translations, $lang);
        $gallery = $item->gallery->map(fn ($media): array => [
            'url' => $this->mediaUrl($media),
            'caption' => $lang === 'en' ? ($media->pivot->caption_en ?: $media->pivot->caption_id) : $media->pivot->caption_id,
            'display_order' => $media->pivot->display_order,
        ]);

        return [
            'id' => $item->id, 'type' => $item->type, 'event_date' => $item->event_date?->toDateString(),
            'translation' => $includeBody ? $translation : collect($translation)->except('body'),
            'cover' => $this->mediaUrl($item->cover), 'gallery' => $gallery,
        ];
    }

    private function mediaUrl(?object $media): ?string
    {
        if ($media === null) {
            return null;
        }

        return $this->fileUrl($media->path);
    }

    private function localizedTranslation(Collection $translations, string $lang): ?object
    {
        $fallback = $translations->firstWhere('lang', 'id');
        $selected = $translations->firstWhere('lang', $lang);
        if ($selected === null || $fallback === null || $lang === 'id') {
            return $selected ?? $fallback;
        }

        $selected = clone $selected;
        foreach ($fallback->getAttributes() as $field => $value) {
            if (! in_array($field, ['id', 'lang', 'created_at', 'updated_at'], true) && blank($selected->getAttribute($field))) {
                $selected->setAttribute($field, $value);
            }
        }

        return $selected;
    }

    private function fileUrl(string $path): string
    {
        $normalizedPath = ltrim($path, '/');

        return File::exists(public_path($normalizedPath))
            ? asset($normalizedPath)
            : Storage::disk(config('cms.media.disk'))->url($normalizedPath);
    }

    private function language(Request $request): string
    {
        return $request->query('lang') === 'en' ? 'en' : 'id';
    }

    /** @param mixed $data @param array<string, int> $meta */
    private function success(mixed $data, string $message = 'Data berhasil dimuat.', array $meta = []): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data, 'message' => $message, 'meta' => $meta]);
    }
}
