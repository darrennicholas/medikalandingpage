<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Certificate;
use App\Models\CertificateTranslation;
use App\Models\CompanySummary;
use App\Models\ContactInfo;
use App\Models\ContactInfoTranslation;
use App\Models\ContentBlock;
use App\Models\ContentBlockTranslation;
use App\Models\Documentation;
use App\Models\DocumentationTranslation;
use App\Models\HeroSlide;
use App\Models\HeroSlideTranslation;
use App\Models\JobOpening;
use App\Models\JobOpeningTranslation;
use App\Models\Media;
use App\Models\MissionItem;
use App\Models\MissionItemTranslation;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCategoryTranslation;
use App\Models\ProductTranslation;
use App\Models\SeoMeta;
use App\Models\SocialLink;
use App\Services\ActivityLogger;
use App\Services\ContentVersion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminResourceController extends Controller
{
    /** @var array<string, class-string<Model>> */
    private const MODELS = [
        'hero-slides' => HeroSlide::class,
        'content-blocks' => ContentBlock::class,
        'brands' => Brand::class,
        'mission-items' => MissionItem::class,
        'company-summary' => CompanySummary::class,
        'certificates' => Certificate::class,
        'product-categories' => ProductCategory::class,
        'products' => Product::class,
        'documentations' => Documentation::class,
        'job-openings' => JobOpening::class,
        'contact-info' => ContactInfo::class,
        'social-links' => SocialLink::class,
        'seo-metas' => SeoMeta::class,
    ];

    /** @var array<string, array<string, mixed>> */
    private const RULES = [
        'hero-slides' => ['media_id' => 'nullable|integer|exists:media,id', 'cta_url' => ['nullable', 'string', 'max:500', 'regex:/^(https:\/\/|\/(?!\/))/i'], 'text_alignment' => 'sometimes|in:left,center,justify,right', 'display_order' => 'nullable|integer|min:1'],
        'content-blocks' => ['block_key' => 'required|string|max:50', 'media_id' => 'nullable|integer|exists:media,id', 'cta_url' => ['nullable', 'string', 'max:500', 'regex:/^(https:\/\/|\/(?!\/))/i']],
        'brands' => ['name' => 'required|string|max:100', 'logo_media_id' => 'nullable|integer|exists:media,id', 'website_url' => 'nullable|url:https|max:500', 'display_order' => 'nullable|integer|min:1'],
        'mission-items' => ['display_order' => 'nullable|integer|min:1'],
        'company-summary' => ['established_year' => 'required|integer|digits:4|min:1900', 'partner_count' => 'required|integer|min:0', 'province_count' => 'required|integer|min:0|max:38'],
        'certificates' => ['media_id' => 'nullable|integer|exists:media,id', 'issued_year' => 'nullable|integer|digits:4', 'display_order' => 'nullable|integer|min:1'],
        'product-categories' => ['display_order' => 'nullable|integer|min:1'],
        'products' => ['category_id' => 'required|integer|exists:product_categories,id', 'media_id' => 'nullable|integer|exists:media,id', 'display_order' => 'nullable|integer|min:1', 'buy_url' => 'nullable|url:https|max:500', 'parameters' => 'nullable|array', 'subcategory' => 'nullable|string|max:100', 'new_until' => 'nullable|date_format:Y-m-d', 'logo_top_media_id' => 'nullable|integer|exists:media,id', 'logo_bottom_media_id' => 'nullable|integer|exists:media,id'],
        'documentations' => ['type' => 'required|in:article,activity', 'cover_media_id' => 'nullable|integer|exists:media,id', 'event_date' => 'required|date', 'gallery' => 'nullable|array|max:30', 'gallery.*.media_id' => 'required|integer|distinct|exists:media,id', 'gallery.*.caption_id' => 'nullable|string|max:200', 'gallery.*.caption_en' => 'nullable|string|max:200', 'gallery.*.caption_zh' => 'nullable|string|max:200'],
        'job-openings' => ['employment_type' => 'required|string|max:50', 'location' => 'required|string|max:150', 'posted_at' => 'required|date', 'closing_date' => 'nullable|date|after_or_equal:posted_at', 'apply_url' => 'required|url:https|max:500'],
        'contact-info' => ['phone' => ['required', 'regex:/^[0-9+()\- .]{6,20}$/'], 'email' => 'required|email|max:150', 'maps_url' => 'nullable|url:https|max:500', 'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180'],
        'social-links' => [
            'platform' => 'required|string|max:30',
            'url' => 'required|url:https|max:500',
            'preview_media_id' => 'nullable|integer',
            'display_order' => 'nullable|integer|min:1',
        ],
        'seo-metas' => ['page_key' => 'required|in:home,about,products,documentation,careers,contact', 'lang' => 'required|in:id,en,zh', 'meta_title' => 'nullable|string|max:70', 'meta_description' => 'nullable|string|max:160', 'canonical_url' => 'nullable|url:https|max:500', 'og_media_id' => 'nullable|integer|exists:media,id', 'robots_index' => 'required|boolean'],
    ];

    /** @var array<string, class-string<Model>> */
    private const TRANSLATIONS = [
        'hero-slides' => HeroSlideTranslation::class,
        'content-blocks' => ContentBlockTranslation::class,
        'mission-items' => MissionItemTranslation::class,
        'certificates' => CertificateTranslation::class,
        'product-categories' => ProductCategoryTranslation::class,
        'products' => ProductTranslation::class,
        'documentations' => DocumentationTranslation::class,
        'job-openings' => JobOpeningTranslation::class,
        'contact-info' => ContactInfoTranslation::class,
    ];

    public function index(Request $request, string $resource): JsonResponse
    {
        $modelClass = $this->modelClass($resource);
        $query = $modelClass::query();

        if ($resource === 'contact-info') {
            $contacts = $query->with('translations')->orderBy('id')->get();

            return response()->json([
                'success' => true,
                'data' => $contacts->first(),
                'contacts' => $contacts,
                'message' => 'Data berhasil dimuat.',
            ]);
        }

        if (in_array($resource, ['company-summary', 'contact-info'], true)) {
            $record = $query->first();
            if ($record !== null && method_exists($record, 'translations')) {
                $record->load('translations');
            }

            return response()->json(['success' => true, 'data' => $record, 'message' => 'Data berhasil dimuat.']);
        }

        if (method_exists($modelClass, 'translations')) {
            $query->with('translations');
        }

        if ($request->filled('status') && in_array('status', (new $modelClass)->getFillable(), true)) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $search = '%'.$request->string('q')->toString().'%';
            $searchColumns = [
                'hero-slides' => ['title'], 'content-blocks' => ['title'], 'mission-items' => ['text'],
                'certificates' => ['title'], 'product-categories' => ['name'], 'products' => ['name'],
                'documentations' => ['title'], 'job-openings' => ['title'], 'contact-info' => ['address'],
            ][$resource] ?? ['name'];
            if (method_exists($modelClass, 'translations') || in_array('name', (new $modelClass)->getFillable(), true)) {
                $query->where(function (Builder $nested) use ($search, $modelClass, $searchColumns): void {
                    if (method_exists($modelClass, 'translations')) {
                        $nested->whereHas('translations', function (Builder $translations) use ($search, $searchColumns): void {
                            $translations->where(function (Builder $fields) use ($search, $searchColumns): void {
                                foreach ($searchColumns as $column) {
                                    $fields->orWhere($column, 'like', $search);
                                }
                            });
                        });
                    } elseif (in_array('name', (new $modelClass)->getFillable(), true)) {
                        $nested->where('name', 'like', $search);
                    }
                });
            }
        }

        $perPage = (int) $request->input('per_page', 25);
        abort_unless(in_array($perPage, [10, 25, 50], true), 422, 'per_page harus 10, 25, atau 50.');

        return response()->json([
            'success' => true,
            'data' => (in_array('display_order', (new $modelClass)->getFillable(), true) ? $query->orderBy('display_order') : $query->orderByDesc('id'))->paginate($perPage),
            'message' => 'Data berhasil dimuat.',
        ]);
    }

    public function showContentBlock(Request $request, string $id): JsonResponse
    {
        return $this->show($request, 'content-blocks', $id);
    }

    public function updateContentBlock(Request $request, string $id, ActivityLogger $activityLogger): JsonResponse
    {
        return $this->update($request, 'content-blocks', $id, $activityLogger);
    }

    public function show(Request $request, string $resource, string $id): JsonResponse
    {
        $record = $this->findRecord($resource, $id);

        if (method_exists($record, 'translations')) {
            $record->load('translations');
        }

        return response()->json(['success' => true, 'data' => $record, 'message' => 'Data berhasil dimuat.']);
    }

    public function store(Request $request, string $resource, ActivityLogger $activityLogger): JsonResponse
    {
        return DB::transaction(fn (): JsonResponse => $this->storeLocked($request, $resource, $activityLogger));
    }

    private function storeLocked(Request $request, string $resource, ActivityLogger $activityLogger): JsonResponse
    {
        if ($resource === 'contact-info') {
            return $this->storeContactInfos($request, $activityLogger);
        }

        $payload = $this->validatedPayload($request, $resource);
        $existing = in_array($resource, ['company-summary', 'contact-info'], true) ? $this->modelClass($resource)::query()->lockForUpdate()->first() : null;
        if ($existing !== null && ($conflict = $this->detectStaleUpdate($request, $existing)) !== null) {
            return $conflict;
        }
        $oldValues = $existing ? $this->activitySnapshot($existing) : null;
        $record = DB::transaction(function () use ($request, $resource, $payload, $existing): Model {
            $modelClass = $this->modelClass($resource);
            $record = $existing ?? new $modelClass;
            $attributes = $payload['attributes'];
            if (in_array('status', $record->getFillable(), true)) {
                $attributes['status'] = $payload['status'];
            }
            if (in_array('display_order', $record->getFillable(), true)) {
                $attributes['display_order'] ??= ((int) $modelClass::query()->max('display_order')) + 1;
            }
            if (! $record->exists && in_array('created_by', $record->getFillable(), true)) {
                $attributes['created_by'] = $request->user()->id;
            }
            if (in_array('updated_by', $record->getFillable(), true)) {
                $attributes['updated_by'] = $request->user()->id;
            }
            $record->forceFill($attributes)->save();
            $this->saveTranslations($resource, $record, $payload['translations']);
            $this->saveGallery($request, $resource, $record);
            $this->saveBrochure($request, $resource, $record);

            return $record;
        });
        $activityLogger->log($request->user(), $existing ? 'update' : ($payload['status'] === 'published' ? 'publish' : 'create'), $record::class, $record->getKey(), $oldValues, $this->activitySnapshot($record->fresh()));

        return response()->json(['success' => true, 'data' => $this->loadTranslations($record), 'message' => 'Data berhasil disimpan.'], 201);
    }

    private function storeContactInfos(Request $request, ActivityLogger $activityLogger): JsonResponse
    {
        $contacts = $request->input('contacts');
        $isLegacyPayload = ! is_array($contacts) && ! $request->exists('contacts_present');
        if (! is_array($contacts)) {
            $legacyContact = $request->exists('phone') || $request->exists('email');
            $existingContactId = $legacyContact
                ? ContactInfo::query()->orderBy('id')->value('id')
                : null;
            $contacts = $legacyContact
                ? [[
                    'id' => $request->input('id', $existingContactId),
                    'phone' => $request->input('phone'),
                    'email' => $request->input('email'),
                    'maps_url' => $request->input('maps_url'),
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                    'translations' => $request->input('translations', []),
                ]]
                : [];
        }
        $request->merge(['contacts' => $contacts]);

        $validated = $request->validate([
            'contacts' => ['present', 'array', 'max:12'],
            'contacts.*.id' => ['nullable', 'integer', 'distinct', 'exists:contact_info,id'],
            'contacts.*.expected_version' => ['nullable', 'string', 'size:64'],
            'contacts.*.label' => ['nullable', 'string', 'max:120'],
            'contacts.*.phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\- .]{6,30}$/'],
            'contacts.*.email' => ['required', 'email', 'max:150'],
            'contacts.*.maps_url' => ['nullable', 'url:https', 'max:500'],
            'contacts.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'contacts.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contacts.*.translations' => ['nullable', 'array'],
            'contacts.*.translations.id' => ['nullable', 'array'],
            'contacts.*.translations.en' => ['nullable', 'array'],
            'contacts.*.translations.zh' => ['nullable', 'array'],
            'contacts.*.translations.id.address' => ['required', 'string', 'max:300'],
            'contacts.*.translations.en.address' => ['nullable', 'string', 'max:300'],
            'contacts.*.translations.zh.address' => ['nullable', 'string', 'max:300'],
            'contacts.*.translations.id.office_hours' => ['nullable', 'string', 'max:150'],
            'contacts.*.translations.en.office_hours' => ['nullable', 'string', 'max:150'],
            'contacts.*.translations.zh.office_hours' => ['nullable', 'string', 'max:150'],
        ]);

        $storedContacts = ContactInfo::query()->with('translations')->lockForUpdate()->get()->keyBy('id');
        $submittedIds = collect($validated['contacts'])->pluck('id')->filter()->map(fn (mixed $id): int => (int) $id);

        foreach ($validated['contacts'] as $contact) {
            $record = filled($contact['id'] ?? null) ? $storedContacts->get((int) $contact['id']) : null;
            $expectedVersion = trim((string) ($contact['expected_version'] ?? ''));

            if ($record?->exists && $expectedVersion !== '' && ! hash_equals(app(ContentVersion::class)->token($record), $expectedVersion)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kontak telah diubah pengguna lain. Muat ulang halaman lalu terapkan perubahan kembali.',
                ], 409);
            }
        }

        if (! $isLegacyPayload) {
            foreach ($storedContacts as $storedContact) {
                if ($submittedIds->contains($storedContact->id)) {
                    continue;
                }

                $oldValues = $this->activitySnapshot($storedContact);
                $storedContact->delete();
                $activityLogger->log($request->user(), 'delete', ContactInfo::class, $storedContact->id, $oldValues);
            }
        }

        foreach ($validated['contacts'] as $contact) {
            $record = filled($contact['id'] ?? null) ? $storedContacts->get((int) $contact['id']) : new ContactInfo;
            $oldValues = $record->exists ? $this->activitySnapshot($record) : null;

            $record->forceFill([
                'label' => $contact['label'] ?? null,
                'phone' => $contact['phone'] ?? null,
                'email' => $contact['email'] ?? null,
                'maps_url' => $contact['maps_url'] ?? null,
                'latitude' => $contact['latitude'] ?? null,
                'longitude' => $contact['longitude'] ?? null,
                'updated_by' => $request->user()->id,
            ])->save();

            foreach (['id', 'en', 'zh'] as $language) {
                $translation = $contact['translations'][$language] ?? [];
                $hasTranslation = $record->translations()->where('lang', $language)->exists();
                if (! $hasTranslation && collect($translation)->every(fn (mixed $value): bool => blank($value))) {
                    continue;
                }

                $record->translations()->updateOrCreate(
                    ['lang' => $language],
                    [
                        'address' => $translation['address'] ?? '',
                        'office_hours' => $translation['office_hours'] ?? null,
                    ],
                );
            }

            $activityLogger->log(
                $request->user(),
                $oldValues === null ? 'create' : 'update',
                ContactInfo::class,
                $record->id,
                $oldValues,
                $this->activitySnapshot($record->fresh()),
            );
        }

        $savedContacts = ContactInfo::query()->with('translations')->orderBy('id')->get();

        return response()->json([
            'success' => true,
            'data' => $savedContacts->first(),
            'contacts' => $savedContacts,
            'message' => 'Informasi kontak berhasil disimpan.',
        ]);
    }

    public function update(Request $request, string $resource, string $id, ActivityLogger $activityLogger): JsonResponse
    {
        return DB::transaction(fn (): JsonResponse => $this->updateLocked($request, $resource, $id, $activityLogger));
    }

    private function updateLocked(Request $request, string $resource, string $id, ActivityLogger $activityLogger): JsonResponse
    {
        $record = $this->findOrNewRecord($resource, $id, true);
        if (($conflict = $this->detectStaleUpdate($request, $record)) !== null) {
            return $conflict;
        }
        $oldValues = $this->activitySnapshot($record);
        $payload = $this->validatedPayload($request, $resource, $record);

        if ($record->exists
            && in_array($resource, ['hero-slides', 'content-blocks'], true)
            && $payload['status'] === 'draft'
            && ($record->getAttribute('status') === 'published' || is_array($record->getAttribute('draft_payload')))) {
            $draftPayload = $this->draftPayload($resource, $record, $payload);
            $record->forceFill([
                'draft_payload' => $draftPayload,
                'updated_by' => $request->user()->id,
            ])->save();
            $activityLogger->log($request->user(), 'update', $record::class, $record->getKey(), $oldValues, $this->activitySnapshot($record->fresh()));

            return response()->json([
                'success' => true,
                'data' => $this->loadTranslations($record->fresh()),
                'message' => 'Draft tersimpan. Versi yang sedang tayang tetap aktif sampai perubahan dipublikasikan.',
            ]);
        }

        DB::transaction(function () use ($request, $resource, $record, $payload): void {
            $previousCategoryId = $record instanceof Product ? $record->category_id : null;
            $attributes = $payload['attributes'];
            if (in_array('status', $record->getFillable(), true)) {
                $attributes['status'] = $payload['status'];
            }
            if (in_array($resource, ['hero-slides', 'content-blocks'], true) && $payload['status'] !== 'draft') {
                $attributes['draft_payload'] = null;
            }
            if (! $record->exists && in_array('created_by', $record->getFillable(), true)) {
                $attributes['created_by'] = $request->user()->id;
            }
            if (in_array('updated_by', $record->getFillable(), true)) {
                $attributes['updated_by'] = $request->user()->id;
            }
            $record->forceFill($attributes)->save();
            if ($record instanceof Product && ($record->status !== 'published' || (string) $previousCategoryId !== (string) $record->category_id)) {
                $record->featuredCategories()->detach();
            }
            $this->saveTranslations($resource, $record, $payload['translations']);
            $this->saveGallery($request, $resource, $record);
            $this->saveBrochure($request, $resource, $record);
        });

        $oldStatus = $oldValues['status'] ?? null;
        $action = $oldStatus !== $payload['status']
            ? ($payload['status'] === 'published' ? 'publish' : ($record->wasRecentlyCreated ? 'create' : 'update'))
            : ($record->wasRecentlyCreated ? 'create' : 'update');
        $activityLogger->log($request->user(), $action, $record::class, $record->getKey(), $oldValues, $this->activitySnapshot($record->fresh()));

        return response()->json(['success' => true, 'data' => $this->loadTranslations($record->fresh()), 'message' => 'Data berhasil diperbarui.']);
    }

    /** @param array{attributes: array<string, mixed>, translations: array<string, array<string, mixed>>, status: string} $payload
     * @return array{attributes: array<string, mixed>, translations: array<string, array<string, mixed>>}
     */
    private function draftPayload(string $resource, Model $record, array $payload): array
    {
        $attributeKeys = array_keys(self::RULES[$resource] ?? []);
        $attributes = array_merge($record->only($attributeKeys), $payload['attributes']);
        $translationClass = self::TRANSLATIONS[$resource];
        $translationFields = (new $translationClass)->getFillable();
        $translations = [];

        foreach (config('cms.content.supported_languages') as $language) {
            $storedTranslation = $record->translations()->where('lang', $language)->first();
            $currentValues = $storedTranslation?->only($translationFields) ?? ['lang' => $language];
            $translations[$language] = array_merge($currentValues, $payload['translations'][$language] ?? [], ['lang' => $language]);
        }

        return ['attributes' => $attributes, 'translations' => $translations];
    }

    public function destroy(Request $request, string $resource, string $id, ActivityLogger $activityLogger): JsonResponse
    {
        $record = $this->findRecord($resource, $id);

        if ($record instanceof MissionItem && MissionItem::query()->count() <= 1) {
            return response()->json(['success' => false, 'message' => 'Minimal satu poin misi harus tersedia.'], 422);
        }

        $oldValues = $this->activitySnapshot($record);

        DB::transaction(function () use ($record, $request, $activityLogger, $oldValues): void {
            if ($record instanceof ProductCategory) {
                foreach ($record->products()->lockForUpdate()->get() as $product) {
                    $productValues = $this->activitySnapshot($product);
                    $product->featuredCategories()->detach();
                    $product->delete();
                    $activityLogger->log($request->user(), 'delete', Product::class, $product->id, $productValues);
                }
                $record->featuredProducts()->detach();
                DB::table('product_subcategories')->where('category_id', $record->id)->delete();
            }
            if ($record instanceof Product) {
                $record->featuredCategories()->detach();
            }
            $record->delete();
            $activityLogger->log($request->user(), 'delete', $record::class, $record->getKey(), $oldValues);
        });

        return response()->json(['success' => true, 'data' => null, 'message' => 'Data berhasil dihapus.']);
    }

    public function updateStatus(Request $request, string $resource, string $id, ActivityLogger $activityLogger): JsonResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::in($resource === 'job-openings' ? ['draft', 'open', 'closed'] : ['draft', 'published'])]]);
        $record = $this->findRecord($resource, $id);
        $oldValues = ['status' => $record->getAttribute('status')];

        if (in_array($validated['status'], ['published', 'open'], true)) {
            $this->assertPublishable($resource, $record);
        }

        DB::transaction(function () use ($record, $validated, $request): void {
            $record->forceFill(['status' => $validated['status'], 'updated_by' => $request->user()->id])->save();
            if ($record instanceof Product && $validated['status'] !== 'published') {
                $record->featuredCategories()->detach();
            }
        });

        $activityLogger->log($request->user(), $validated['status'] === 'published' ? 'publish' : 'update', $record::class, $record->getKey(), $oldValues, ['status' => $validated['status']]);

        return response()->json(['success' => true, 'data' => $record, 'message' => 'Status berhasil diperbarui.']);
    }

    public function reorder(Request $request, string $resource, ActivityLogger $activityLogger): JsonResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['required', 'integer', 'distinct']]);
        $modelClass = $this->modelClass($resource);

        $matchingRecords = $modelClass::query()->whereIn('id', $validated['ids'])->count();
        $totalRecords = $modelClass::query()->count();
        if ($matchingRecords !== count($validated['ids']) || $totalRecords !== count($validated['ids'])) {
            throw ValidationException::withMessages(['ids' => 'Kirim seluruh ID aktif dalam jenis konten yang sama untuk mengubah urutan.']);
        }

        $previousIds = DB::transaction(function () use ($validated, $modelClass, $request): array {
            $previousIds = $modelClass::query()->orderBy('display_order')->orderBy('id')->lockForUpdate()->pluck('id')->all();
            foreach ($validated['ids'] as $index => $id) {
                $record = $modelClass::query()->findOrFail($id);
                $record->forceFill(['display_order' => $index + 1, 'updated_by' => $request->user()->id])->save();
            }

            return $previousIds;
        });

        $activityLogger->log($request->user(), 'reorder', $modelClass, null, ['ids' => $previousIds], ['ids' => $validated['ids']]);

        return response()->json(['success' => true, 'data' => null, 'message' => 'Urutan berhasil disimpan.']);
    }

    public function featured(Request $request, string $id, ActivityLogger $activityLogger): JsonResponse
    {
        $category = ProductCategory::query()->with('translations')->findOrFail($id);

        if ($request->isMethod('get')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'selected' => $category->featuredProducts()->where('products.status', 'published')->whereColumn('products.category_id', 'category_featured_products.category_id')->with('translations')->get(),
                    'available' => $category->products()->where('status', 'published')->with('translations')->orderBy('display_order')->get(),
                ],
                'message' => 'Produk unggulan berhasil dimuat.',
            ]);
        }

        $validated = $request->validate(['product_ids' => ['present', 'array'], 'product_ids.*' => ['required', 'integer', 'distinct', 'exists:products,id']]);
        if (count($validated['product_ids']) > 6) {
            return response()->json(['success' => false, 'message' => 'Maksimal 6 produk unggulan per kategori.'], 409);
        }
        $products = Product::query()->whereIn('id', $validated['product_ids'])->where('category_id', $category->id)->where('status', 'published')->get();

        if ($products->count() !== count($validated['product_ids'])) {
            throw ValidationException::withMessages(['product_ids' => 'Produk unggulan harus Published dan berasal dari kategori yang sama.']);
        }

        $previousIds = DB::transaction(function () use ($category, $validated): array {
            ProductCategory::query()->whereKey($category->id)->lockForUpdate()->firstOrFail();
            $previousIds = $category->featuredProducts()->pluck('products.id')->all();
            $category->featuredProducts()->detach();
            $category->featuredProducts()->attach(collect($validated['product_ids'])->values()->mapWithKeys(
                fn (int $productId, int $index): array => [$productId => ['display_order' => $index + 1]],
            )->all());

            return $previousIds;
        });
        $activityLogger->log($request->user(), 'reorder', ProductCategory::class, $category->id, ['featured_product_ids' => $previousIds], ['featured_product_ids' => $validated['product_ids']]);

        return response()->json(['success' => true, 'data' => $category->fresh()->featuredProducts()->with('translations')->get(), 'message' => 'Produk unggulan berhasil disimpan.']);
    }

    public function preview(string $resource, string $id): JsonResponse
    {
        $record = $this->findRecord($resource, $id);
        abort_unless($record->getAttribute('status') === 'draft', 404);
        $record->loadMissing('translations');

        return response()->json(['success' => true, 'data' => $record, 'message' => 'Pratinjau konten.'])->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function modelClass(string $resource): string
    {
        abort_unless(isset(self::MODELS[$resource]), 404);

        return self::MODELS[$resource];
    }

    /** @return array<string, mixed> */
    private function activitySnapshot(Model $record): array
    {
        if ($record->exists && method_exists($record, 'translations')) {
            $record->load('translations');
        }
        if ($record->exists && $record instanceof Documentation) {
            $record->load('gallery');
        }

        return $record->toArray();
    }

    private function findRecord(string $resource, string $id, bool $lock = false): Model
    {
        $modelClass = $this->modelClass($resource);
        $query = $modelClass::query();

        if ($resource === 'content-blocks') {
            ctype_digit($id) ? $query->whereKey((int) $id) : $query->where('block_key', $id);
        } elseif ($resource === 'seo-metas') {
            $query->where('page_key', $id)->where('lang', request()->query('lang', 'id'));
        } elseif ($resource === 'company-summary' || $resource === 'contact-info') {
            $query->whereKey($id === 'current' ? $query->max('id') : $id);
        } else {
            $query->whereKey($id);
        }

        $record = ($lock ? $query->lockForUpdate() : $query)->firstOrFail();
        abort_if($resource === 'job-openings' && $record->source === 'oneis', 403, 'Lowongan dari sistem eksternal hanya dapat dikelola melalui integrasi fase 2.');

        return $record;
    }

    /**
     * Reject a save when another admin updated the record after this form was loaded.
     * FSD 5.2.2: konflik edit bersamaan harus ditolak dengan pesan yang jelas.
     */
    private function detectStaleUpdate(Request $request, Model $record): ?JsonResponse
    {
        if (! $record->exists) {
            return null;
        }

        $expectedVersion = trim((string) $request->input('expected_version', ''));
        if ($expectedVersion !== '') {
            if (hash_equals(app(ContentVersion::class)->token($record), $expectedVersion)) {
                return null;
            }

            return response()->json(['success' => false, 'message' => 'Data telah diubah pengguna lain. Muat ulang halaman lalu terapkan perubahan kembali.'], 409);
        }

        $expectedUpdatedAt = trim((string) $request->input('expected_updated_at', ''));

        if ($expectedUpdatedAt === '') {
            return null;
        }

        try {
            $expected = Carbon::parse($expectedUpdatedAt);
        } catch (\Throwable) {
            return null;
        }

        $storedUpdatedAt = $record->getAttribute('updated_at');

        if ($storedUpdatedAt === null || $storedUpdatedAt->timestamp === $expected->timestamp) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => 'Data telah diubah pengguna lain. Muat ulang halaman lalu terapkan perubahan kembali.',
        ], 409);
    }

    private function findOrNewRecord(string $resource, string $id, bool $lock = false): Model
    {
        if (in_array($resource, ['content-blocks', 'seo-metas'], true)) {
            $modelClass = $this->modelClass($resource);
            $query = $modelClass::query();
            if ($lock) {
                $query->lockForUpdate();
            }

            if ($resource === 'content-blocks') {
                $record = $query->where('block_key', $id)->first();

                return $record ?? new $modelClass(['block_key' => Str::slug($id, '_')]);
            }

            $language = request()->query('lang', 'id');
            $record = $query->where('page_key', $id)->where('lang', $language)->first();

            return $record ?? new $modelClass(['page_key' => $id, 'lang' => $language]);
        }

        return $this->findRecord($resource, $id, $lock);
    }

    /** @return array{attributes: array<string, mixed>, translations: array<string, array<string, mixed>>, status: string} */
    private function validatedPayload(Request $request, string $resource, ?Model $record = null): array
    {
        $baseRules = self::RULES[$resource] ?? [];
        if ($resource === 'content-blocks' && $record?->getAttribute('block_key') === 'footer_about') {
            $baseRules['footer_show_phone'] = 'sometimes|boolean';
            $baseRules['footer_show_email'] = 'sometimes|boolean';
        }
        $statusRule = $resource === 'job-openings' ? 'required|in:draft,open,closed' : 'required|in:draft,published';
        $modelClass = $this->modelClass($resource);
        $statusRequired = in_array('status', (new $modelClass)->getFillable(), true);
        $rules = [...$baseRules, 'status' => $statusRequired ? $statusRule : 'nullable|in:draft,published,open,closed', 'translations' => 'nullable|array', 'translations.id' => 'nullable|array', 'translations.en' => 'nullable|array', 'translations.zh' => 'nullable|array'];
        $requestedStatus = $request->input('status', $record?->getAttribute('status') ?? 'draft');
        if ($statusRequired && ! in_array($requestedStatus, ['published', 'open'], true)) {
            foreach ($rules as $field => $rule) {
                $rules[$field] = $this->allowPartialDraft($rule);
            }
        }

        foreach ($this->translationRules($resource, $record) as $field => $rule) {
            $rules['translations.id.'.$field] = $rule;
            foreach (['en', 'zh'] as $language) {
                $rules['translations.'.$language.'.'.$field] = is_array($rule)
                    ? array_map(fn (string $part): string => str_replace('required', 'nullable', $part), $rule)
                    : str_replace('required', 'nullable', $rule);
            }
        }

        if ($resource === 'product-categories') {
            foreach (config('cms.content.supported_languages') as $language) {
                $rules['translations.'.$language.'.name'] = ['nullable', 'string', 'max:80', Rule::unique('product_category_translations', 'name')->where('lang', $language)->ignore($record?->getKey(), 'product_category_id')];
            }
        }

        if ($resource === 'social-links') {
            $rules['preview_media_id'] = [
                'nullable',
                'integer',
                Rule::exists('media', 'id')->whereNull('deleted_at')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp']),
            ];
        }

        if ($resource === 'products') {
            $rules['category_id'] = ['nullable', 'integer', Rule::exists('product_categories', 'id')->where('status', 'published')->whereNull('deleted_at')];
            $rules['brochure'] = 'nullable|file|mimetypes:application/pdf';
            $rules['new_status'] = 'sometimes|in:new,not_new';
            $rules['new_until'] = 'nullable|required_if:new_status,new|date_format:Y-m-d|after_or_equal:today';
        }

        if ($resource === 'job-openings' && $record?->source === 'oneis') {
            throw ValidationException::withMessages(['source' => 'Lowongan dari sistem eksternal tidak dapat diubah di CMS.']);
        }

        $validated = $request->validate($rules);
        if ($resource === 'products') {
            $subcategory = array_key_exists('subcategory', $validated) ? $validated['subcategory'] : $record?->subcategory;
            $categoryId = array_key_exists('category_id', $validated) ? $validated['category_id'] : $record?->category_id;
            if (filled($subcategory) && ! DB::table('product_subcategories')->where('category_id', $categoryId)->where('name', $subcategory)->exists()) {
                throw ValidationException::withMessages(['subcategory' => 'Pilih subkategori yang sesuai dengan kategori utama.']);
            }
        }
        if ($resource === 'products' && ($validated['new_status'] ?? null) === 'not_new') {
            $validated['new_until'] = null;
        }
        if ($resource === 'hero-slides' && filled($validated['media_id'] ?? null)) {
            $isSupportedBannerMedia = DB::table('media')
                ->where('id', $validated['media_id'])
                ->whereNull('deleted_at')
                ->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'video/webm'])
                ->exists();

            if (! $isSupportedBannerMedia) {
                throw ValidationException::withMessages(['media_id' => 'Pilih media banner berupa JPG, PNG, WebP, MP4, atau WebM.']);
            }
        }
        if ($resource === 'content-blocks' && $record?->exists && ($validated['block_key'] ?? $record->block_key) !== $record->block_key) {
            throw ValidationException::withMessages(['block_key' => 'Section konten tidak dapat diubah.']);
        }
        if ($resource === 'company-summary' && (int) ($validated['established_year'] ?? 0) > now()->year) {
            throw ValidationException::withMessages(['established_year' => 'Tahun berdiri tidak boleh lebih dari tahun berjalan.']);
        }
        $translations = $validated['translations'] ?? [];
        if ($resource === 'content-blocks') {
            foreach (config('cms.content.supported_languages') as $language) {
                if ($request->boolean('translations.'.$language.'.metrics_present')) {
                    $translations[$language]['metrics'] = collect($translations[$language]['metrics'] ?? [])
                        ->filter(fn (array $metric): bool => filled($metric['value'] ?? null) || filled($metric['label'] ?? null))
                        ->values()
                        ->all();
                }
                if ($request->boolean('translations.'.$language.'.buttons_present')) {
                    $translations[$language]['buttons'] = collect($translations[$language]['buttons'] ?? [])
                        ->filter(fn (array $button): bool => filled($button['label'] ?? null) || filled($button['url'] ?? null))
                        ->values()
                        ->all();
                }
            }
        }
        if (in_array($resource, ['product-categories', 'products', 'documentations'], true)) {
            foreach (config('cms.content.supported_languages') as $language) {
                if (filled($translations[$language]['name'] ?? $translations[$language]['title'] ?? null)
                    && blank($translations[$language]['slug'] ?? null)) {
                    $slugSource = $translations[$language]['name'] ?? $translations[$language]['title'];
                    $translations[$language]['slug'] = Str::slug((string) $slugSource);
                }
            }
        }
        $status = $validated['status'] ?? $record?->getAttribute('status') ?? 'published';

        if ($resource === 'products' && $status === 'published' && ! ProductCategory::query()->whereKey(array_key_exists('category_id', $validated) ? $validated['category_id'] : $record?->category_id)->where('status', 'published')->exists()) {
            throw ValidationException::withMessages(['category_id' => 'Pilih kategori yang aktif sebelum publikasi.']);
        }

        if (in_array($status, ['published', 'open'], true)) {
            $existingIdTranslation = $record?->exists && isset(self::TRANSLATIONS[$resource]) ? $record->translations()->where('lang', 'id')->first() : null;
            $idTranslation = $translations['id'] ?? [];
            $idValues = array_replace(
                $existingIdTranslation?->only(array_keys($this->translationRules($resource, $record))) ?? [],
                $translations['id'] ?? [],
            );
            $translationFields = $this->publicationTranslationFields($resource, $record, $idValues);
            $missingFields = [];

            foreach (['id'] as $language) {
                $languageTranslation = $translations[$language] ?? [];
                foreach ($translationFields as $field) {
                    $value = array_key_exists($field, $languageTranslation)
                        ? $languageTranslation[$field]
                        : $existingIdTranslation?->{$field};

                    if (blank($value)) {
                        $missingFields[] = 'translations.'.$language.'.'.$field;
                    }
                }
            }

            if ($missingFields !== []) {
                throw ValidationException::withMessages(array_fill_keys($missingFields, 'Konten Bahasa Indonesia wajib diisi sebelum publikasi.'));
            }

            if (in_array($resource, ['hero-slides', 'brands', 'certificates', 'products', 'documentations'], true)) {
                $mediaField = ['brands' => 'logo_media_id', 'documentations' => 'cover_media_id'][$resource] ?? 'media_id';

                if (blank(array_key_exists($mediaField, $validated) ? $validated[$mediaField] : $record?->getAttribute($mediaField))) {
                    throw ValidationException::withMessages([$mediaField => 'Gambar wajib dipilih untuk menerbitkan konten.']);
                }
            }

            if ($resource === 'documentations' && ($validated['type'] ?? $record?->type) === 'article'
                && blank(array_key_exists('body', $idTranslation) ? $idTranslation['body'] : $record?->translations()->where('lang', 'id')->value('body'))) {
                throw ValidationException::withMessages(['translations.id.body' => 'Isi artikel wajib diisi sebelum publikasi.']);
            }

            $commonRequiredFields = match ($resource) {
                'brands' => ['name', 'logo_media_id'],
                'products' => ['category_id', 'media_id'],
                'documentations' => ['type', 'cover_media_id', 'event_date'],
                'job-openings' => ['employment_type', 'location', 'posted_at', 'apply_url'],
                default => [],
            };

            foreach ($commonRequiredFields as $field) {
                if (blank(array_key_exists($field, $validated) ? $validated[$field] : $record?->getAttribute($field))) {
                    throw ValidationException::withMessages([$field => 'Wajib diisi sebelum konten dapat dipublikasikan.']);
                }
            }

            if ($resource === 'hero-slides') {
                $existingLabels = $record?->exists ? $record->translations()->whereIn('lang', config('cms.content.supported_languages'))->pluck('cta_label', 'lang') : collect();
                $hasCtaLabel = collect(config('cms.content.supported_languages'))->contains(function (string $language) use ($translations, $existingLabels): bool {
                    $fields = $translations[$language] ?? [];

                    return filled(array_key_exists('cta_label', $fields) ? $fields['cta_label'] : $existingLabels->get($language));
                });
                $ctaUrl = array_key_exists('cta_url', $validated) ? $validated['cta_url'] : $record?->cta_url;
                if ($hasCtaLabel && blank($ctaUrl)) {
                    throw ValidationException::withMessages(['cta_url' => 'Link tombol wajib diisi bila label CTA digunakan.']);
                }
            }

            if ($resource === 'content-blocks') {
                $blockKey = (string) ($validated['block_key'] ?? $record?->getAttribute('block_key'));
                if ($blockKey === 'home_coverage' && blank(array_key_exists('media_id', $validated) ? $validated['media_id'] : $record?->getAttribute('media_id'))) {
                    throw ValidationException::withMessages(['media_id' => 'Gambar jangkauan wajib dipilih sebelum publikasi.']);
                }
                $storedTranslation = $record?->translations()->where('lang', 'id')->first();
                if ($blockKey === 'home_cta') {
                    $ctaButtons = $translations['id']['buttons'] ?? $storedTranslation?->buttons ?? [];
                    $hasCompleteButton = collect($ctaButtons)->contains(fn (array $button): bool => filled($button['label'] ?? null) && filled($button['url'] ?? null));
                    $hasIncompleteButton = collect($ctaButtons)->contains(fn (array $button): bool => filled($button['label'] ?? null) !== filled($button['url'] ?? null));
                    $hasLegacyButton = filled(array_key_exists('cta_label', $idTranslation) ? $idTranslation['cta_label'] : $storedTranslation?->cta_label)
                        && filled(array_key_exists('cta_url', $validated) ? $validated['cta_url'] : $record?->getAttribute('cta_url'));
                    if ((! $hasCompleteButton && ! $hasLegacyButton) || $hasIncompleteButton) {
                        throw ValidationException::withMessages(['translations.id.buttons' => 'Tambahkan minimal satu tombol CTA dengan label dan URL sebelum publikasi.']);
                    }
                }

                if ($blockKey === 'home_coverage') {
                    $metrics = $translations['id']['metrics'] ?? $storedTranslation?->metrics ?? [];
                    $hasIncompleteMetric = collect($metrics)->contains(fn (array $metric): bool => filled($metric['value'] ?? null) !== filled($metric['label'] ?? null));
                    if ($hasIncompleteMetric) {
                        throw ValidationException::withMessages(['translations.id.metrics' => 'Lengkapi nilai dan label setiap metrik sebelum publikasi.']);
                    }
                }
            }
        }

        $attributeKeys = array_keys($baseRules);
        $fillable = (new $modelClass)->getFillable();
        $attributes = array_intersect_key($validated, array_flip(array_intersect($attributeKeys, $fillable)));

        if ($resource === 'content-blocks' && ! $record) {
            $attributes['block_key'] = Str::slug($attributes['block_key'], '_');
        }

        if ($resource === 'seo-metas') {
            $attributes['robots_index'] = (bool) $attributes['robots_index'];
        }

        return ['attributes' => $attributes, 'translations' => $translations, 'status' => $status];
    }

    /** @return array<string, string|list<string>> */
    private function translationRules(string $resource, ?Model $record = null): array
    {
        if ($resource === 'content-blocks' && $record?->getAttribute('block_key') === 'footer_about') {
            return ['body' => 'nullable|string|max:300', 'copyright_text' => 'nullable|string|max:150'];
        }

        return match ($resource) {
            'hero-slides' => ['title' => 'nullable|string|max:120', 'subtitle' => 'nullable|string|max:250', 'description' => 'nullable|string|max:1000', 'cta_label' => 'nullable|string|max:40'],
            'content-blocks' => [
                'title' => 'nullable|string|max:120',
                'subtitle' => 'nullable|string|max:300',
                'body' => 'nullable|string|max:'.match ($record?->getAttribute('block_key')) {
                    'home_coverage', 'about_vision' => 500, 'home_cta' => 300, 'about_company' => 3000, default => 10000
                },
                'cta_label' => 'nullable|string|max:40',
                'copyright_text' => 'nullable|string|max:150',
                'metrics' => 'nullable|array|max:6',
                'metrics.*.value' => 'nullable|string|max:40',
                'metrics.*.label' => 'nullable|string|max:60',
                'buttons' => 'nullable|array|max:4',
                'buttons.*.label' => 'nullable|string|max:40',
                'buttons.*.url' => ['nullable', 'string', 'max:500', 'regex:/^(https:\/\/|\/(?!\/))/i'],
                'buttons.*.style' => 'nullable|in:primary,secondary,tertiary',
                'buttons.*.icon' => 'nullable|string|max:30',
            ],
            'mission-items' => ['text' => 'nullable|string|max:300'],
            'certificates' => ['title' => 'nullable|string|max:150', 'description' => 'nullable|string|max:300'],
            'product-categories' => ['name' => 'nullable|string|max:80', 'slug' => 'nullable|string|max:100|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'products' => ['name' => 'nullable|string|max:150', 'slug' => 'nullable|string|max:150|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'description' => 'nullable|string|max:20000'],
            'documentations' => ['title' => 'nullable|string|max:150', 'slug' => 'nullable|string|max:150|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'excerpt' => 'nullable|string|max:250', 'body' => 'nullable|string|max:20000'],
            'job-openings' => ['title' => 'nullable|string|max:150', 'description' => 'nullable|string|max:20000', 'requirements' => 'nullable|string|max:20000'],
            'contact-info' => ['address' => 'nullable|string|max:300', 'office_hours' => 'nullable|string|max:150'],
            default => [],
        };
    }

    /** @return list<string> */
    private function requiredTranslationFields(string $resource, ?Model $record = null): array
    {
        if ($resource === 'content-blocks' && $record?->getAttribute('block_key') === 'footer_about') {
            return [];
        }

        if ($resource === 'content-blocks' && in_array($record?->getAttribute('block_key'), ['home_cta', 'home_coverage'], true)) {
            return ['title'];
        }

        return match ($resource) {
            'hero-slides' => ['title'],
            'content-blocks' => ['title', 'body'],
            'mission-items' => ['text'],
            'certificates' => ['title'],
            'product-categories' => ['name', 'slug'],
            'products' => ['name', 'slug', 'description'],
            'documentations' => ['title', 'slug'],
            'job-openings' => ['title', 'description', 'requirements'],
            'contact-info' => ['address'],
            default => [],
        };
    }

    /** @param array<string, mixed> $idValues
     * @return list<string>
     */
    private function publicationTranslationFields(string $resource, ?Model $record, array $idValues): array
    {
        $fields = $this->requiredTranslationFields($resource, $record);

        foreach (array_keys($this->translationRules($resource, $record)) as $field) {
            if (! str_contains($field, '.') && filled($idValues[$field] ?? null)) {
                $fields[] = $field;
            }
        }

        return array_values(array_unique($fields));
    }

    /** @param array<string, array<string, mixed>> $translations */
    private function saveTranslations(string $resource, Model $record, array $translations): void
    {
        if (! isset(self::TRANSLATIONS[$resource])) {
            return;
        }

        $allowedFields = array_keys($this->translationRules($resource, $record));
        $translationClass = self::TRANSLATIONS[$resource];

        foreach (config('cms.content.supported_languages') as $language) {
            if (! isset($translations[$language])) {
                continue;
            }

            $attributes = array_intersect_key($translations[$language], array_flip($allowedFields));
            $existingTranslation = $record->translations()->where('lang', $language)->exists();
            if (! $existingTranslation && collect($attributes)->every(fn (mixed $value): bool => blank($value))) {
                continue;
            }

            foreach (['body', 'description', 'requirements'] as $richTextField) {
                if (isset($attributes[$richTextField])) {
                    $attributes[$richTextField] = $this->sanitizeRichText((string) $attributes[$richTextField], $resource === 'documentations' && $richTextField === 'body');
                }
            }
            if (in_array($resource, ['product-categories', 'products', 'documentations'], true) && (array_key_exists('slug', $attributes) || array_key_exists('name', $attributes) || array_key_exists('title', $attributes))) {
                $candidate = (string) ($attributes['slug'] ?? Str::slug((string) ($attributes['name'] ?? $attributes['title'] ?? '')));
                $attributes['slug'] = filled($candidate)
                    ? $this->uniqueSlug($translationClass, $record, $language, $candidate)
                    : null;
            }

            $record->translations()->updateOrCreate(['lang' => $language], $attributes);
        }
    }

    private function sanitizeRichText(string $value, bool $allowImages = false): string
    {
        $source = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $source->loadHTML('<?xml encoding="UTF-8"><div>'.$value.'</div>', LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $output = new \DOMDocument('1.0', 'UTF-8');
        $container = $output->createElement('div');
        $output->appendChild($container);
        $copy = function (\DOMNode $node, \DOMNode $parent) use (&$copy, $output, $allowImages): void {
            if ($node instanceof \DOMText) {
                $parent->appendChild($output->createTextNode($node->nodeValue ?? ''));

                return;
            }
            if (! $node instanceof \DOMElement || in_array(strtolower($node->tagName), ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template'], true)) {
                return;
            }
            $tag = strtolower($node->tagName);
            $target = $parent;
            if ($allowImages && $tag === 'img' && preg_match('~^/media/([0-9]+)/file$~', $node->getAttribute('src'), $matches) && Media::query()->whereKey($matches[1])->where('mime_type', 'like', 'image/%')->exists()) {
                $image = $output->createElement('img');
                $image->setAttribute('src', '/media/'.$matches[1].'/file');
                $image->setAttribute('alt', mb_substr($node->getAttribute('alt'), 0, 200));
                $parent->appendChild($image);

                return;
            }
            $colorStyle = strtolower(preg_replace('/\s+/', '', $node->getAttribute('style')) ?? '');
            if ($tag === 'span' && in_array($colorStyle, ['color:#315bd5', 'color:rgb(49,91,213)', 'color:rgb(49,91,213);'], true)) {
                $target = $output->createElement('span');
                $target->setAttribute('style', 'color:#315bd5');
                $parent->appendChild($target);
            }
            if (in_array($tag, ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'a'], true)) {
                $target = $output->createElement($tag);
                $classes = array_intersect(preg_split('/\s+/', $node->getAttribute('class')) ?: [], ['ql-align-center', 'ql-align-right', 'ql-align-justify', 'ql-size-small', 'ql-size-large', 'ql-size-huge', 'ql-size-12px', 'ql-size-14px', 'ql-size-16px', 'ql-size-18px', 'ql-size-20px', 'ql-size-24px', 'ql-size-28px', 'ql-size-32px', 'ql-size-36px']);
                if ($classes !== []) {
                    $target->setAttribute('class', implode(' ', $classes));
                }
                if ($tag === 'a') {
                    $href = trim($node->getAttribute('href'));
                    if (preg_match('~^(?:https://[^\s/]+|/(?!/))~i', $href) && ! preg_match('/[\x00-\x20\x7f\\\\]/', $href)) {
                        $target->setAttribute('href', $href);
                    }
                }
                $parent->appendChild($target);
            }
            if ($tag === 'span' && $target === $parent) {
                $classes = array_intersect(preg_split('/\s+/', $node->getAttribute('class')) ?: [], ['ql-size-small', 'ql-size-large', 'ql-size-huge', 'ql-size-12px', 'ql-size-14px', 'ql-size-16px', 'ql-size-18px', 'ql-size-20px', 'ql-size-24px', 'ql-size-28px', 'ql-size-32px', 'ql-size-36px']);
                if ($classes !== []) {
                    $target = $output->createElement('span');
                    $target->setAttribute('class', implode(' ', $classes));
                    $parent->appendChild($target);
                }
            }
            foreach ($node->childNodes as $child) {
                $copy($child, $target);
            }
        };
        foreach ($source->documentElement?->childNodes ?? [] as $child) {
            $copy($child, $container);
        }

        $result = '';
        foreach ($container->childNodes as $child) {
            $result .= $output->saveHTML($child);
        }

        return $result;
    }

    private function assertPublishable(string $resource, Model $record): void
    {
        if ($resource === 'products') {
            abort_unless(ProductCategory::query()->whereKey($record->getAttribute('category_id'))->where('status', 'published')->exists(), 422, 'Pilih kategori yang aktif sebelum publikasi.');
        }

        if (isset(self::TRANSLATIONS[$resource])) {
            $idTranslation = $record->translations()->where('lang', 'id')->first();
            $translationFields = $this->publicationTranslationFields(
                $resource,
                $record,
                $idTranslation?->only(array_keys($this->translationRules($resource, $record))) ?? [],
            );

            foreach (['id'] as $language) {
                $translation = $record->translations()->where('lang', $language)->first();
                abort_if($translation === null, 422, 'Konten Bahasa Indonesia wajib diisi sebelum publikasi.');
                foreach ($translationFields as $field) {
                    abort_if(blank($translation->{$field}), 422, 'Lengkapi seluruh isian wajib Bahasa Indonesia sebelum publikasi.');
                }
            }
        }

        $mediaField = match ($resource) {
            'brands' => 'logo_media_id',
            'documentations' => 'cover_media_id',
            'hero-slides', 'certificates', 'products' => 'media_id',
            default => null,
        };
        abort_if($mediaField !== null && ! $record->getAttribute($mediaField), 422, 'Gambar wajib dipilih sebelum publikasi.');

        $requiredCommonFields = match ($resource) {
            'brands' => ['name', 'logo_media_id'],
            'products' => ['category_id', 'media_id'],
            'documentations' => ['type', 'cover_media_id', 'event_date'],
            'job-openings' => ['employment_type', 'location', 'posted_at', 'apply_url'],
            default => [],
        };

        foreach ($requiredCommonFields as $field) {
            abort_if(blank($record->getAttribute($field)), 422, 'Seluruh isian wajib harus lengkap sebelum konten dapat dipublikasikan.');
        }

        if ($record instanceof HeroSlide) {
            $hasCtaLabel = $record->translations()->whereIn('lang', config('cms.content.supported_languages'))->get()->contains(fn ($translation): bool => filled($translation->cta_label));
            abort_if($hasCtaLabel && blank($record->cta_url), 422, 'Link tombol wajib diisi bila label CTA digunakan.');
        }

        if ($record instanceof ContentBlock) {
            if ($record->block_key === 'home_coverage') {
                abort_if(! $record->media_id, 422, 'Gambar jangkauan wajib dipilih sebelum publikasi.');
            }
            if ($record->block_key === 'home_cta') {
                $translation = $record->translations()->where('lang', 'id')->first();
                $buttons = collect($translation?->buttons ?? []);
                $hasCompleteButton = $buttons->contains(fn (array $button): bool => filled($button['label'] ?? null) && filled($button['url'] ?? null));
                $hasIncompleteButton = $buttons->contains(fn (array $button): bool => filled($button['label'] ?? null) !== filled($button['url'] ?? null));
                $hasLegacyButton = filled($translation?->cta_label) && filled($record->cta_url);
                abort_if((! $hasCompleteButton && ! $hasLegacyButton) || $hasIncompleteButton, 422, 'Tambahkan minimal satu tombol CTA dengan label dan URL sebelum publikasi.');
            }
            if ($record->block_key === 'home_coverage') {
                $translation = $record->translations()->where('lang', 'id')->first();
                $metrics = collect($translation?->metrics ?? []);
                $hasIncompleteMetric = $metrics->contains(fn (array $metric): bool => filled($metric['value'] ?? null) !== filled($metric['label'] ?? null));
                abort_if($hasIncompleteMetric, 422, 'Lengkapi nilai dan label setiap metrik sebelum publikasi.');
            }
        }

        if ($record instanceof Documentation && $record->type === 'article') {
            abort_if(blank($record->translations()->where('lang', 'id')->value('body')), 422, 'Isi artikel wajib diisi sebelum publikasi.');
        }

    }

    private function saveBrochure(Request $request, string $resource, Model $record): void
    {
        if ($resource !== 'products' || ! $request->hasFile('brochure')) {
            return;
        }

        $previousPath = $record->getAttribute('brochure_path');
        $productName = $record->translations()->where('lang', 'id')->value('name')
            ?: $record->translations()->orderBy('lang')->value('name');
        $baseName = Str::slug($productName ?: 'produk-'.$record->getKey()) ?: 'produk-'.$record->getKey();
        $disk = Storage::disk('public');
        $suffix = 1;

        do {
            $fileName = $baseName.($suffix > 1 ? '-'.$suffix : '').'.pdf';
            $path = 'brochures/cms/'.$fileName;
            $suffix++;
        } while ($disk->exists($path) || Product::query()->where('brochure_path', $path)->where('id', '<>', $record->getKey())->exists());

        abort_if($request->file('brochure')->storeAs('brochures/cms', $fileName, 'public') === false, 500, 'Brosur PDF gagal disimpan ke Media Library.');
        $record->forceFill(['brochure_path' => $path])->save();

        if ($previousPath && $previousPath !== $path && ! str_contains($previousPath, '://')) {
            Storage::disk('public')->delete($previousPath);
        }
    }

    /** @param list<array{media_id: int, caption_id?: ?string, caption_en?: ?string, caption_zh?: ?string}>|null $gallery */
    private function saveGallery(Request $request, string $resource, Model $record): void
    {
        if ($resource !== 'documentations' || ! $request->boolean('gallery_present')) {
            return;
        }

        $gallery = $request->input('gallery', []);
        $sync = [];
        foreach ($gallery as $index => $item) {
            $sync[(int) $item['media_id']] = [
                'caption_id' => $item['caption_id'] ?? null,
                'caption_en' => $item['caption_en'] ?? null,
                'caption_zh' => $item['caption_zh'] ?? null,
                'display_order' => $index + 1,
            ];
        }
        $record->gallery()->sync($sync);
    }

    private function allowPartialDraft(mixed $rule): mixed
    {
        if (is_array($rule)) {
            return array_map(fn (string $item): string => preg_replace('/^required(?:\||$)/', 'nullable|', $item) ?? $item, $rule);
        }

        if (is_string($rule)) {
            return preg_replace('/(^|\|)required(?=\||$)/', '$1nullable', $rule) ?? $rule;
        }

        return $rule;
    }

    /** @param class-string<Model> $translationClass */
    private function uniqueSlug(string $translationClass, Model $record, string $language, string $candidate): string
    {
        $slug = Str::slug($candidate);
        $base = $slug;
        $suffix = 2;

        while ($translationClass::query()
            ->where('lang', $language)
            ->where('slug', $slug)
            ->where('id', '!=', $record->translations()->where('lang', $language)->value('id') ?? 0)
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function loadTranslations(Model $record): Model
    {
        if (method_exists($record, 'translations')) {
            $record->load('translations');
        }

        return $record;
    }
}
