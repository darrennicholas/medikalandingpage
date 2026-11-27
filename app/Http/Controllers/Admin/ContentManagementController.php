<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
use App\Models\Media;
use App\Models\MissionItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SeoMeta;
use App\Models\SocialLink;
use App\Services\ActivityLogger;
use App\Services\PublicApiCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentManagementController extends Controller
{
    public function subcategories(Request $request): View
    {
        $editingSubcategory = $request->filled('edit')
            ? DB::table('product_subcategories')->where('id', $request->integer('edit'))->first()
            : null;
        abort_if($request->filled('edit') && ! $editingSubcategory, 404);

        return view('admin.content.product-subcategories-index', [
            'subcategoryCategories' => ProductCategory::query()->with('translations')->withCount('products')->orderBy('display_order')->orderBy('id')->get(),
            'subcategories' => DB::table('product_subcategories')->select('product_subcategories.*')
                ->selectSub(DB::table('products')->selectRaw('COUNT(*)')
                    ->whereColumn('products.category_id', 'product_subcategories.category_id')
                    ->whereColumn('products.subcategory', 'product_subcategories.name'), 'products_count')
                ->orderBy('name')->get()->groupBy('category_id'),
            'editingSubcategory' => $editingSubcategory,
        ]);
    }

    public function updateSubcategory(Request $request, int $id, ActivityLogger $activityLogger, PublicApiCache $publicApiCache): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('product_categories', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:100', Rule::unique('product_subcategories', 'name')->where('category_id', $request->input('category_id'))->ignore($id)],
        ]);
        DB::transaction(function () use ($request, $id, $data, $activityLogger): void {
            $subcategory = DB::table('product_subcategories')->where('id', $id)->lockForUpdate()->first();
            abort_unless($subcategory, 404);
            DB::table('product_subcategories')->where('id', $id)->update([...$data, 'updated_at' => now()]);
            DB::table('products')->where('category_id', $subcategory->category_id)->where('subcategory', $subcategory->name)
                ->update(['category_id' => $data['category_id'], 'subcategory' => $data['name'], 'updated_at' => now()]);
            $activityLogger->log($request->user(), 'update', 'product_subcategory', $id, (array) $subcategory, $data);
        });
        $publicApiCache->invalidate();

        return redirect()->route('admin.subcategories.index')->with('status', 'Subkategori dan produk terkait berhasil diperbarui.');
    }

    public function destroySubcategory(Request $request, int $id, ActivityLogger $activityLogger, PublicApiCache $publicApiCache): RedirectResponse
    {
        DB::transaction(function () use ($request, $id, $activityLogger): void {
            $subcategory = DB::table('product_subcategories')->where('id', $id)->lockForUpdate()->first();
            abort_unless($subcategory, 404);
            foreach (Product::query()->where('category_id', $subcategory->category_id)->where('subcategory', $subcategory->name)->lockForUpdate()->get() as $product) {
                $oldValues = $product->toArray();
                $product->featuredCategories()->detach();
                $product->delete();
                $activityLogger->log($request->user(), 'delete', Product::class, $product->id, $oldValues);
            }
            DB::table('product_subcategories')->where('id', $id)->delete();
            $activityLogger->log($request->user(), 'delete', 'product_subcategory', $id, (array) $subcategory);
        });
        $publicApiCache->invalidate();

        return redirect()->route('admin.subcategories.index')->with('status', 'Subkategori beserta produk di dalamnya berhasil dihapus.');
    }

    public function storeSubcategory(Request $request, ActivityLogger $activityLogger, PublicApiCache $publicApiCache): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('product_categories', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:100', Rule::unique('product_subcategories', 'name')->where('category_id', $request->input('category_id'))],
        ]);
        $id = DB::table('product_subcategories')->insertGetId([...$data, 'created_at' => now(), 'updated_at' => now()]);
        $activityLogger->log($request->user(), 'create', 'product_subcategory', $id, null, $data);
        $publicApiCache->invalidate();

        return redirect()->route('admin.subcategories.index')->with('status', 'Subkategori berhasil ditambahkan.');
    }

    /** @var array<string, class-string<Model>> */
    private const MODELS = [
        'hero-slides' => HeroSlide::class, 'content-blocks' => ContentBlock::class, 'brands' => Brand::class,
        'mission-items' => MissionItem::class, 'company-summary' => CompanySummary::class, 'certificates' => Certificate::class,
        'product-categories' => ProductCategory::class, 'products' => Product::class, 'documentations' => Documentation::class,
        'job-openings' => JobOpening::class, 'contact-info' => ContactInfo::class, 'social-links' => SocialLink::class, 'seo-metas' => SeoMeta::class,
    ];

    private const LABELS = [
        'hero-slides' => 'Hero / Banner', 'content-blocks' => 'Blok Konten', 'brands' => 'Brand Kami',
        'mission-items' => 'Visi & Misi', 'company-summary' => 'Summary Perusahaan', 'certificates' => 'Sertifikat & Penghargaan',
        'product-categories' => 'Pengaturan Kategori', 'products' => 'Daftar Produk & Brosur', 'documentations' => 'Dokumentasi',
        'job-openings' => 'Lowongan Karir', 'contact-info' => 'Informasi Kontak', 'social-links' => 'Sosial Media & Footer', 'seo-metas' => 'Pengaturan SEO',
    ];

    public function index(Request $request, string $resource): View
    {
        $modelClass = $this->modelClass($resource);
        $contentBlockSection = $request->query('section');

        if ($resource === 'brands') {
            $brandStats = Brand::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();
            $brandQuery = Brand::query()->with('logo');

            if ($request->filled('status')) {
                $brandQuery->where('status', $request->string('status')->toString());
            }
            if ($request->filled('q')) {
                $brandQuery->where('name', 'like', '%'.$request->string('q')->toString().'%');
            }

            $perPage = (int) $request->query('per_page', 10);
            if (! in_array($perPage, [10, 25, 50], true)) {
                $perPage = 10;
            }

            $brandRecords = $brandQuery->orderBy('display_order')->paginate($perPage)->withQueryString();
            $brandRecords->getCollection()->each(function (Brand $brand): void {
                if ($brand->logo) {
                    $brand->logo->setAttribute('admin_preview_url', $this->mediaUrl($brand->logo));
                }
            });

            return view('admin.content.brands-index', [
                'records' => $brandRecords,
                'brandStats' => $brandStats,
                'brandIds' => Brand::query()->orderBy('display_order')->orderBy('id')->pluck('id')->all(),
            ]);
        }

        if ($resource === 'product-categories') {
            $categoryStats = ProductCategory::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();
            $categoryQuery = ProductCategory::query()->with('translations')->withCount('products');

            if ($request->filled('status')) {
                $categoryQuery->where('status', $request->string('status')->toString());
            }
            if ($request->filled('q')) {
                $searchTerm = '%'.$request->string('q')->toString().'%';
                $categoryQuery->whereHas('translations', function ($translationQuery) use ($searchTerm): void {
                    $translationQuery->where('name', 'like', $searchTerm)->orWhere('slug', 'like', $searchTerm);
                });
            }

            $perPage = (int) $request->query('per_page', 10);
            if (! in_array($perPage, [10, 25, 50], true)) {
                $perPage = 10;
            }

            return view('admin.content.product-categories-index', [
                'records' => $categoryQuery->orderBy('display_order')->paginate($perPage)->withQueryString(),
                'categoryStats' => $categoryStats,
                'categoryIds' => ProductCategory::query()->orderBy('display_order')->pluck('id')->all(),
            ]);
        }

        if ($resource === 'company-summary') {
            $record = CompanySummary::query()->first() ?? new CompanySummary;

            return view('admin.content.company-summary-form', [
                'record' => $record,
                'apiUrl' => '/api/v1/admin/company-summary',
                'redirectUrl' => route('admin.content.index', 'company-summary'),
            ]);
        }

        if ($resource === 'certificates') {
            $certificateStats = Certificate::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();
            $certificateQuery = Certificate::query()->with(['translations', 'media']);

            if ($request->filled('status')) {
                $certificateQuery->where('status', $request->string('status')->toString());
            }
            if ($request->filled('year')) {
                $certificateQuery->where('issued_year', $request->integer('year'));
            }
            if ($request->filled('q')) {
                $searchTerm = '%'.$request->string('q')->toString().'%';
                $certificateQuery->whereHas('translations', fn ($translationQuery) => $translationQuery->where('title', 'like', $searchTerm));
            }

            $perPage = (int) $request->query('per_page', 10);
            if (! in_array($perPage, [10, 25, 50], true)) {
                $perPage = 10;
            }

            $certificateRecords = $certificateQuery->orderBy('display_order')->paginate($perPage)->withQueryString();
            $certificateRecords->getCollection()->each(function (Certificate $certificate): void {
                if ($certificate->media) {
                    $certificate->media->setAttribute('admin_preview_url', $this->mediaUrl($certificate->media));
                }
            });

            return view('admin.content.certificates-index', [
                'records' => $certificateRecords,
                'certificateStats' => $certificateStats,
                'certificateIds' => Certificate::query()->orderBy('display_order')->orderBy('id')->pluck('id')->all(),
                'certificateYears' => Certificate::query()->whereNotNull('issued_year')->distinct()->orderByDesc('issued_year')->pluck('issued_year'),
            ]);
        }

        if ($resource === 'products') {
            $productStats = Product::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();
            $productQuery = Product::query()->with(['translations', 'category.translations', 'media', 'featuredCategories']);

            if ($request->filled('status')) {
                $productQuery->where('status', $request->string('status')->toString());
            }
            if ($request->filled('category')) {
                $productQuery->where('category_id', $request->integer('category'));
            }
            if ($request->filled('featured')) {
                if ($request->string('featured')->toString() === 'yes') {
                    $productQuery->whereHas('featuredCategories');
                } else {
                    $productQuery->whereDoesntHave('featuredCategories');
                }
            }
            if ($request->filled('q')) {
                $searchTerm = '%'.$request->string('q')->toString().'%';
                $productQuery->whereHas('translations', fn ($translationQuery) => $translationQuery
                    ->where('name', 'like', $searchTerm)
                    ->orWhere('slug', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm));
            }

            $perPage = (int) $request->query('per_page', 10);
            if (! in_array($perPage, [10, 25, 50], true)) {
                $perPage = 10;
            }

            $records = $productQuery->orderBy('display_order')->paginate($perPage)->withQueryString();
            $records->getCollection()->each(function (Product $product): void {
                if ($product->media) {
                    $product->media->setAttribute('admin_preview_url', $this->mediaUrl($product->media));
                }
            });

            return view('admin.content.index', [
                'resource' => $resource,
                'title' => self::LABELS[$resource],
                'records' => $records,
                'modelClass' => $modelClass,
                'heroStats' => null,
                'heroSlideIds' => [],
                'productStats' => $productStats,
                'productCategories' => ProductCategory::query()->with('translations')->orderBy('display_order')->get(),
            ]);
        }

        if ($resource === 'content-blocks' && $contentBlockSection === 'about_company') {
            $record = ContentBlock::query()->with(['translations', 'media'])->firstOrNew(['block_key' => 'about_company']);
            $record->loadMissing(['translations', 'media']);
            $this->applyDraftPayload($record, 'content-blocks');

            return $this->aboutCompanyFormView($record);
        }

        if ($resource === 'content-blocks' && $contentBlockSection === 'footer_about') {
            $record = ContentBlock::query()->with(['translations', 'media'])->firstOrNew(['block_key' => 'footer_about']);
            $record->loadMissing(['translations', 'media']);
            $this->applyDraftPayload($record, 'content-blocks');

            return $this->formView($resource, $record);
        }

        if ($resource === 'mission-items') {
            $missionQuery = MissionItem::query()->with('translations');
            if ($request->filled('status')) {
                $missionQuery->where('status', $request->string('status')->toString());
            }
            if ($request->filled('q')) {
                $searchTerm = '%'.$request->string('q')->toString().'%';
                $missionQuery->whereHas('translations', fn ($translationQuery) => $translationQuery->where('text', 'like', $searchTerm));
            }

            $perPage = (int) $request->query('per_page', 10);
            if (! in_array($perPage, [10, 25, 50], true)) {
                $perPage = 10;
            }

            $visionBlock = ContentBlock::query()->with(['translations', 'media'])->firstOrNew(['block_key' => 'about_vision']);
            $visionBlock->loadMissing(['translations', 'media']);
            $this->applyDraftPayload($visionBlock, 'content-blocks');
            $missionStats = MissionItem::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();

            return view('admin.content.vision-mission-index', [
                'records' => $missionQuery->orderBy('display_order')->paginate($perPage)->withQueryString(),
                'missionStats' => $missionStats,
                'missionIds' => MissionItem::query()->orderBy('display_order')->orderBy('id')->pluck('id')->all(),
                'visionBlock' => $visionBlock,
                'visionTranslationId' => $visionBlock->translations->firstWhere('lang', 'id'),
                'visionTranslationEn' => $visionBlock->translations->firstWhere('lang', 'en'),
                'visionTranslationZh' => $visionBlock->translations->firstWhere('lang', 'zh'),
            ]);
        }

        if ($resource === 'content-blocks' && in_array($contentBlockSection, ['home_coverage', 'home_cta', 'career_intro'], true)) {
            $record = ContentBlock::query()->with(['translations', 'media'])->firstOrNew(['block_key' => $contentBlockSection]);
            $record->loadMissing(['translations', 'media']);
            $this->applyDraftPayload($record, 'content-blocks');

            return $this->blockFormView($record, $contentBlockSection);
        }

        if ($resource === 'contact-info') {
            $records = ContactInfo::query()->with('translations')->orderBy('id')->get();
            if ($records->isEmpty()) {
                $records->push(new ContactInfo);
            }

            return view('admin.content.contact-info', [
                'records' => $records,
            ]);
        }

        if ($resource === 'social-links') {
            $socialQuery = SocialLink::query()->with('previewMedia');

            if ($request->filled('status')) {
                $socialQuery->where('status', $request->string('status')->toString());
            }
            if ($request->filled('q')) {
                $searchTerm = '%'.$request->string('q')->toString().'%';
                $socialQuery->where(fn ($query) => $query->where('platform', 'like', $searchTerm)->orWhere('url', 'like', $searchTerm));
            }

            $footerBlock = ContentBlock::query()->with('translations')->firstOrNew(['block_key' => 'footer_about']);
            $footerBlock->loadMissing('translations');

            $socialRecords = $socialQuery->orderBy('display_order')->paginate(in_array($request->integer('per_page'), [10, 25, 50], true) ? $request->integer('per_page') : 10)->withQueryString();
            $socialRecords->getCollection()->each(function (SocialLink $socialLink): void {
                if ($socialLink->previewMedia) {
                    $socialLink->previewMedia->setAttribute('admin_preview_url', $this->mediaUrl($socialLink->previewMedia));
                }
            });

            return view('admin.content.social-footer-index', [
                'records' => $socialRecords,
                'socialTotal' => SocialLink::query()->count(),
                'socialActive' => SocialLink::query()->where('status', 'published')->count(),
                'footerBlock' => $footerBlock,
                'footerTranslations' => $footerBlock->translations->keyBy('lang'),
            ]);
        }

        if ($resource === 'seo-metas') {
            $pageLabels = [
                'home' => 'Beranda',
                'about' => 'Tentang Perusahaan',
                'products' => 'Produk & Alat Medis',
                'documentation' => 'Dokumentasi',
                'careers' => 'Karir & Rekrutmen',
                'contact' => 'Kontak & Layanan',
            ];
            $seoQuery = SeoMeta::query()->with('ogImage');
            if ($request->filled('q')) {
                $searchTerm = '%'.$request->string('q')->toString().'%';
                $seoQuery->where(fn ($query) => $query->where('page_key', 'like', $searchTerm)->orWhere('meta_title', 'like', $searchTerm));
            }
            $selectedPage = $request->string('page', 'home')->toString();
            $selectedLanguage = $request->string('lang', 'id')->toString();
            abort_unless(isset($pageLabels[$selectedPage]) && in_array($selectedLanguage, config('cms.content.supported_languages'), true), 404);
            $selectedMeta = SeoMeta::query()->with('ogImage')->firstOrNew(['page_key' => $selectedPage, 'lang' => $selectedLanguage]);
            $mediaOptions = Media::query()->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])->latest('created_at')->limit(100)->get();
            $mediaOptions->each(function (Media $media): void {
                $media->setAttribute('admin_preview_url', $this->mediaUrl($media));
            });
            $coveragePreviewMedia = ContentBlock::query()
                ->with('media')
                ->where('block_key', 'home_coverage')
                ->where('status', 'published')
                ->first()
                ?->media;

            return view('admin.content.seo-index', [
                'records' => $seoQuery->orderBy('page_key')->orderBy('lang')->get(),
                'pageLabels' => $pageLabels,
                'selectedPage' => $selectedPage,
                'selectedLanguage' => $selectedLanguage,
                'selectedMeta' => $selectedMeta,
                'mediaOptions' => $mediaOptions,
                'sitemapPreviewImage' => $this->mediaUrl($coveragePreviewMedia) ?? asset('images/coverage-map.jpg'),
                'sitemapPreviewAlt' => $coveragePreviewMedia?->alt_text_id ?: 'Peta jangkauan distribusi Interskala Medika',
                'seoTotal' => SeoMeta::query()->count(),
                'seoIndexed' => SeoMeta::query()->where('robots_index', true)->count(),
                'seoComplete' => SeoMeta::query()->whereNotNull('meta_title')->whereNotNull('meta_description')->count(),
            ]);
        }

        if (in_array($resource, ['documentations', 'job-openings'], true)) {
            $isDocumentation = $resource === 'documentations';
            $query = $modelClass::query()->with($isDocumentation ? ['translations', 'cover'] : ['translations']);
            if ($isDocumentation) {
                $query->withCount('gallery');
            }
            foreach (['status', 'type', 'location'] as $filter) {
                if ($request->filled($filter) && ($filter === 'status' || ($filter === 'type' && $isDocumentation) || ($filter === 'location' && ! $isDocumentation))) {
                    $query->where($filter, $request->string($filter)->toString());
                }
            }
            if ($request->filled('q')) {
                $search = '%'.$request->string('q')->toString().'%';
                $query->whereHas('translations', fn ($translations) => $translations->where('title', 'like', $search));
            }
            $perPage = (int) $request->query('per_page', 10);
            $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
            $records = ($isDocumentation ? $query->orderByDesc('event_date')->orderByDesc('id') : $query->latest('id'))->paginate($perPage)->withQueryString();
            if ($isDocumentation) {
                $records->getCollection()->each(function (Documentation $item): void {
                    if ($item->cover) {
                        $item->cover->setAttribute('admin_preview_url', $this->mediaUrl($item->cover));
                    }
                });
            }

            return view('admin.content.publications-index', [
                'resource' => $resource,
                'records' => $records,
                'total' => $modelClass::query()->count(),
                'publishedCount' => $isDocumentation ? $modelClass::query()->where('status', 'published')->count() : 0,
                'primaryCount' => $modelClass::query()->where($isDocumentation ? 'type' : 'status', $isDocumentation ? 'article' : 'open')->count(),
                'secondaryCount' => $modelClass::query()->where($isDocumentation ? 'type' : 'status', $isDocumentation ? 'activity' : 'closed')->count(),
                'closingCount' => $isDocumentation ? 0 : JobOpening::query()->where('status', 'open')->whereBetween('closing_date', [today(), today()->addDays(7)])->count(),
            ]);
        }

        $query = $modelClass::query();
        if ($resource === 'content-blocks' && in_array($contentBlockSection, ['home_coverage', 'home_cta', 'about_company'], true)) {
            $query->where('block_key', $contentBlockSection);
        }
        $relations = match ($resource) {
            'hero-slides' => ['translations', 'media'],
            'products' => ['translations', 'category.translations', 'media'],
            'certificates' => ['translations', 'media'],
            'documentations' => ['translations', 'cover'],
            default => method_exists($modelClass, 'translations') ? ['translations'] : [],
        };
        if ($relations !== []) {
            $query->with($relations);
        }
        if ($request->filled('status') && in_array('status', (new $modelClass)->getFillable(), true)) {
            $query->where('status', $request->string('status')->toString());
        }
        if ($request->filled('q')) {
            $searchTerm = '%'.$request->string('q')->toString().'%';
            if (method_exists($modelClass, 'translations')) {
                $query->whereHas('translations', function ($translationQuery) use ($resource, $searchTerm): void {
                    $translationQuery->where('title', 'like', $searchTerm);
                    if (! in_array($resource, ['documentations', 'job-openings'], true)) {
                        $translationQuery->orWhere('name', 'like', $searchTerm);
                    }
                });
            } elseif (in_array('name', (new $modelClass)->getFillable(), true)) {
                $query->where('name', 'like', $searchTerm);
            }
        }
        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }
        $records = in_array('display_order', (new $modelClass)->getFillable(), true)
            ? $query->orderBy('display_order')->paginate($perPage)->withQueryString()
            : $query->latest('id')->paginate($perPage)->withQueryString();
        $heroStats = null;
        $heroSlideIds = [];

        if ($resource === 'hero-slides') {
            $heroStats = HeroSlide::query()
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();
            $heroSlideIds = HeroSlide::query()->orderBy('display_order')->pluck('id')->all();
        }

        return view('admin.content.index', [
            'resource' => $resource,
            'title' => self::LABELS[$resource],
            'records' => $records,
            'modelClass' => $modelClass,
            'heroStats' => $heroStats,
            'heroSlideIds' => $heroSlideIds,
        ]);
    }

    public function create(string $resource): View
    {
        $record = new (self::MODELS[$resource] ?? $this->modelClass($resource))();
        if ($resource === 'documentations') {
            $type = request()->query('type', 'article');
            $record->setAttribute('type', in_array($type, ['article', 'activity'], true) ? $type : 'article');
        }

        return $this->formView($resource, $record);
    }

    public function edit(string $resource, string $id): View
    {
        $record = $this->record($resource, $id);
        if (method_exists($record, 'translations')) {
            $record->load($resource === 'hero-slides' ? ['translations', 'media'] : ['translations']);
            $this->applyDraftPayload($record, $resource);
        }
        if ($resource === 'documentations') {
            $record->loadMissing('gallery');
        }

        return $this->formView($resource, $record);
    }

    public function show(string $resource, string $id): View
    {
        abort_unless($resource === 'hero-slides', 404);
        $record = $this->record($resource, $id)->load(['translations', 'media']);

        return view('admin.content.hero-detail', [
            'record' => $record,
            'translationId' => $record->translations->firstWhere('lang', 'id'),
            'translationEn' => $record->translations->firstWhere('lang', 'en'),
            'translationZh' => $record->translations->firstWhere('lang', 'zh'),
            'mediaUrl' => $this->mediaUrl($record->media),
        ]);
    }

    private function formView(string $resource, Model $record): View
    {
        if ($resource === 'hero-slides') {
            return view('admin.content.hero-form', [
                'record' => $record,
                'translationId' => $record->translations->firstWhere('lang', 'id'),
                'translationEn' => $record->translations->firstWhere('lang', 'en'),
                'translationZh' => $record->translations->firstWhere('lang', 'zh'),
                'mediaOptions' => $this->mediaOptions(true),
                'isEditing' => $record->exists,
                'apiUrl' => '/api/v1/admin/hero-slides'.($record->exists ? '/'.$record->getKey() : ''),
                'redirectUrl' => route('admin.content.index', 'hero-slides'),
                'mediaUrl' => $this->mediaUrl($record->media),
            ]);
        }

        if ($resource === 'certificates') {
            $record->loadMissing(['translations', 'media']);

            if (! $record->exists) {
                return view('admin.content.certificate-batch-form', [
                    'mediaOptions' => $this->mediaOptions(),
                    'apiUrl' => '/api/v1/admin/certificates',
                    'redirectUrl' => route('admin.content.index', 'certificates'),
                ]);
            }

            return view('admin.content.certificate-form', [
                'record' => $record,
                'translations' => $record->translations->keyBy('lang'),
                'mediaOptions' => $this->mediaOptions(),
                'isEditing' => $record->exists,
                'apiUrl' => '/api/v1/admin/certificates'.($record->exists ? '/'.$record->getKey() : ''),
                'redirectUrl' => route('admin.content.index', 'certificates'),
                'mediaUrl' => $this->mediaUrl($record->media),
            ]);
        }

        if ($resource === 'products' && ! $record->exists) {
            $defaultLogos = Product::query()
                ->whereHas('topLogo')->whereHas('bottomLogo')
                ->select('logo_top_media_id', 'logo_bottom_media_id')
                ->selectRaw('COUNT(*) as usage_count')
                ->groupBy('logo_top_media_id', 'logo_bottom_media_id')
                ->orderByDesc('usage_count')->orderBy('logo_top_media_id')->orderBy('logo_bottom_media_id')
                ->first();

            if ($defaultLogos) {
                $record->forceFill([
                    'logo_top_media_id' => $defaultLogos->logo_top_media_id,
                    'logo_bottom_media_id' => $defaultLogos->logo_bottom_media_id,
                ]);
            }
        }

        $definitions = $this->definitions($resource);
        if ($resource === 'content-blocks' && $record->getAttribute('block_key') === 'footer_about') {
            $definitions = [
                'common' => [['name' => 'block_key', 'label' => 'Section', 'type' => 'hidden', 'required' => true, 'value' => 'footer_about']],
                'id' => [
                    ['name' => 'translations[id][body]', 'label' => 'Teks singkat perusahaan di footer', 'type' => 'textarea', 'required' => false, 'maxlength' => 300],
                    ['name' => 'translations[id][copyright_text]', 'label' => 'Teks hak cipta', 'type' => 'text', 'required' => false, 'maxlength' => 150],
                ],
                'en' => [
                    ['name' => 'translations[en][body]', 'label' => 'Teks singkat perusahaan di footer', 'type' => 'textarea', 'required' => false, 'maxlength' => 300],
                    ['name' => 'translations[en][copyright_text]', 'label' => 'Teks hak cipta', 'type' => 'text', 'required' => false, 'maxlength' => 150],
                ],
                'zh' => [
                    ['name' => 'translations[zh][body]', 'label' => 'Teks singkat perusahaan di footer', 'type' => 'textarea', 'required' => false, 'maxlength' => 300],
                    ['name' => 'translations[zh][copyright_text]', 'label' => 'Teks hak cipta', 'type' => 'text', 'required' => false, 'maxlength' => 150],
                ],
            ];
        }
        if ($resource === 'documentations') {
            foreach ($definitions['common'] as &$field) {
                if ($field['name'] === 'type') {
                    $field = ['name' => 'type', 'type' => 'hidden', 'value' => $record->type ?: 'article'];
                }
            }
            unset($field);
            if ($record->type === 'activity') {
                foreach (config('cms.content.supported_languages') as $language) {
                    foreach ($definitions[$language] as &$field) {
                        if (str_ends_with($field['name'], '[body]')) {
                            $field['label'] = 'Isi Berita';
                        }
                    }
                    unset($field);
                }
            }
        }
        if (in_array('status', $record->getFillable(), true)) {
            $statusField = ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => $resource === 'job-openings'
                ? ['draft' => 'Draft', 'open' => 'Open', 'closed' => 'Closed']
                : ['draft' => 'Draft', 'published' => 'Published']];
            if ($resource === 'documentations') {
                $galleryIndex = array_search('gallery', array_column($definitions['common'], 'name'), true);
                if ($galleryIndex !== false) {
                    array_splice($definitions['common'], $galleryIndex, 0, [$statusField]);
                } else {
                    $definitions['common'][] = $statusField;
                }
            } else {
                $definitions['common'][] = $statusField;
            }
        }
        $translations = method_exists($record, 'translations') ? $record->translations : collect();
        $mediaOptions = $this->mediaOptions();
        $categoryOptions = ProductCategory::query()->where(function ($query) use ($record, $resource): void {
            $query->where('status', 'published');
            if ($resource === 'products' && $record->getAttribute('category_id')) {
                $query->orWhere('id', $record->getAttribute('category_id'));
            }
        })->with('translations')->orderBy('display_order')->get();
        foreach ($definitions['common'] as &$field) {
            if ($resource === 'products' && $field['name'] === 'category_id') {
                $field['options'] = $categoryOptions->mapWithKeys(fn (ProductCategory $category): array => [$category->id => ($category->translations->firstWhere('lang', 'id')?->name ?? 'Kategori '.$category->id).($category->status !== 'published' ? ' (tidak aktif)' : '')])->all();
            }
            if ($resource === 'products' && $field['name'] === 'subcategory') {
                $field['suggestions'] = DB::table('product_subcategories')->orderBy('name')->get();
            }
            if ($resource === 'content-blocks' && $record->getAttribute('block_key') === 'footer_about' && $field['name'] === 'block_key') {
                $field['options'] = ['footer_about' => 'Tentang Footer'];
            }
        }
        unset($field);

        return view('admin.content.form', [
            'resource' => $resource,
            'title' => match (true) {
                $resource === 'documentations' => $record->type === 'activity' ? 'Berita / Foto Kegiatan' : 'Artikel',
                $resource === 'content-blocks' && $record->getAttribute('block_key') === 'footer_about' => 'Teks Footer',
                default => self::LABELS[$resource],
            },
            'record' => $record,
            'definitions' => $definitions,
            'translations' => $translations->keyBy('lang'),
            'mediaOptions' => $mediaOptions,
            'categoryOptions' => $categoryOptions,
            'isEditing' => $record->exists,
            'apiUrl' => match ($resource) {
                'company-summary' => '/api/v1/admin/company-summary',
                'contact-info' => '/api/v1/admin/contact-info',
                'seo-metas' => '/api/v1/admin/seo-metas',
                default => '/api/v1/admin/'.$resource.($record->exists ? '/'.($record->block_key ?? $record->id) : ''),
            },
        ]);
    }

    private function blockFormView(ContentBlock $record, string $blockKey): View
    {
        return view('admin.content.block-form', [
            'record' => $record,
            'blockKey' => $blockKey,
            'title' => match ($blockKey) {
                'home_coverage' => 'Jangkauan Kami', 'career_intro' => 'Pengantar Karir', default => 'CTA Hubungi Kami'
            },
            'translations' => $record->translations->keyBy('lang'),
            'mediaOptions' => $this->mediaOptions(),
            'apiUrl' => '/api/v1/admin/content-blocks/'.$blockKey,
            'redirectUrl' => route('admin.content.index', ['resource' => 'content-blocks', 'section' => $blockKey]),
            'mediaUrl' => $this->mediaUrl($record->media),
            'fallbackMetrics' => CompanySummary::query()->first(),
        ]);
    }

    private function aboutCompanyFormView(ContentBlock $record): View
    {
        $translationId = $record->translations->firstWhere('lang', 'id');
        $translationEn = $record->translations->firstWhere('lang', 'en');
        $translationZh = $record->translations->firstWhere('lang', 'zh');

        return view('admin.content.about-company-form', [
            'record' => $record,
            'translationId' => $translationId,
            'translationEn' => $translationEn,
            'translationZh' => $translationZh,
            'translationBodyId' => $this->plainTextForAdmin($translationId?->body),
            'translationBodyEn' => $this->plainTextForAdmin($translationEn?->body),
            'translationBodyZh' => $this->plainTextForAdmin($translationZh?->body),
            'mediaOptions' => $this->mediaOptions(),
            'apiUrl' => '/api/v1/admin/content-blocks/about_company',
            'redirectUrl' => route('admin.content.index', ['resource' => 'content-blocks', 'section' => 'about_company']),
            'mediaUrl' => $this->mediaUrl($record->media),
        ]);
    }

    private function plainTextForAdmin(?string $content): string
    {
        $contentWithParagraphBreaks = preg_replace('/<\s*\/p\s*>\s*<\s*p(?:\s[^>]*)?>/iu', "\n\n", $content ?? '') ?? ($content ?? '');
        $contentWithLineBreaks = preg_replace('/<\s*br\s*\/?\s*>/iu', "\n", $contentWithParagraphBreaks) ?? $contentWithParagraphBreaks;
        $plainText = strip_tags($contentWithLineBreaks);

        return trim(html_entity_decode($plainText, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function applyDraftPayload(Model $record, string $resource): void
    {
        $draftPayload = $record->getAttribute('draft_payload');
        if (! is_array($draftPayload)) {
            return;
        }

        $record->forceFill($draftPayload['attributes'] ?? []);
        $translationClass = match ($resource) {
            'hero-slides' => HeroSlideTranslation::class,
            'content-blocks' => ContentBlockTranslation::class,
            default => null,
        };
        if ($translationClass !== null) {
            $translations = $record->translations->keyBy('lang');
            foreach ($draftPayload['translations'] ?? [] as $language => $attributes) {
                $translation = $translations->get($language) ?? new $translationClass(['lang' => $language]);
                $translation->forceFill($attributes);
                $translations->put($language, $translation);
            }
            $record->setRelation('translations', $translations->values());
        }

        if (in_array($resource, ['hero-slides', 'content-blocks'], true)) {
            $record->unsetRelation('media')->load('media');
        }
    }

    /** @return array{common: list<array<string, mixed>>, id: list<array<string, mixed>>, en: list<array<string, mixed>>} */
    private function definitions(string $resource): array
    {
        $media = static fn (string $name, string $label, bool $required = false): array => ['name' => $name, 'label' => $label, 'type' => 'media', 'required' => $required];
        $text = static fn (string $name, string $label, bool $required = false, int $maxLength = 255): array => ['name' => $name, 'label' => $label, 'type' => 'text', 'required' => $required, 'maxlength' => $maxLength];
        $textarea = static fn (string $name, string $label, bool $required = false, int $maxLength = 3000): array => ['name' => $name, 'label' => $label, 'type' => 'textarea', 'required' => $required, 'maxlength' => $maxLength];
        $select = static fn (string $name, string $label, array $options, bool $required = true): array => ['name' => $name, 'label' => $label, 'type' => 'select', 'required' => $required, 'options' => $options];

        $empty = ['common' => [], 'id' => [], 'en' => [], 'zh' => []];

        $definitions = match ($resource) {
            'hero-slides' => ['common' => [$media('media_id', 'Gambar Banner', true), $text('cta_url', 'Link CTA')], 'id' => [$text('translations[id][title]', 'Judul', true, 120), $text('translations[id][subtitle]', 'Subjudul', false, 250), $text('translations[id][cta_label]', 'Label CTA', false, 40)], 'en' => [$text('translations[en][title]', 'Judul', false, 120), $text('translations[en][subtitle]', 'Subjudul', false, 250), $text('translations[en][cta_label]', 'Label CTA', false, 40)]],
            'content-blocks' => ['common' => [$select('block_key', 'Bagian', ['home_coverage' => 'Jangkauan Kami', 'home_cta' => 'CTA Hubungi Kami', 'about_company' => 'Tentang Perusahaan', 'about_vision' => 'Visi', 'footer_about' => 'Tentang Footer', 'career_intro' => 'Pengantar Karir'], true), $media('media_id', 'Gambar'), $text('cta_url', 'Link CTA')], 'id' => [$text('translations[id][title]', 'Judul', true, 200), $text('translations[id][subtitle]', 'Subjudul', false, 300), $textarea('translations[id][body]', 'Isi', true, 10000), $text('translations[id][cta_label]', 'Label CTA', false, 60)], 'en' => [$text('translations[en][title]', 'Judul', false, 200), $text('translations[en][subtitle]', 'Subjudul', false, 300), $textarea('translations[en][body]', 'Isi', false, 10000), $text('translations[en][cta_label]', 'Label CTA', false, 60)]],
            'brands' => ['common' => [$text('name', 'Nama Brand', true, 100), $media('logo_media_id', 'Logo', true), $text('website_url', 'Link Website'), $text('display_order', 'Urutan')], 'id' => [], 'en' => []],
            'mission-items' => ['common' => [$text('display_order', 'Urutan')], 'id' => [$textarea('translations[id][text]', 'Isi Misi', true, 300)], 'en' => [$textarea('translations[en][text]', 'Isi Misi', false, 300)]],
            'company-summary' => ['common' => [$text('established_year', 'Tahun Berdiri', true, 4), $text('partner_count', 'Jumlah Mitra', true), $text('province_count', 'Jumlah Provinsi', true)], 'id' => [], 'en' => []],
            'certificates' => ['common' => [$media('media_id', 'Gambar', true), $text('issued_year', 'Tahun Terbit'), $text('display_order', 'Urutan')], 'id' => [$text('translations[id][title]', 'Judul', true, 150), $textarea('translations[id][description]', 'Keterangan', false, 300)], 'en' => [$text('translations[en][title]', 'Judul', false, 150), $textarea('translations[en][description]', 'Keterangan', false, 300)]],
            'product-categories' => ['common' => [$text('display_order', 'Urutan')], 'id' => [$text('translations[id][name]', 'Nama Kategori', true, 80), $text('translations[id][slug]', 'Slug', true, 100)], 'en' => [$text('translations[en][name]', 'Nama Kategori', false, 80), $text('translations[en][slug]', 'Slug', false, 100)], 'zh' => [$text('translations[zh][name]', 'Nama Kategori', false, 80), $text('translations[zh][slug]', 'Slug', false, 100)]],
            'products' => ['common' => [$select('category_id', 'Kategori Utama', [], true), $text('subcategory', 'Subkategori', false, 100), $select('new_status', 'Status Produk Baru', ['new' => 'New', 'not_new' => 'Tidak New'], true), ['name' => 'new_until', 'label' => 'Tampilkan NEW sampai tanggal', 'type' => 'date', 'required' => false], $media('media_id', 'Gambar Utama'), $media('logo_top_media_id', 'Logo Atas'), $media('logo_bottom_media_id', 'Logo Watermark'), $text('buy_url', 'Link Beli'), ['name' => 'brochure', 'label' => 'Brosur PDF (lokal)', 'type' => 'file', 'required' => false], ['name' => 'parameters', 'label' => 'Parameter (pisahkan dengan koma)', 'type' => 'text', 'required' => false], $text('display_order', 'Urutan')], 'id' => [$text('translations[id][name]', 'Nama Produk', true, 150), $text('translations[id][slug]', 'Slug', true, 150), $textarea('translations[id][description]', 'Deskripsi Produk', true, 20000)], 'en' => [$text('translations[en][name]', 'Nama Produk', false, 150), $text('translations[en][slug]', 'Slug', false, 150), $textarea('translations[en][description]', 'Deskripsi Produk', false, 20000)]],
            'documentations' => ['common' => [$select('type', 'Tipe', ['article' => 'Artikel', 'activity' => 'Foto Kegiatan']), $media('cover_media_id', 'Gambar Sampul'), $text('event_date', 'Tanggal', true)], 'id' => [$text('translations[id][title]', 'Judul', true, 150), $text('translations[id][slug]', 'Slug', true, 150), $textarea('translations[id][excerpt]', 'Ringkasan', false, 250), $textarea('translations[id][body]', 'Isi Artikel', false, 20000)], 'en' => [$text('translations[en][title]', 'Judul', false, 150), $text('translations[en][slug]', 'Slug', false, 150), $textarea('translations[en][excerpt]', 'Ringkasan', false, 250), $textarea('translations[en][body]', 'Isi Artikel', false, 20000)]],
            'job-openings' => ['common' => [$text('employment_type', 'Tipe Kepegawaian', true, 50), $text('location', 'Lokasi', true, 150), $text('posted_at', 'Tanggal Posting', true), $text('closing_date', 'Tanggal Penutupan'), $text('apply_url', 'Link HRIS Lamaran', true, 500)], 'id' => [$textarea('translations[id][title]', 'Posisi', true, 150), $textarea('translations[id][description]', 'Deskripsi', true, 20000), $textarea('translations[id][requirements]', 'Persyaratan', true, 20000)], 'en' => [$textarea('translations[en][title]', 'Posisi', false, 150), $textarea('translations[en][description]', 'Deskripsi', false, 20000), $textarea('translations[en][requirements]', 'Persyaratan', false, 20000)]],
            'contact-info' => ['common' => [$text('phone', 'Nomor Telepon', true, 20), $text('email', 'Email', true, 150), $text('maps_url', 'Link Google Maps'), $text('latitude', 'Latitude'), $text('longitude', 'Longitude')], 'id' => [$textarea('translations[id][address]', 'Alamat', true, 300), $text('translations[id][office_hours]', 'Jam Operasional', false, 150)], 'en' => [$textarea('translations[en][address]', 'Alamat', false, 300), $text('translations[en][office_hours]', 'Jam Operasional', false, 150)]],
            'social-links' => ['common' => [$select('platform', 'Platform', ['Instagram' => 'Instagram', 'LinkedIn' => 'LinkedIn', 'Facebook' => 'Facebook', 'YouTube' => 'YouTube', 'TikTok' => 'TikTok']), $text('url', 'URL', true, 500), $media('preview_media_id', 'Gambar pratinjau hover'), $text('display_order', 'Urutan')], 'id' => [], 'en' => []],
            'seo-metas' => ['common' => [$select('page_key', 'Halaman', ['home' => 'Beranda', 'about' => 'Tentang', 'products' => 'Produk', 'documentation' => 'Dokumentasi', 'careers' => 'Karir', 'contact' => 'Kontak']), $select('lang', 'Bahasa', ['id' => 'Bahasa Indonesia', 'en' => 'Bahasa Inggris', 'zh' => 'Mandarin']), $text('meta_title', 'Judul Meta', false, 70), $textarea('meta_description', 'Deskripsi Meta', false, 160), $text('canonical_url', 'URL Kanonis'), $media('og_media_id', 'Gambar Open Graph'), $select('robots_index', 'Izinkan Diindeks', ['1' => 'Ya', '0' => 'Tidak'])], 'id' => [], 'en' => []],
            default => $empty,
        };

        $definitions['zh'] = array_map(static function (array $field): array {
            $field['name'] = str_replace('translations[id]', 'translations[zh]', $field['name']);
            $field['required'] = false;

            return $field;
        }, $definitions['id']);

        return $definitions;
    }

    /** @return Collection<int, Media> */
    private function mediaOptions(bool $includeVideos = false): Collection
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if ($includeVideos) {
            $allowedTypes = [...$allowedTypes, 'video/mp4', 'video/webm'];
        }
        $mediaOptions = Media::query()->whereIn('mime_type', $allowedTypes)->orderByDesc('created_at')->get();
        $mediaOptions->each(function (Media $media): void {
            $media->setAttribute('admin_preview_url', $this->mediaUrl($media));
        });

        return $mediaOptions;
    }

    private function mediaUrl(?Media $media): ?string
    {
        if ($media === null) {
            return null;
        }

        return route('media.stream', ['media' => $media->id]);
    }

    private function modelClass(string $resource): string
    {
        abort_unless(isset(self::MODELS[$resource]), 404);

        return self::MODELS[$resource];
    }

    private function record(string $resource, string $id): Model
    {
        $modelClass = $this->modelClass($resource);
        $query = $modelClass::query();

        if ($resource === 'content-blocks') {
            return $query->where('block_key', $id)->firstOrFail();
        }
        if ($resource === 'seo-metas') {
            return $query->where('page_key', $id)->where('lang', request('lang', 'id'))->firstOrFail();
        }

        return $query->findOrFail($id);
    }
}
