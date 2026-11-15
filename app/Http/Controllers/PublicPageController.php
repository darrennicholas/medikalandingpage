<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Certificate;
use App\Models\CompanySummary;
use App\Models\ContactInfo;
use App\Models\ContentBlock;
use App\Models\ContentBlockTranslation;
use App\Models\Documentation;
use App\Models\HeroSlide;
use App\Models\HeroSlideTranslation;
use App\Models\JobOpening;
use App\Models\MissionItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SeoMeta;
use App\Models\SocialLink;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class PublicPageController extends Controller
{
    public function preview(string $resource, string $id): Response
    {
        $language = request()->query('lang') === 'en' ? 'en' : 'id';
        app()->setLocale($language);
        if (in_array($resource, ['hero-slides', 'content-blocks', 'brands', 'mission-items', 'certificates', 'product-categories', 'social-links'], true)) {
            return $this->previewSection($resource, $id, $language);
        }
        $data = ['language' => $language, 'footer' => $this->footerData($language), 'seo' => ['title' => 'Pratinjau', 'description' => '', 'robots_index' => false]];

        if ($resource === 'products') {
            $product = Product::query()->with(['translations', 'media'])->findOrFail($id);
            $product->setAttribute('public_image_url', $this->mediaUrl($product->media));
            $product->setAttribute('public_image_alt', $product->media?->alt_text_id);
            $product->setAttribute('public_brochure_url', $this->fileUrl($product->brochure_path));
            $data += ['kind' => 'product', 'product' => $product, 'translation' => $this->translation($product->translations, $language)];
        } elseif ($resource === 'documentations') {
            $item = Documentation::query()->with(['translations', 'cover', 'gallery'])->findOrFail($id);
            $this->decorateDocumentationItem($item, $language);
            $data += ['kind' => 'documentation', 'item' => $item, 'translation' => $this->translation($item->translations, $language)];
        } else {
            $job = JobOpening::query()->with('translations')->findOrFail($id);
            $data += ['kind' => 'career', 'job' => $job, 'translation' => $this->translation($job->translations, $language)];
        }

        return response()->view('public-detail', $data)->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'private, no-store');
    }

    private function previewSection(string $resource, string $id, string $language): Response
    {
        $model = match ($resource) {
            'hero-slides' => HeroSlide::class,
            'content-blocks' => ContentBlock::class,
            'brands' => Brand::class,
            'mission-items' => MissionItem::class,
            'certificates' => Certificate::class,
            'product-categories' => ProductCategory::class,
            'social-links' => SocialLink::class,
        };
        $query = $model::query();
        if ($resource === 'content-blocks' && ! ctype_digit($id)) {
            $query->where('block_key', $id);
        } else {
            $query->whereKey($id);
        }
        $record = $query->firstOrFail();
        $record->load(match ($resource) {
            'brands' => ['logo'], 'mission-items' => ['translations'], 'product-categories' => ['translations', 'products.translations', 'products.media', 'products.topLogo', 'products.bottomLogo'], 'social-links' => [], default => ['translations', 'media']
        });
        $payload = $record->getAttribute('draft_payload');
        if (is_array($payload)) {
            $record->forceFill($payload['attributes'] ?? []);
            $class = $resource === 'hero-slides' ? HeroSlideTranslation::class : ContentBlockTranslation::class;
            $translations = $record->translations->keyBy('lang');
            foreach ($payload['translations'] ?? [] as $lang => $attributes) {
                $translation = $translations->get($lang) ?? new $class;
                $translation->forceFill($attributes);
                $translations->put($lang, $translation);
            }
            $record->setRelation('translations', $translations->values());
            $record->unsetRelation('media')->load('media');
        }
        $isAbout = in_array($resource, ['mission-items', 'certificates'], true) || ($resource === 'content-blocks' && in_array($record->block_key, ['about_company', 'about_vision'], true));
        $isCareer = $resource === 'content-blocks' && $record->block_key === 'career_intro';
        $view = $resource === 'product-categories' ? app(ProductController::class)->index() : ($isCareer ? $this->careers() : ($isAbout ? $this->about() : $this->home()));
        $data = $view->getData();
        $data['seo']['robots_index'] = false;
        if ($resource === 'content-blocks') {
            $translation = $this->translation($record->translations, $language);
            $block = ['title' => $translation?->title, 'subtitle' => $translation?->subtitle, 'body' => $translation?->body, 'cta_label' => $translation?->cta_label, 'copyright_text' => $translation?->copyright_text, 'metrics' => $translation?->metrics ?? [], 'buttons' => $translation?->buttons ?? [], 'cta_url' => $record->cta_url, 'image_url' => $this->mediaUrl($record->media), 'image_alt' => $record->media?->alt_text_id];
            if ($isCareer) {
                $data['careerIntro'] = $block;
            } else {
                $data['blocks']->put($record->block_key, $block);
                if ($record->block_key === 'footer_about') {
                    $data['footer']['about'] = $block;
                    $data['footer']['copyright_text'] = $block['copyright_text'];
                }
            }
        } elseif ($resource === 'hero-slides') {
            $translation = $this->translation($record->translations, $language);
            $record->setAttribute('public_title', $translation?->title);
            $record->setAttribute('public_subtitle', $translation?->subtitle);
            $record->setAttribute('public_cta_label', $translation?->cta_label);
            $record->setAttribute('public_image_url', $this->mediaUrl($record->media));
            $record->setAttribute('public_image_alt', $record->media?->alt_text_id);
            $data['slides'] = $data['slides']->reject(fn ($item) => $item->id === $record->id)->push($record)->sortBy('display_order')->values();
        } elseif ($resource === 'brands') {
            $record->setAttribute('public_logo_url', $this->mediaUrl($record->logo));
            $record->setAttribute('public_logo_alt', $record->logo?->alt_text_id);
            $data['brands'] = $data['brands']->reject(fn ($item) => $item->id === $record->id)->push($record)->sortBy('display_order')->values();
        } elseif ($resource === 'product-categories') {
            $translation = $this->translation($record->translations, $language);
            $products = $record->products->where('status', 'published')->sortBy('display_order')->take(6)->map(function ($product) use ($language): array {
                $text = $this->translation($product->translations, $language);

                return ['name' => $text?->name ?? '', 'subtitle' => $text?->description ?? '', 'parameter' => '', 'parameters' => $product->parameters ?? [], 'image' => $this->mediaUrl($product->media) ?? '', 'image_alt' => $product->media?->alt_text_id, 'logo_top' => $this->mediaUrl($product->topLogo) ?? '', 'logo_bottom' => $this->mediaUrl($product->bottomLogo) ?? '', 'buy_url' => $product->buy_url, 'brochure_url' => $this->fileUrl($product->brochure_path) ?? '', 'subcategory' => $product->subcategory ?? ''];
            })->values()->all();
            $data['categories'] = collect($data['categories'])->reject(fn ($item) => (string) $item['id'] === (string) $record->id)->push(['id' => (string) $record->id, 'name' => $translation?->name ?? '', 'products' => $products])->values()->all();
        } elseif ($resource === 'social-links') {
            $data['footer']['social_links'] = $data['footer']['social_links']->reject(fn ($item) => $item->id === $record->id)->push($record)->sortBy('display_order')->values();
        } elseif ($resource === 'mission-items') {
            $record->setAttribute('public_translation', $this->translation($record->translations, $language));
            $data['missions'] = $data['missions']->reject(fn ($item) => $item->id === $record->id)->push($record)->sortBy('display_order')->values();
        } else {
            $record->setAttribute('public_translation', $this->translation($record->translations, $language));
            $record->setAttribute('public_image_url', $this->mediaUrl($record->media));
            $record->setAttribute('public_image_alt', $record->media?->alt_text_id);
            $data['certificates'] = $data['certificates']->reject(fn ($item) => $item->id === $record->id)->push($record)->sortBy('display_order')->values();
        }

        return response()->view($resource === 'product-categories' ? 'produk' : ($isCareer ? 'careers' : ($isAbout ? 'tentang' : 'welcome')), $data)->header('X-Robots-Tag', 'noindex, nofollow')->header('Cache-Control', 'private, no-store');
    }

    public function home(): View
    {
        $language = app()->getLocale();
        $slides = HeroSlide::query()->where('status', 'published')->with(['media', 'translations'])->orderBy('display_order')->get();
        $blocks = $this->publishedBlocks($language);
        $brands = Brand::query()->where('status', 'published')->with('logo')->orderBy('display_order')->get();
        $slides->each(function (HeroSlide $slide) use ($language): void {
            $translation = $this->translation($slide->translations, $language);
            $slide->setAttribute('public_title', $translation?->title);
            $slide->setAttribute('public_subtitle', $translation?->subtitle);
            $slide->setAttribute('public_cta_label', $translation?->cta_label);
            $slide->setAttribute('public_image_url', $this->mediaUrl($slide->media));
            $slide->setAttribute('public_image_alt', $language === 'en' ? ($slide->media?->alt_text_en ?: $slide->media?->alt_text_id) : $slide->media?->alt_text_id);
        });
        $brands->each(function (Brand $brand) use ($language): void {
            $brand->setAttribute('public_logo_url', $this->mediaUrl($brand->logo));
            $brand->setAttribute('public_logo_alt', $language === 'en' ? ($brand->logo?->alt_text_en ?: $brand->logo?->alt_text_id) : $brand->logo?->alt_text_id);
        });

        return view('welcome', [
            'slides' => $slides,
            'blocks' => $blocks,
            'brands' => $brands,
            'summary' => CompanySummary::query()->first(),
            'footer' => $this->footerData($language),
            'seo' => $this->seoData('home', 'PT Interskala Medika Indonesia', 'PT Interskala Medika Indonesia melayani kebutuhan alat kesehatan di seluruh Indonesia.'),
        ]);
    }

    public function about(): View
    {
        $language = app()->getLocale();
        $blocks = $this->publishedBlocks($language);
        $summary = CompanySummary::query()->first();
        $certificates = Certificate::query()->where('status', 'published')->with(['media', 'translations'])->orderBy('display_order')->get();
        $certificates->each(function (Certificate $certificate) use ($language): void {
            $certificate->setAttribute('public_translation', $this->translation($certificate->translations, $language));
            $certificate->setAttribute('public_image_url', $this->mediaUrl($certificate->media));
            $certificate->setAttribute('public_image_alt', $language === 'en' ? ($certificate->media?->alt_text_en ?: $certificate->media?->alt_text_id) : $certificate->media?->alt_text_id);
        });

        $missions = MissionItem::query()->where('status', 'published')->with('translations')->orderBy('display_order')->get();
        $missions->each(fn (MissionItem $item) => $item->setAttribute('public_translation', $this->translation($item->translations, $language)));

        return view('tentang', [
            'language' => $language,
            'blocks' => $blocks,
            'missions' => $missions,
            'summary' => $summary,
            'certificates' => $certificates,
            'footer' => $this->footerData($language),
            'yearsEstablished' => $summary ? now()->year - (int) $summary->established_year : null,
            'seo' => $this->seoData('about', 'Tentang Kami - Interskala Medika Indonesia', 'Tentang PT Interskala Medika Indonesia, visi, misi, dan sertifikasi kami.'),
        ]);
    }

    public function contact(): View
    {
        $language = app()->getLocale();

        $contact = ContactInfo::query()->with('translations')->first();
        if ($contact) {
            $contact->setAttribute('public_translation', $this->translation($contact->translations, $language));
        }

        return view('kontak', [
            'contactInfo' => $contact,
            'socialLinks' => SocialLink::query()->where('status', 'published')->orderBy('display_order')->get(),
            'footer' => $this->footerData($language),
            'seo' => $this->seoData('contact', 'Hubungi Kami - Interskala Medika Indonesia', 'Informasi kontak PT Interskala Medika Indonesia.'),
        ]);
    }

    public function articles(Request $request): View
    {
        return $this->documentation($request, 'article');
    }

    public function news(Request $request): View
    {
        return $this->documentation($request, 'activity');
    }

    public function documentation(Request $request, ?string $forcedType = null): View
    {
        $language = app()->getLocale();
        $type = $forcedType ?? $request->string('type')->toString();
        $type = in_array($type, ['article', 'activity'], true) ? $type : null;
        $search = trim($request->string('q')->toString());
        $sortDirection = $request->string('sort')->toString() === 'oldest' ? 'asc' : 'desc';
        $year = $request->string('year')->toString();
        $year = preg_match('/^\d{4}$/', $year) ? $year : '';
        $years = Documentation::query()->where('status', 'published')->when($type, fn (Builder $query) => $query->where('type', $type))
            ->whereNotNull('event_date')->pluck('event_date')->map(fn ($date) => $date->format('Y'))->unique()->sortDesc()->values();
        $query = Documentation::query()->where('status', 'published')->with(['translations', 'cover', 'gallery']);

        if ($year !== '') {
            $query->whereYear('event_date', $year);
        }

        if ($search !== '') {
            $query->whereHas('translations', function (Builder $translationQuery) use ($language, $search): Builder {
                return $translationQuery->whereIn('lang', array_unique([$language, 'id']))
                    ->where(function (Builder $matchingTranslation) use ($search): Builder {
                        return $matchingTranslation->where('title', 'like', "%{$search}%")
                            ->orWhere('excerpt', 'like', "%{$search}%")
                            ->orWhere('body', 'like', "%{$search}%");
                    });
            });
        }

        $query->orderBy('event_date', $sortDirection)->orderByDesc('id');
        $articles = (clone $query)->where('type', 'article')->paginate(9, ['*'], 'articles_page')->withQueryString();
        $activities = (clone $query)->where('type', 'activity')->paginate(9, ['*'], 'activities_page')->withQueryString();
        $this->decorateDocumentationItems($articles, $language);
        $this->decorateDocumentationItems($activities, $language);

        if ($type === 'article') {
            $activities = new LengthAwarePaginator([], 0, 9);
        } elseif ($type === 'activity') {
            $articles = new LengthAwarePaginator([], 0, 9);
        }

        return view('dokumentasi', [
            'articles' => $articles,
            'activities' => $activities,
            'language' => $language,
            'selectedType' => $type,
            'search' => $search,
            'year' => $year,
            'years' => $years,
            'sort' => $sortDirection === 'asc' ? 'oldest' : 'newest',
            'footer' => $this->footerData($language),
            'seo' => $type === 'article'
                ? $this->seoData('articles', 'Artikel - Interskala Medika Indonesia', 'Artikel kesehatan terbaru dari IMI Medika.')
                : ($type === 'activity'
                    ? $this->seoData('news', 'Berita - Interskala Medika Indonesia', 'Berita dan kegiatan terbaru dari IMI Medika.')
                    : $this->seoData('documentation', 'Dokumentasi - Interskala Medika Indonesia', 'Artikel dan foto kegiatan PT Interskala Medika Indonesia.')),
        ]);
    }

    public function documentationDetail(string $locale, string $slug): View
    {
        $language = app()->getLocale();
        $item = Documentation::query()->where('status', 'published')->where('type', 'article')
            ->whereHas('translations', fn (Builder $query): Builder => $query->where('slug', $slug)->whereIn('lang', [$language, 'id']))
            ->with(['translations', 'cover', 'gallery'])->firstOrFail();
        $translation = $this->translation($item->translations, $language);
        $this->decorateDocumentationItem($item, $language);

        return view('public-detail', [
            'kind' => 'documentation',
            'item' => $item,
            'translation' => $translation,
            'language' => $language,
            'footer' => $this->footerData($language),
            'seo' => $this->detailSeo($item->translations, $translation?->title ?? 'Dokumentasi', $translation?->excerpt ?? '', 'documentation.show', $item->cover),
        ]);
    }

    public function activityDetail(string $locale, int $id): View
    {
        $language = app()->getLocale();
        $item = Documentation::query()->where('status', 'published')->where('type', 'activity')
            ->with(['translations', 'cover', 'gallery'])->findOrFail($id);
        $translation = $this->translation($item->translations, $language);
        $this->decorateDocumentationItem($item, $language);

        return view('public-detail', [
            'kind' => 'documentation',
            'item' => $item,
            'translation' => $translation,
            'language' => $language,
            'footer' => $this->footerData($language),
            'seo' => $this->seoData('documentation', $translation?->title ?? 'Dokumentasi', $translation?->excerpt ?? ''),
        ]);
    }

    public function careers(): View
    {
        $language = app()->getLocale();
        $jobs = JobOpening::query()->where('status', 'open')->where(fn (Builder $query): Builder => $query->whereNull('closing_date')->orWhereDate('closing_date', '>=', today()))
            ->with('translations')->orderByDesc('posted_at')->paginate(9)->withQueryString();
        $jobs->getCollection()->each(fn (JobOpening $job) => $job->setAttribute('public_translation', $this->translation($job->translations, $language)));
        $careerIntro = $this->publishedBlocks($language)->get('career_intro');

        return view('careers', [
            'jobs' => $jobs,
            'careerIntro' => $careerIntro,
            'language' => $language,
            'footer' => $this->footerData($language),
            'seo' => $this->seoData('careers', 'Karir - Interskala Medika Indonesia', 'Lowongan karir di PT Interskala Medika Indonesia.'),
        ]);
    }

    public function careerDetail(string $locale, int $id): View
    {
        $language = app()->getLocale();
        $job = JobOpening::query()->where('status', 'open')->where(fn (Builder $query): Builder => $query->whereNull('closing_date')->orWhereDate('closing_date', '>=', today()))
            ->with('translations')->findOrFail($id);
        $translation = $this->translation($job->translations, $language);

        return view('public-detail', [
            'kind' => 'career',
            'job' => $job,
            'translation' => $translation,
            'language' => $language,
            'footer' => $this->footerData($language),
            'seo' => $this->seoData('careers', $translation?->title ?? 'Karir', strip_tags($translation?->description ?? '')),
        ]);
    }

    public function productDetail(string $locale, string $slug): View
    {
        $language = app()->getLocale();
        $product = Product::query()->where('status', 'published')->whereHas('category', fn (Builder $query): Builder => $query->where('status', 'published'))
            ->whereHas('translations', fn (Builder $query): Builder => $query->where('slug', $slug)->whereIn('lang', [$language, 'id']))
            ->with(['translations', 'media', 'category.translations'])->firstOrFail();
        $translation = $this->translation($product->translations, $language);
        $product->setAttribute('public_image_url', $this->mediaUrl($product->media));
        $product->setAttribute('public_image_alt', $language === 'en'
            ? ($product->media?->alt_text_en ?: $product->media?->alt_text_id)
            : $product->media?->alt_text_id);
        $product->setAttribute('public_brochure_url', $this->fileUrl($product->brochure_path));

        return view('public-detail', [
            'kind' => 'product',
            'product' => $product,
            'translation' => $translation,
            'language' => $language,
            'footer' => $this->footerData($language),
            'seo' => $this->detailSeo($product->translations, $translation?->name ?? 'Produk', $translation?->description ?? '', 'products.show', $product->media),
        ]);
    }

    /** @return Collection<string, array<string, mixed>> */
    private function publishedBlocks(string $language): Collection
    {
        return ContentBlock::query()->where('status', 'published')->with(['translations', 'media'])->get()
            ->mapWithKeys(function (ContentBlock $block) use ($language): array {
                $translation = $this->translation($block->translations, $language);

                return [$block->block_key => [
                    'title' => $translation?->title,
                    'subtitle' => $translation?->subtitle,
                    'body' => $translation?->body,
                    'cta_label' => $translation?->cta_label,
                    'copyright_text' => $translation?->copyright_text,
                    'metrics' => $translation?->metrics ?? [],
                    'buttons' => $translation?->buttons ?? [],
                    'cta_url' => $block->cta_url,
                    'image_url' => $this->mediaUrl($block->media),
                    'image_alt' => $language === 'en' ? ($block->media?->alt_text_en ?: $block->media?->alt_text_id) : $block->media?->alt_text_id,
                ]];
            });
    }

    /** @return array<string, mixed> */
    private function footerData(string $language): array
    {
        $contact = ContactInfo::query()->with('translations')->first();
        $translation = $contact ? $this->translation($contact->translations, $language) : null;
        $about = $this->publishedBlocks($language)->get('footer_about');

        return [
            'about' => $about,
            'contact' => $contact ? ['phone' => $contact->phone, 'email' => $contact->email, 'address' => $translation?->address] : null,
            'social_links' => SocialLink::query()->where('status', 'published')->orderBy('display_order')->get(),
            'copyright_text' => $about ? ($about['copyright_text'] ?? null) : null,
        ];
    }

    private function seoData(string $pageKey, string $fallbackTitle, string $fallbackDescription): array
    {
        $language = app()->getLocale();
        $seo = SeoMeta::query()->where('page_key', $pageKey)->where('lang', $language)->with('ogImage')->first();
        $alternateSeo = SeoMeta::query()->where('page_key', $pageKey)->where('lang', $language === 'id' ? 'en' : 'id')->first();

        return [
            'title' => $seo?->meta_title ?: $fallbackTitle,
            'description' => $seo?->meta_description ?: mb_substr(trim(strip_tags($fallbackDescription)), 0, 160),
            'canonical' => $seo?->canonical_url ?: request()->url(),
            'robots_index' => $seo?->robots_index ?? true,
            'og_image' => $seo?->ogImage ? $this->mediaUrl($seo->ogImage) : null,
            'language' => $language,
            'alternate' => $alternateSeo?->canonical_url,
        ];
    }

    private function translation(Collection $translations, string $language): ?object
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

    private function detailSeo(Collection $translations, string $title, string $description, string $route, ?object $image): array
    {
        $other = app()->getLocale() === 'id' ? 'en' : 'id';
        $alternate = $this->translation($translations, $other);

        return [
            'title' => $title,
            'description' => mb_substr(trim(strip_tags($description)), 0, 160),
            'canonical' => request()->url(),
            'robots_index' => true,
            'og_image' => $this->mediaUrl($image),
            'alternate' => $alternate?->slug ? route('localized.'.$route, ['locale' => $other, 'slug' => $alternate->slug]) : null,
        ];
    }

    private function decorateDocumentationItems(LengthAwarePaginator $items, string $language): void
    {
        $items->getCollection()->each(fn (Documentation $item) => $this->decorateDocumentationItem($item, $language));
    }

    private function decorateDocumentationItem(Documentation $item, string $language): void
    {
        $translation = $this->translation($item->translations, $language);
        $item->setAttribute('public_title', $translation?->title);
        $item->setAttribute('public_slug', $translation?->slug);
        $item->setAttribute('public_excerpt', $translation?->excerpt);
        $item->setAttribute('public_body', $translation?->body);
        $item->setAttribute('public_cover_url', $this->mediaUrl($item->cover));
        $item->setAttribute('public_cover_alt', $language === 'en' ? ($item->cover?->alt_text_en ?: $item->cover?->alt_text_id) : $item->cover?->alt_text_id);
        $item->setAttribute('public_gallery', $item->gallery->map(fn ($media): array => [
            'url' => $this->mediaUrl($media),
            'alt' => $language === 'en' ? ($media->alt_text_en ?: $media->alt_text_id) : $media->alt_text_id,
            'caption' => $language === 'en' ? ($media->pivot->caption_en ?: $media->pivot->caption_id) : $media->pivot->caption_id,
        ])->all());
    }

    private function mediaUrl(?object $media): ?string
    {
        if ($media === null) {
            return null;
        }

        return $this->fileUrl($media->path);
    }

    private function fileUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = ltrim($path, '/');

        return file_exists(public_path($path)) ? asset($path) : Storage::disk(config('cms.media.disk'))->url($path);
    }
}
