<?php

namespace App\Http\Controllers;

use App\Models\ContactInfo;
use App\Models\ContentBlock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SeoMeta;
use App\Models\SocialLink;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

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
                ->when(request()->filled('subcategory'), function ($query): void {
                    $terms = array_filter(explode('|', (string) request('subcategory')));
                    $query->where(function ($match) use ($terms): void {
                        foreach ($terms as $term) {
                            $match->orWhere('subcategory', 'like', '%'.$term.'%')->orWhereHas('translations', fn ($translation) => $translation->where('name', 'like', '%'.$term.'%'));
                        }
                    });
                })
                ->with(['translations', 'media', 'topLogo', 'bottomLogo'])->orderBy('category_id')->orderBy('display_order')->orderBy('id')
                ->paginate((int) config('cms.products.catalog_per_page', 12))->withQueryString();
        }
        $categories = ProductCategory::query()
            ->where('status', 'published')
            ->whereHas('products', fn ($query) => $query->where('status', 'published'))
            ->with(['translations', 'featuredProducts' => fn ($query) => $query->where('status', 'published')->with(['translations', 'media', 'topLogo', 'bottomLogo'])])
            ->orderBy('display_order')
            ->get()
            ->map(function (ProductCategory $category) use ($catalogMode, $catalogPage): array {
                $products = $catalogMode
                    ? $catalogPage->getCollection()->where('category_id', $category->id)
                    : $category->featuredProducts->take((int) config('cms.products.featured_per_category', 6));

                if (! $catalogMode && $products->isEmpty()) {
                    $products = $category->products()->where('status', 'published')->with(['translations', 'media', 'topLogo', 'bottomLogo'])
                        ->orderBy('display_order')->limit((int) config('cms.products.featured_per_category', 6))->get();
                }

                return [
                    'id' => (string) $category->id,
                    'name' => $this->localizedValue($category->translations, 'name'),
                    'products' => $products->map(fn (Product $product): array => $this->productCard($product))->all(),
                ];
            })
            ->values()
            ->all();

        $language = app()->getLocale();
        $seo = SeoMeta::query()->where('page_key', 'products')->where('lang', $language)->with('ogImage')->first();
        $contact = ContactInfo::query()->with('translations')->first();
        $contactTranslation = $contact ? $this->localizedTranslation($contact->translations, $language) : null;
        $footerAbout = ContentBlock::query()->where('block_key', 'footer_about')->where('status', 'published')->with('translations')->first();
        $footerTranslation = $footerAbout ? $this->localizedTranslation($footerAbout->translations, $language) : null;

        return view('produk', [
            'categories' => $categories,
            'catalogMode' => $catalogMode,
            'catalogPage' => $catalogPage,
            'seo' => [
                'title' => $seo?->meta_title ?: 'Produk Kami - Interskala Medika Indonesia',
                'description' => $seo?->meta_description ?: 'Jelajahi katalog alat kesehatan PT Interskala Medika Indonesia.',
                'canonical' => $seo?->canonical_url ?: request()->url(),
                'robots_index' => $seo?->robots_index ?? true,
                'og_image' => $seo?->ogImage ? $this->localAsset($seo->ogImage->path) : null,
            ],
            'footer' => [
                'about' => $footerTranslation,
                'contact' => $contact ? ['phone' => $contact->phone, 'email' => $contact->email, 'address' => $contactTranslation?->address] : null,
                'social_links' => SocialLink::query()->where('status', 'published')->orderBy('display_order')->get(),
                'copyright_text' => $footerTranslation?->copyright_text,
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function productCard(Product $product): array
    {
        return [
            'name' => $this->localizedValue($product->translations, 'name'),
            'subtitle' => $this->localizedValue($product->translations, 'description'),
            'parameter' => '',
            'parameters' => $product->parameters ?? [],
            'image' => $this->localAsset($product->media?->path),
            'image_alt' => app()->getLocale() === 'en'
                ? ($product->media?->alt_text_en ?: $product->media?->alt_text_id)
                : $product->media?->alt_text_id,
            'logo_top' => $this->localAsset($product->topLogo?->path),
            'logo_bottom' => $this->localAsset($product->bottomLogo?->path),
            'buy_url' => $product->buy_url,
            'brochure_url' => $this->brochureUrl($product->brochure_path),
            'subcategory' => $product->subcategory ?? '',
        ];
    }

    private function localizedValue(Collection $translations, string $field): string
    {
        $language = app()->getLocale() === 'en' ? 'en' : 'id';
        $translation = $translations->firstWhere('lang', $language) ?? $translations->firstWhere('lang', 'id');

        return (string) ($translation?->{$field} ?: $translations->firstWhere('lang', 'id')?->{$field} ?: '');
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
                $selected->setAttribute($field, $value);
            }
        }

        return $selected;
    }

    private function localAsset(?string $path): string
    {
        if (! $path) {
            return '';
        }

        $normalizedPath = ltrim($path, '/');

        return file_exists(public_path($normalizedPath)) ? asset($normalizedPath) : Storage::disk(config('cms.media.disk'))->url($normalizedPath);
    }

    private function brochureUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        $normalizedPath = ltrim($path, '/');

        return file_exists(public_path($normalizedPath)) ? asset($normalizedPath) : Storage::disk(config('cms.media.disk'))->url($normalizedPath);
    }
}
