@extends('admin.layout')
@php
    $isBrochureFocus = $resource === 'products' && request()->query('section') === 'specifications-brochures';
    $hasMediaPreview = in_array($resource, ['products', 'certificates', 'documentations'], true);
    $contentBlockSections = [
        'home_coverage' => 'Jangkauan Kami',
        'home_cta' => 'CTA Hubungi Kami',
        'about_company' => 'Tentang Perusahaan',
    ];
    $activeContentBlockSection = request()->query('section');
    $isContentBlockFocus = $resource === 'content-blocks' && isset($contentBlockSections[$activeContentBlockSection]);
    $isHeroResource = $resource === 'hero-slides';
    $isProductResource = $resource === 'products';
    $pageTitle = $isContentBlockFocus ? $contentBlockSections[$activeContentBlockSection] : ($isProductResource ? ($isBrochureFocus ? 'Spesifikasi & Brosur' : 'Daftar Produk') : $title);
@endphp
@section('title', $isBrochureFocus ? 'Spesifikasi & Brosur' : $pageTitle)
@section('page-heading', $isBrochureFocus ? 'Spesifikasi & Brosur' : $pageTitle)
@section('breadcrumb', $isHeroResource ? 'Pengaturan Konten  /  Beranda  /  Hero / Banner' : 'ONEIS CMS  /  Konten Landing Page  /  '.$pageTitle)
@section('page-description', $isHeroResource ? 'Kelola slide utama pada halaman beranda' : '')
@section('page-actions')
    <a class="admin-button admin-button--primary" href="{{ route('admin.content.create', array_merge(['resource' => $resource], $isContentBlockFocus || $isBrochureFocus ? ['section' => $activeContentBlockSection ?? 'specifications-brochures'] : [])) }}"><span class="material-symbols-rounded" aria-hidden="true">add</span>{{ $isProductResource ? 'Tambah Produk' : 'Tambah Data' }}</a>
@endsection
@section('content')
@if ($isHeroResource)
    @php
        $heroTotal = array_sum($heroStats ?? []);
        $heroPublished = $heroStats['published'] ?? 0;
        $heroDraft = ($heroStats['draft'] ?? 0) + ($heroStats['unpublished'] ?? 0);
    @endphp
    <section class="admin-hero-stats" aria-label="Ringkasan banner">
        <article><span><span>Total Slide</span><span class="material-symbols-rounded" aria-hidden="true">view_carousel</span></span><strong>{{ $heroTotal }}</strong></article>
        <article><span><span>Published</span><span class="material-symbols-rounded admin-hero-stats__green" aria-hidden="true">check_circle</span></span><strong>{{ $heroPublished }}</strong></article>
        <article><span><span>Draft / Unpublish</span><span class="material-symbols-rounded admin-hero-stats__orange" aria-hidden="true">pending</span></span><strong>{{ $heroDraft }}</strong></article>
    </section>

    <section class="admin-panel admin-hero-panel">
        <form class="admin-hero-toolbar" method="GET" action="{{ route('admin.content.index', $resource) }}">
            <label>Status<select name="status"><option value="">Semua Status</option>@foreach (['published' => 'Published', 'draft' => 'Draft', 'unpublished' => 'Unpublish'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></label>
            <label>Tampilkan<select name="per_page"><option value="10" @selected(request('per_page', 10) == 10)>10 data</option><option value="25" @selected(request('per_page') == 25)>25 data</option><option value="50" @selected(request('per_page') == 50)>50 data</option></select></label>
            <label class="admin-hero-search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari berdasarkan judul banner..."></label>
            <button class="admin-button" type="submit">Cari</button>
        </form>
        <div class="admin-table-wrap admin-hero-table-wrap" data-reorder-list="{{ implode(',', $heroSlideIds) }}">
            <table class="admin-table admin-hero-table">
                <thead><tr><th>Aksi</th><th>Urutan</th><th>Preview Gambar</th><th>Judul &amp; Informasi Slide</th><th>Status</th><th>Tanggal</th></tr></thead>
                <tbody>
                    @forelse ($records as $record)
                        @php
                            $translation = $record->translations->firstWhere('lang', 'id') ?? $record->translations->first();
                            $heroImageUrl = $record->media
                                ? (file_exists(public_path(ltrim($record->media->path, '/')))
                                    ? asset(ltrim($record->media->path, '/'))
                                    : Illuminate\Support\Facades\Storage::disk(config('cms.media.disk'))->url($record->media->path))
                                : null;
                            $heroStatus = $record->status ?? 'draft';
                            $heroOrderIndex = array_search($record->id, $heroSlideIds);
                        @endphp
                        <tr data-slide-row data-slide-id="{{ $record->id }}">
                            <td><div class="admin-hero-actions">
                                <a href="{{ route('admin.content.show', ['resource' => 'hero-slides', 'id' => $record->id]) }}" aria-label="Lihat detail slide" title="Lihat detail"><span class="material-symbols-rounded" aria-hidden="true">visibility</span></a>
                                <button type="button" aria-label="{{ $heroStatus === 'published' ? 'Unpublish' : 'Publikasikan' }} slide" title="{{ $heroStatus === 'published' ? 'Unpublish' : 'Publikasikan' }}" data-status-url="{{ url('/api/v1/admin/'.$resource.'/'.$record->id.'/status') }}" data-next-status="{{ $heroStatus === 'published' ? 'unpublished' : 'published' }}"><span class="material-symbols-rounded" aria-hidden="true">{{ $heroStatus === 'published' ? 'visibility_off' : 'check_circle' }}</span></button>
                                <button type="button" class="is-danger" aria-label="Hapus slide" title="Hapus" data-delete-url="{{ url('/api/v1/admin/'.$resource.'/'.$record->id) }}"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button>
                            </div></td>
                            <td><div class="admin-hero-order"><strong>{{ $record->display_order }}</strong><span><button type="button" aria-label="Naikkan urutan" title="Naikkan" data-reorder-direction="up" @disabled($heroOrderIndex === 0)><span class="material-symbols-rounded" aria-hidden="true">keyboard_arrow_up</span></button><button type="button" aria-label="Turunkan urutan" title="Turunkan" data-reorder-direction="down" @disabled($heroOrderIndex === count($heroSlideIds) - 1)><span class="material-symbols-rounded" aria-hidden="true">keyboard_arrow_down</span></button></span></div></td>
                            <td>@if ($heroImageUrl)<button class="admin-hero-thumbnail" type="button" data-preview-url="{{ $heroImageUrl }}" aria-label="Lihat gambar {{ $translation?->title ?? 'banner' }}"><img src="{{ $heroImageUrl }}" alt="{{ $record->media?->alt_text_id ?? $translation?->title ?? 'Gambar banner' }}" loading="lazy"></button>@else<span class="admin-hero-thumbnail admin-hero-thumbnail--empty"><span class="material-symbols-rounded" aria-hidden="true">image</span></span>@endif</td>
                            <td><strong>{{ $translation?->title ?? 'Tanpa judul' }}</strong>@if (is_array($record->draft_payload))<small class="admin-status admin-status--draft">Draft perubahan tersimpan</small>@endif</td>
                            <td><span class="admin-status admin-status--{{ $heroStatus }}">{{ $heroStatus === 'published' ? 'Published' : ucfirst($heroStatus) }}</span></td>
                            <td class="admin-hero-date"><strong>{{ $record->created_at?->format('d M Y') ?? '—' }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty">Belum ada slide banner.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-hero-pagination">{{ $records->links() }}</div>
    </section>
@elseif ($isProductResource)
    @php
        $productTotal = array_sum($productStats ?? []);
        $productPublished = $productStats['published'] ?? 0;
        $productDraft = ($productStats['draft'] ?? 0) + ($productStats['unpublished'] ?? 0);
    @endphp
    <section class="admin-product-page {{ $isBrochureFocus ? 'admin-product-page--brochures' : '' }}">
        <div class="admin-product-stats" aria-label="Ringkasan produk">
            <article><span>Total Produk</span><strong>{{ $productTotal }}</strong><small>Semua produk katalog</small></article>
            <article class="is-published"><span>Produk Published</span><strong>{{ $productPublished }}</strong><small>Tampil di website publik</small></article>
            <article class="is-draft"><span>Draft / Unpublished</span><strong>{{ $productDraft }}</strong><small>Belum ditayangkan</small></article>
        </div>

        <section class="admin-panel admin-product-panel">
            <form class="admin-product-filters" method="GET" action="{{ route('admin.content.index', 'products') }}">
                @if ($isBrochureFocus)<input type="hidden" name="section" value="specifications-brochures">@endif
                <label>Kategori<select name="category"><option value="">Semua Kategori</option>@foreach ($productCategories ?? [] as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->translations->firstWhere('lang', 'id')?->name ?? 'Kategori tanpa nama' }}</option>@endforeach</select></label>
                <label>Status<select name="status"><option value="">Semua Status</option>@foreach (['published' => 'Published', 'draft' => 'Draft', 'unpublished' => 'Unpublished'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></label>
                @unless ($isBrochureFocus)<label>Unggulan<select name="featured"><option value="">Semua</option><option value="yes" @selected(request('featured') === 'yes')>Unggulan</option><option value="no" @selected(request('featured') === 'no')>Bukan unggulan</option></select></label>@endunless
                <label class="admin-product-filters__search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama produk atau slug…"></label>
                <label>Baris<select name="per_page"><option value="10" @selected(request('per_page', 10) == 10)>10</option><option value="25" @selected(request('per_page') == 25)>25</option><option value="50" @selected(request('per_page') == 50)>50</option></select></label>
                <button class="admin-button admin-button--primary" type="submit"><span class="material-symbols-rounded" aria-hidden="true">filter_list</span>Terapkan</button>
                <a class="admin-button" href="{{ route('admin.content.index', array_merge(['resource' => 'products'], $isBrochureFocus ? ['section' => 'specifications-brochures'] : [])) }}">Reset</a>
            </form>

            <div class="admin-table-wrap admin-product-table-wrap">
                <table class="admin-table admin-product-table">
                    <thead><tr><th>Aksi</th><th>Foto</th><th>Nama Produk</th><th>Kategori</th>@if ($isBrochureFocus)<th>Spesifikasi &amp; Brosur PDF</th>@else<th>Unggulan</th>@endif<th>Urutan</th><th>Status</th><th>Waktu</th></tr></thead>
                    <tbody>
                        @forelse ($records as $record)
                            @php
                                $translation = $record->translations->firstWhere('lang', 'id') ?? $record->translations->first();
                                $categoryTranslation = $record->category?->translations?->firstWhere('lang', 'id') ?? $record->category?->translations?->first();
                                $imageUrl = $record->media?->admin_preview_url;
                                $isFeatured = $record->featuredCategories->contains('id', $record->category_id);
                                $brochurePath = $record->brochure_path ? ltrim($record->brochure_path, '/') : null;
                                $brochureUrl = $brochurePath
                                    ? (file_exists(public_path($brochurePath))
                                        ? asset($brochurePath)
                                        : Illuminate\Support\Facades\Storage::disk(config('cms.media.disk'))->url($brochurePath))
                                    : null;
                                $specificationText = collect($record->parameters ?? [])->flatten()->filter()->implode(' · ');
                            @endphp
                            <tr>
                                <td><div class="admin-product-actions"><a href="{{ route('admin.content.edit', ['resource' => 'products', 'id' => $record->id]) }}" aria-label="Ubah {{ $translation?->name }}" title="Ubah"><span class="material-symbols-rounded" aria-hidden="true">edit</span></a><a href="{{ route('admin.content.preview', ['resource' => 'products', 'id' => $record->id]) }}" target="_blank" rel="noopener" aria-label="Pratinjau {{ $translation?->name }}" title="Pratinjau"><span class="material-symbols-rounded" aria-hidden="true">open_in_new</span></a><button type="button" aria-label="{{ $record->status === 'published' ? 'Unpublish' : 'Publish' }} {{ $translation?->name }}" title="{{ $record->status === 'published' ? 'Unpublish' : 'Publish' }}" data-status-url="{{ url('/api/v1/admin/products/'.$record->id.'/status') }}" data-next-status="{{ $record->status === 'published' ? 'unpublished' : 'published' }}"><span class="material-symbols-rounded" aria-hidden="true">{{ $record->status === 'published' ? 'visibility_off' : 'visibility' }}</span></button><button type="button" class="is-danger" aria-label="Hapus {{ $translation?->name }}" title="Hapus" data-delete-url="{{ url('/api/v1/admin/products/'.$record->id) }}"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></div></td>
                                <td>@if ($imageUrl)<button class="admin-product-image" type="button" data-preview-url="{{ $imageUrl }}" aria-label="Lihat foto {{ $translation?->name }}"><img src="{{ $imageUrl }}" alt="{{ $record->media?->alt_text_id ?? $translation?->name ?? '' }}" loading="lazy"></button>@else<span class="admin-product-image admin-product-image--empty"><span class="material-symbols-rounded" aria-hidden="true">image</span></span>@endif</td>
                                <td class="admin-product-name"><strong>{{ $translation?->name ?? 'Produk tanpa nama' }}</strong>@if ($isBrochureFocus)<small>{{ $translation?->description ?? $record->subcategory ?? '—' }}</small>@endif</td>
                                <td>{{ $categoryTranslation?->name ?? 'Tanpa kategori' }}</td>
                                @if ($isBrochureFocus)
                                    <td class="admin-product-brochure">
                                        @if ($brochureUrl)
                                            <a href="{{ $brochureUrl }}" target="_blank" rel="noopener"><span class="material-symbols-rounded" aria-hidden="true">picture_as_pdf</span>Buka PDF</a>
                                            <small>{{ basename($record->brochure_path) }}</small>
                                        @else
                                            <span class="admin-status">Belum ada PDF</span>
                                        @endif
                                        @if ($specificationText !== '')
                                            <small class="admin-product-specification">{{ $specificationText }}</small>
                                        @endif
                                    </td>
                                @else
                                    <td>
                                        @if ($isFeatured)
                                            <span class="admin-product-featured"><span class="material-symbols-rounded" aria-hidden="true">star</span>Unggulan</span>
                                        @else
                                            <span class="admin-product-not-featured">—</span>
                                        @endif
                                    </td>
                                @endif
                                <td><span class="admin-product-order">{{ $record->display_order }}</span></td>
                                <td><span class="admin-status admin-status--{{ $record->status }}">{{ ucfirst($record->status) }}</span></td>
                                <td class="admin-product-date"><strong>{{ $record->created_at?->format('d M Y') ?? '—' }}</strong><small>Update {{ $record->updated_at?->format('d M Y H:i') ?? '—' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="admin-empty">Belum ada produk yang sesuai dengan filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <footer class="admin-product-pagination"><p>Menampilkan <strong>{{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }}</strong> dari <strong>{{ $records->total() }}</strong> produk</p>{{ $records->links() }}</footer>
        </section>
    </section>
@else
    <section class="admin-panel">
        <div class="admin-panel__header"><div><p class="admin-eyebrow">{{ $isBrochureFocus ? 'Katalog Produk' : ($isContentBlockFocus ? 'Konten Landing Page' : 'Manajemen Konten') }}</p><h2>{{ $isBrochureFocus ? 'Spesifikasi & Brosur PDF' : $pageTitle }}</h2></div></div>
        @if ($isContentBlockFocus)<p class="admin-section-description">Kelola bagian {{ strtolower($pageTitle) }} pada halaman beranda.</p>@endif
        @if ($isBrochureFocus)<p class="admin-section-description">Kelola file brosur PDF lokal yang terhubung ke setiap produk. Tautan di bawah membuka PDF di tab baru.</p>@endif
        <form class="admin-filter" method="GET">@if ($isBrochureFocus || $isContentBlockFocus)<input type="hidden" name="section" value="{{ $isBrochureFocus ? 'specifications-brochures' : $activeContentBlockSection }}">@endif<input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, judul, atau teks"><select name="status"><option value="">Semua status</option>@foreach (['draft' => 'Draft', 'published' => 'Published', 'unpublished' => 'Unpublished', 'open' => 'Open', 'closed' => 'Closed'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select><select name="per_page"><option value="10" @selected(request('per_page', 10) == 10)>10 baris</option><option value="25" @selected(request('per_page') == 25)>25 baris</option><option value="50" @selected(request('per_page') == 50)>50 baris</option></select><button class="admin-button" type="submit">Filter</button></form>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Aksi</th><th>{{ $isBrochureFocus ? 'Produk' : 'Konten' }}</th>@if ($hasMediaPreview)<th>Gambar</th>@endif @if ($isBrochureFocus)<th>Brosur PDF</th>@endif<th>Status</th><th>Urutan / Info</th></tr></thead><tbody>
            @forelse ($records as $record)
                @php
                    $translation = method_exists($record, 'translations') ? ($record->translations->firstWhere('lang', 'id') ?? $record->translations->first()) : null;
                    $recordTitle = $translation?->title ?? $translation?->name ?? $translation?->text ?? $record->name ?? $record->block_key ?? ($record->page_key ?? class_basename($modelClass));
                    $recordSubtitle = $record->category?->translations?->firstWhere('lang', 'id')?->name ?? $record->type ?? $record->platform ?? $record->email ?? '';
                    $recordIdentifier = $record->block_key ?? $record->id;
                    $statusValue = $record->status ?? null;
                    $imageMedia = match ($resource) {
                        'products', 'certificates' => $record->media,
                        'documentations' => $record->cover,
                        default => null,
                    };
                    $imagePath = $imageMedia ? ltrim($imageMedia->path, '/') : null;
                    $imageUrl = $imagePath
                        ? (file_exists(public_path($imagePath)) ? asset($imagePath) : Illuminate\Support\Facades\Storage::disk(config('cms.media.disk'))->url($imagePath))
                        : null;
                    $brochurePath = $record->brochure_path ? ltrim($record->brochure_path, '/') : null;
                    $brochureUrl = $brochurePath
                        ? (file_exists(public_path($brochurePath))
                            ? asset($brochurePath)
                            : Illuminate\Support\Facades\Storage::disk(config('cms.media.disk'))->url($brochurePath))
                        : null;
                @endphp
                <tr>
                    <td class="admin-actions">
                        <a href="{{ route('admin.content.edit', [$resource, $recordIdentifier]) }}" title="Ubah" aria-label="Ubah"><span class="material-symbols-rounded" aria-hidden="true">edit</span></a>
                        @if ($statusValue)
                            @php $nextStatus = $statusValue === 'published' ? 'unpublished' : ($statusValue === 'open' ? 'closed' : 'published'); @endphp
                            <button type="button" title="{{ $statusValue === 'published' || $statusValue === 'open' ? 'Nonaktifkan' : 'Aktifkan' }}" aria-label="{{ $statusValue === 'published' || $statusValue === 'open' ? 'Nonaktifkan' : 'Aktifkan' }}" data-status-url="{{ url('/api/v1/admin/'.$resource.'/'.$record->id.'/status') }}" data-next-status="{{ $nextStatus }}"><span class="material-symbols-rounded" aria-hidden="true">{{ $statusValue === 'published' || $statusValue === 'open' ? 'visibility_off' : 'visibility' }}</span></button>
                        @endif
                        @if ($resource !== 'company-summary' && $resource !== 'contact-info' && $resource !== 'seo-metas')
                            <button type="button" class="is-danger" title="Hapus" aria-label="Hapus" data-delete-url="{{ url('/api/v1/admin/'.$resource.'/'.$recordIdentifier) }}"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button>
                        @endif
                    </td>
                    <td><strong>{{ $recordTitle }}</strong><small>{{ $recordSubtitle }}</small></td>
                    @if ($hasMediaPreview)<td>@if ($imageUrl)<button class="admin-content-thumb" type="button" data-preview-url="{{ $imageUrl }}" aria-label="Lihat gambar {{ $recordTitle }}"><img src="{{ $imageUrl }}" alt="{{ $imageMedia?->alt_text_id ?: $recordTitle }}" loading="lazy"></button>@else<span class="admin-content-thumb admin-content-thumb--empty">Belum ada gambar</span>@endif</td>@endif
                    @if ($isBrochureFocus)<td>@if ($brochureUrl)<a href="{{ $brochureUrl }}" target="_blank" rel="noopener">Buka PDF</a><small>{{ basename($record->brochure_path) }}</small>@else<span class="admin-status">Belum diunggah</span>@endif</td>@endif
                    <td>@if ($statusValue)<span class="admin-status admin-status--{{ $statusValue }}">{{ ucfirst($statusValue) }}</span>@else<span class="admin-status">Data tunggal</span>@endif</td>
                    <td>{{ $record->display_order ?? $record->event_date?->format('d M Y') ?? $record->posted_at?->format('d M Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ 4 + (int) $hasMediaPreview + (int) $isBrochureFocus }}" class="admin-empty">Belum ada data {{ $isBrochureFocus ? 'produk' : strtolower($pageTitle) }}.</td></tr>
            @endforelse
        </tbody></table></div>
        {{ $records->links() }}
    </section>
@endif
@endsection
