<?php

namespace App\Http\Controllers;

use App\Models\ContactInfo;
use App\Models\ContentBlock;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SeoMeta;
use App\Models\SocialLink;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class ProductController extends Controller
{
    public function index(): View
    {
        return $this->renderCatalog(false);
    }

    public function catalog(): View
    {
        return $this->renderCatalog(true);
    }

    private function renderCatalog(bool $catalogMode): View
    {
        $catalogPage = null;
        if ($catalogMode) {
            $categoryIds = collect(explode(',', (string) request('category', '')))->filter(fn ($id) => ctype_digit($id))->map(fn ($id) => (int) $id)->all();
            $catalogPage = Product::query()->where('status', 'published')->whereHas('category', fn ($query) => $query->where('status', 'published'))
                ->when($categoryIds !== [], fn ($query) => $query->whereIn('category_id', $categoryIds))
                ->when(request()->filled('q'), fn ($query) => $query->whereHas('translations', fn ($translation) => $translation->where('name', 'like', '%'.request('q').'%')))
                ->when(request()->boolean('new'), fn ($query) => $query->whereDate('new_until', '>=', today()))
                ->when(request()->filled('subcategory'), function ($query): void {
                    $terms = array_filter(explode('|', (string) request('subcategory')));
                    $query->where(function ($match) use ($terms): void {
                        foreach ($terms as $term) {
                            $match->orWhere('subcategory', 'like', '%'.$term.'%')->orWhereHas('translations', fn ($translation) => $translation->where('name', 'like', '%'.$term.'%'));
                        }
                    });
                })
                ->with(['translations', 'media', 'topLogo', 'bottomLogo'])->orderByRaw('CASE WHEN new_until >= ? THEN 0 ELSE 1 END', [today()->toDateString()])->orderBy('category_id')->orderBy('display_order')->orderBy('id')
                ->paginate((int) config('cms.products.catalog_per_page', 12))->withQueryString();
        }
        $newProducts = $catalogMode
            ? $catalogPage->getCollection()->filter(fn (Product $product): bool => $product->new_until !== null && $product->new_until->gte(today()))
            : Product::query()->where('status', 'published')->whereDate('new_until', '>=', today())
                ->whereHas('category', fn ($query) => $query->where('status', 'published'))
                ->with(['translations', 'media', 'topLogo', 'bottomLogo'])->orderBy('new_until')->orderBy('display_order')
                ->limit((int) config('cms.products.featured_per_category', 6))->get();
        $newProductIds = $newProducts->modelKeys();

        $categories = ProductCategory::query()
            ->where('status', 'published')
            ->whereHas('products', fn ($query) => $query->where('status', 'published'))
            ->with(['translations', 'featuredProducts' => fn ($query) => $query->where('status', 'published')->whereColumn('products.category_id', 'category_featured_products.category_id')->with(['translations', 'media', 'topLogo', 'bottomLogo'])])
            ->orderBy('display_order')
            ->get()
            ->map(function (ProductCategory $category) use ($catalogMode, $catalogPage, $newProductIds): array {
                $products = $catalogMode
                    ? $catalogPage->getCollection()->where('category_id', $category->id)
                    : $category->featuredProducts->sortBy(fn (Product $product): int => $product->new_until?->isPast() === false || $product->new_until?->isToday() ? 0 : 1)->take((int) config('cms.products.featured_per_category', 6));

                if (! $catalogMode && $products->isEmpty()) {
                    $products = $category->products()->where('status', 'published')->with(['translations', 'media', 'topLogo', 'bottomLogo'])
                        ->orderByRaw('CASE WHEN new_until >= ? THEN 0 ELSE 1 END', [today()->toDateString()])->orderBy('display_order')->limit((int) config('cms.products.featured_per_category', 6))->get();
                }

                $products = $products->reject(fn (Product $product): bool => in_array($product->getKey(), $newProductIds, true));

                return [
                    'id' => (string) $category->id,
                    'name' => $this->localizedValue($category->translations, 'name'),
                    'products' => $products->map(fn (Product $product): array => $this->productCard($product))->all(),
                ];
            })
            ->values()
            ->all();

        $newProducts = $newProducts->map(fn (Product $product): array => $this->productCard($product))->all();

        $language = app()->getLocale();
        $seo = SeoMeta::query()->where('page_key', 'products')->where('lang', $language)->with('ogImage')->first();
        $contact = ContactInfo::query()->with('translations')->first();
        $contactTranslation = $contact ? $this->localizedTranslation($contact->translations, $language) : null;
        $footerAbout = ContentBlock::query()->where('block_key', 'footer_about')->where('status', 'published')->with('translations')->first();
        $footerTranslation = $footerAbout ? $this->localizedTranslation($footerAbout->translations, $language) : null;
        $footerSettings = ContentBlock::query()->where('block_key', 'footer_about')->first();

        return view('produk', [
            'categories' => $categories,
            'newProducts' => $newProducts,
            'catalogMode' => $catalogMode,
            'catalogPage' => $catalogPage,
            'seo' => [
                'title' => $seo?->meta_title ?: 'Produk Kami - Interskala Medika Indonesia',
                'description' => $seo?->meta_description ?: 'Jelajahi katalog alat kesehatan PT Interskala Medika Indonesia.',
                'canonical' => $seo?->canonical_url ?: request()->url(),
                'robots_index' => $seo?->robots_index ?? true,
                'og_image' => $this->mediaUrl($seo?->ogImage),
            ],
            'footer' => [
                'about' => $footerTranslation,
                'show_phone' => $footerSettings?->footer_show_phone ?? true,
                'show_email' => $footerSettings?->footer_show_email ?? true,
                'contact' => $contact ? ['phone' => $contact->phone, 'email' => $contact->email, 'address' => $contactTranslation?->address] : null,
                'social_links' => SocialLink::query()->where('status', 'published')->with('previewMedia')->orderBy('display_order')->get(),
                'copyright_text' => $footerTranslation?->copyright_text,
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function productCard(Product $product): array
    {
        return [
            'name' => $this->localizedValue($product->translations, 'name'),
            'id' => $product->id,
            'category_id' => (string) $product->category_id,
            'is_new' => $product->new_until !== null && $product->new_until->gte(today()),
            'slug' => $this->localizedValue($product->translations, 'slug'),
            'subtitle' => $this->plainText($this->localizedValue($product->translations, 'description')),
            'parameter' => '',
            'parameters' => $product->parameters ?? [],
            'image' => $this->mediaUrl($product->media),
            'image_alt' => match (app()->getLocale()) {
                'zh' => $product->media?->alt_text_zh ?: ($product->media?->alt_text_en ?: $product->media?->alt_text_id),
                'en' => $product->media?->alt_text_en ?: $product->media?->alt_text_id,
                default => $product->media?->alt_text_id,
            },
            'logo_top' => $this->mediaUrl($product->topLogo),
            'logo_bottom' => $this->mediaUrl($product->bottomLogo),
            'buy_url' => $product->buy_url,
            'brochure_url' => $product->brochure_path ? route('localized.brochure.request', ['locale' => app()->getLocale(), 'product' => $product->id]) : null,
            'subcategory' => $product->subcategory ?? '',
        ];
    }

    private function localizedValue(Collection $translations, string $field): string
    {
        $language = app()->getLocale();
        $translation = $translations->firstWhere('lang', $language) ?? $translations->firstWhere('lang', 'id');

        return (string) ($translation?->{$field} ?: $translations->firstWhere('lang', 'id')?->{$field} ?: '');
    }

    private function plainText(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $text = preg_replace('/<(?:br|\/p|\/li|\/h[1-6])\b[^>]*>/i', ' ', $value) ?? $value;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    private function localizedTranslation(Collection $translations, string $language): ?object
    {
        $fallback = $translations->firstWhere('lang', 'id');
        $selected = $translations->firstWhere('lang', $language);
        if ($selected === null || $fallback === null || $language === 'id') {
            return $selected ?? $fallback;
        }
        $selected = clone $selected;
        foreach ($fallback->getAttributes() as $field => $value) {
            if (! in_array($field, ['id', 'lang', 'created_at', 'updated_at'], true) && blank($selected->getAttribute($field))) {
                $selected->setAttribute($field, $fallback->getAttribute($field));
            }
        }

        return $selected;
    }

    private function mediaUrl(?Media $media): string
    {
        if ($media === null) {
            return '';
        }

        return route('media.stream', ['media' => $media->id]);
    }

    private function brochureUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        $normalizedPath = trim(ltrim($path, '/'), '/');
        $normalizedPath = preg_replace('#^(?:public/)?brochures/#', '', $normalizedPath) ?? $normalizedPath;

        return route('stored.brochure', ['path' => $normalizedPath]);
    }
}
