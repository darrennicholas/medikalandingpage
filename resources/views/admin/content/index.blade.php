@extends('admin.layout')
@php
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
    $pageTitle = $isContentBlockFocus ? $contentBlockSections[$activeContentBlockSection] : $title;
@endphp
@section('title', $pageTitle)
@section('page-heading', $pageTitle)
@section('breadcrumb', $isHeroResource ? 'Pengaturan Konten  /  Beranda  /  Hero / Banner' : 'CMS  /  Konten Landing Page  /  '.$pageTitle)
@section('page-description', $isHeroResource ? 'Kelola slide utama pada halaman beranda' : ($isProductResource ? 'Kelola data, spesifikasi, dan brosur PDF produk dari satu daftar.' : ''))
@section('page-actions')
    <a class="admin-button admin-button--primary" href="{{ route('admin.content.create', array_merge(['resource' => $resource], $isContentBlockFocus ? ['section' => $activeContentBlockSection] : [])) }}"><span class="material-symbols-rounded" aria-hidden="true">add</span>{{ $isProductResource ? 'Tambah Produk' : 'Tambah Data' }}</a>
@endsection
@section('content')
@if ($isHeroResource)
    @php
        $heroTotal = array_sum($heroStats ?? []);
        $heroPublished = $heroStats['published'] ?? 0;
        $heroDraft = $heroStats['draft'] ?? 0;
    @endphp
    <section class="admin-hero-stats" aria-label="Ringkasan banner">
        <article class="admin-summary-card"><span>Total Slide</span><strong>{{ $heroTotal }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">view_carousel</span></article>
        <article class="admin-summary-card admin-summary-card--success"><span>Published</span><strong>{{ $heroPublished }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">check_circle</span></article>
        <article class="admin-summary-card admin-summary-card--warning"><span>Draft</span><strong>{{ $heroDraft }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">pending</span></article>
    </section>

    <section class="admin-panel admin-hero-panel">
        <p>1 slide aktif ditampilkan sebagai gambar tunggal; lebih dari 1 slide aktif ditampilkan sebagai slider.</p>
        <form class="admin-hero-toolbar" method="GET" action="{{ route('admin.content.index', $resource) }}">
            <label>Status<select name="status"><option value="">Semua Status</option>@foreach (['published' => 'Published', 'draft' => 'Draft'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></label>
            <label>Tampilkan<select name="per_page"><option value="10" @selected(request('per_page', 10) == 10)>10 data</option><option value="25" @selected(request('per_page') == 25)>25 data</option><option value="50" @selected(request('per_page') == 50)>50 data</option></select></label>
            <label class="admin-hero-search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari berdasarkan judul banner..."></label>
            <div class="admin-filter-actions"><button class="admin-button admin-button--primary" type="submit">Cari</button><a class="admin-button" href="{{ route('admin.content.index', $resource) }}">Reset</a></div>
        </form>
        <div class="admin-table-wrap admin-hero-table-wrap" data-reorder-list="{{ implode(',', $heroSlideIds) }}">
            <table class="admin-table admin-hero-table">
                <thead><tr><th>Aksi</th><th>Urutan</th><th>Preview Gambar</th><th>Judul &amp; Informasi Slide</th><th>Status</th><th>Tanggal</th></tr></thead>
                <tbody>
                    @forelse ($records as $record)
                        @php
                            $translation = $record->translations->firstWhere('lang', 'id') ?? $record->translations->first();
                            $heroImageUrl = $record->media ? route('media.stream', ['media' => $record->media->id]) : null;
                            $heroStatus = $record->status ?? 'draft';
                            $heroOrderIndex = array_search($record->id, $heroSlideIds);
                        @endphp
                        <tr data-slide-row data-slide-id="{{ $record->id }}">
                            <td><div class="admin-hero-actions">
                                <a href="{{ route('admin.content.show', ['resource' => 'hero-slides', 'id' => $record->id]) }}" aria-label="Lihat detail slide" title="Lihat detail"><span class="material-symbols-rounded" aria-hidden="true">visibility</span></a>
                                <button type="button" aria-label="{{ $heroStatus === 'published' ? 'Simpan sebagai Draft' : 'Publikasikan' }} slide" title="{{ $heroStatus === 'published' ? 'Simpan sebagai Draft' : 'Publikasikan' }}" data-status-url="{{ url('/api/v1/admin/'.$resource.'/'.$record->id.'/status') }}" data-next-status="{{ $heroStatus === 'published' ? 'draft' : 'published' }}"><span class="material-symbols-rounded" aria-hidden="true">{{ $heroStatus === 'published' ? 'visibility_off' : 'visibility' }}</span></button>
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
        @include('admin.partials.pagination', ['paginator' => $records, 'label' => 'banner'])
    </section>
@elseif ($isProductResource)
    @php
        $productTotal = array_sum($productStats ?? []);
        $productPublished = $productStats['published'] ?? 0;
        $productDraft = $productStats['draft'] ?? 0;
    @endphp
    <section class="admin-product-page">
        <div class="admin-product-stats" aria-label="Ringkasan produk">
            <article class="admin-summary-card"><span>Total Produk</span><strong>{{ $productTotal }}</strong><small>Semua produk katalog</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">inventory_2</span></article>
            <article class="admin-summary-card admin-summary-card--success"><span>Published</span><strong>{{ $productPublished }}</strong><small>Tampil di website publik</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">check_circle</span></article>
            <article class="admin-summary-card admin-summary-card--warning"><span>Draft</span><strong>{{ $productDraft }}</strong><small>Belum ditayangkan</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">pending</span></article>
        </div>

        <section class="admin-panel admin-product-panel">
            <form class="admin-product-filters" method="GET" action="{{ route('admin.content.index', 'products') }}">
                <label>Kategori<select name="category"><option value="">Semua Kategori</option>@foreach ($productCategories ?? [] as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->translations->firstWhere('lang', 'id')?->name ?? 'Kategori tanpa nama' }}</option>@endforeach</select></label>
                <label>Status<select name="status"><option value="">Semua Status</option>@foreach (['published' => 'Published', 'draft' => 'Draft'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></label>
                <label>Unggulan<select name="featured"><option value="">Semua</option><option value="yes" @selected(request('featured') === 'yes')>Unggulan</option><option value="no" @selected(request('featured') === 'no')>Bukan unggulan</option></select></label>
                <label class="admin-product-filters__search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama produk atau slug…"></label>
                <label>Baris<select name="per_page"><option value="10" @selected(request('per_page', 10) == 10)>10</option><option value="25" @selected(request('per_page') == 25)>25</option><option value="50" @selected(request('per_page') == 50)>50</option></select></label>
                <div class="admin-filter-actions"><button class="admin-button admin-button--primary" type="submit">Cari</button><a class="admin-button" href="{{ route('admin.content.index', 'products') }}">Reset</a></div>
            </form>

            <div class="admin-table-wrap admin-product-table-wrap">
                <table class="admin-table admin-product-table">
                    <thead><tr><th>Aksi</th><th>Foto</th><th>Nama Produk</th><th>Kategori</th><th>Unggulan</th><th>Spesifikasi &amp; Brosur PDF</th><th>Urutan</th><th>Status</th><th>Waktu</th></tr></thead>
                    <tbody>
                        @forelse ($records as $record)
                            @php
                                $translation = $record->translations->firstWhere('lang', 'id') ?? $record->translations->first();
                                $categoryTranslation = $record->category?->translations?->firstWhere('lang', 'id') ?? $record->category?->translations?->first();
                                $imageUrl = $record->media?->admin_preview_url;
                                $isFeatured = $record->featuredCategories->contains('id', $record->category_id);
                                $brochurePath = $record->brochure_path ? ltrim($record->brochure_path, '/') : null;
                                $brochureUrl = $brochurePath
                                    ? route('stored.brochure', ['path' => preg_replace('#^(?:public/)?brochures/#', '', $brochurePath)])
                                    : null;
                                $brochureName = $translation?->name
                                    ? \Illuminate\Support\Str::slug($translation->name).'.pdf'
                                    : ($record->brochure_path ? basename($record->brochure_path) : null);
                                $specificationText = collect($record->parameters ?? [])->flatten()->filter()->implode(' · ');
                            @endphp
                            <tr>
                                <td><div class="admin-product-actions"><a href="{{ route('admin.content.edit', ['resource' => 'products', 'id' => $record->id]) }}" aria-label="Ubah {{ $translation?->name }}" title="Ubah"><span class="material-symbols-rounded" aria-hidden="true">edit</span></a><a href="{{ route('admin.content.preview', ['resource' => 'products', 'id' => $record->id]) }}" target="_blank" rel="noopener" aria-label="Pratinjau {{ $translation?->name }}" title="Pratinjau"><span class="material-symbols-rounded" aria-hidden="true">open_in_new</span></a><button type="button" aria-label="{{ $record->status === 'published' ? 'Simpan sebagai Draft' : 'Publish' }} {{ $translation?->name }}" title="{{ $record->status === 'published' ? 'Simpan sebagai Draft' : 'Publish' }}" data-status-url="{{ url('/api/v1/admin/products/'.$record->id.'/status') }}" data-next-status="{{ $record->status === 'published' ? 'draft' : 'published' }}"><span class="material-symbols-rounded" aria-hidden="true">{{ $record->status === 'published' ? 'visibility_off' : 'visibility' }}</span></button><button type="button" class="is-danger" aria-label="Hapus {{ $translation?->name }}" title="Hapus" data-delete-url="{{ url('/api/v1/admin/products/'.$record->id) }}"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></div></td>
                                <td>@if ($imageUrl)<button class="admin-product-image" type="button" data-preview-url="{{ $imageUrl }}" aria-label="Lihat foto {{ $translation?->name }}"><img src="{{ $imageUrl }}" alt="{{ $record->media?->alt_text_id ?? $translation?->name ?? '' }}" loading="lazy"></button>@else<span class="admin-product-image admin-product-image--empty"><span class="material-symbols-rounded" aria-hidden="true">image</span></span>@endif</td>
                                <td class="admin-product-name"><strong>{{ $translation?->name ?? 'Produk tanpa nama' }}</strong><small>{{ $record->new_until && $record->new_until->gte(today()) ? 'New sampai '.$record->new_until->format('d/m/Y') : 'Tidak New' }}</small></td>
                                <td>{{ $categoryTranslation?->name ?? 'Tanpa kategori' }}</td>
                                <td>@if ($isFeatured)<span class="admin-product-featured"><span class="material-symbols-rounded" aria-hidden="true">star</span>Unggulan</span>@else<span class="admin-product-not-featured">—</span>@endif</td>
                                <td class="admin-product-brochure">
                                    @if ($brochureUrl)
                                        <a href="{{ $brochureUrl }}" target="_blank" rel="noopener"><span class="material-symbols-rounded" aria-hidden="true">picture_as_pdf</span>Buka PDF</a>
                                        <small>{{ $brochureName }}</small>
                                    @else
                                        <span class="admin-status">Belum ada PDF</span>
                                    @endif
                                    @if ($specificationText !== '')<small class="admin-product-specification">{{ $specificationText }}</small>@endif
                                </td>
                                <td><span class="admin-product-order">{{ $record->display_order }}</span></td>
                                <td><span class="admin-status admin-status--{{ $record->status }}">{{ ucfirst($record->status) }}</span></td>
                                <td class="admin-product-date"><strong>{{ $record->created_at?->format('d M Y') ?? '—' }}</strong><small>Update {{ $record->updated_at?->format('d M Y H:i') ?? '—' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="admin-empty">Belum ada produk yang sesuai dengan filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $records, 'label' => 'produk'])
        </section>
    </section>
@else
    <section class="admin-panel">
        <div class="admin-panel__header"><div><p class="admin-eyebrow">{{ $isContentBlockFocus ? 'Konten Landing Page' : 'Manajemen Konten' }}</p><h2>{{ $pageTitle }}</h2></div></div>
        @if ($isContentBlockFocus)<p class="admin-section-description">Kelola bagian {{ strtolower($pageTitle) }} pada halaman beranda.</p>@endif
        <form class="admin-filter" method="GET">@if ($isContentBlockFocus)<input type="hidden" name="section" value="{{ $activeContentBlockSection }}">@endif<input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, judul, atau teks"><select name="status"><option value="">Semua status</option>@foreach (['draft' => 'Draft', 'published' => 'Published', 'open' => 'Open', 'closed' => 'Closed'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select><select name="per_page"><option value="10" @selected(request('per_page', 10) == 10)>10 baris</option><option value="25" @selected(request('per_page') == 25)>25 baris</option><option value="50" @selected(request('per_page') == 50)>50 baris</option></select><div class="admin-filter-actions"><button class="admin-button admin-button--primary" type="submit">Cari</button><a class="admin-button" href="{{ route('admin.content.index', array_merge(['resource' => $resource], $isContentBlockFocus ? ['section' => $activeContentBlockSection] : [])) }}">Reset</a></div></form>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Aksi</th><th>Konten</th>@if ($hasMediaPreview)<th>Gambar</th>@endif<th>Status</th><th>Urutan / Info</th></tr></thead><tbody>
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
                    $imageUrl = $imageMedia ? route('media.stream', ['media' => $imageMedia->id]) : null;
                    $brochurePath = $record->brochure_path ? ltrim($record->brochure_path, '/') : null;
                    $brochureUrl = $brochurePath
                        ? route('stored.brochure', ['path' => preg_replace('#^(?:public/)?brochures/#', '', $brochurePath)])
                        : null;
                @endphp
                <tr>
                    <td class="admin-actions">
                        <a href="{{ route('admin.content.edit', [$resource, $recordIdentifier]) }}" title="Ubah" aria-label="Ubah"><span class="material-symbols-rounded" aria-hidden="true">edit</span></a>
                        @if ($statusValue)
                            @php $nextStatus = $statusValue === 'published' ? ($resource === 'job-openings' ? 'closed' : 'draft') : ($statusValue === 'open' ? 'closed' : 'published'); @endphp
                            <button type="button" title="{{ $statusValue === 'published' || $statusValue === 'open' ? 'Simpan sebagai Draft' : 'Publish' }}" aria-label="{{ $statusValue === 'published' || $statusValue === 'open' ? 'Simpan sebagai Draft' : 'Publish' }}" data-status-url="{{ url('/api/v1/admin/'.$resource.'/'.$record->id.'/status') }}" data-next-status="{{ $nextStatus }}"><span class="material-symbols-rounded" aria-hidden="true">{{ $statusValue === 'published' || $statusValue === 'open' ? 'visibility_off' : 'visibility' }}</span></button>
                        @endif
                        @if ($resource !== 'company-summary' && $resource !== 'contact-info' && $resource !== 'seo-metas')
                            <button type="button" class="is-danger" title="Hapus" aria-label="Hapus" data-delete-url="{{ url('/api/v1/admin/'.$resource.'/'.$recordIdentifier) }}"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button>
                        @endif
                    </td>
                    <td><strong>{{ $recordTitle }}</strong><small>{{ $recordSubtitle }}</small></td>
                    @if ($hasMediaPreview)<td>@if ($imageUrl)<button class="admin-content-thumb" type="button" data-preview-url="{{ $imageUrl }}" aria-label="Lihat gambar {{ $recordTitle }}"><img src="{{ $imageUrl }}" alt="{{ $imageMedia?->alt_text_id ?: $recordTitle }}" loading="lazy"></button>@else<span class="admin-content-thumb admin-content-thumb--empty">Belum ada gambar</span>@endif</td>@endif
                    <td>@if ($statusValue)<span class="admin-status admin-status--{{ $statusValue }}">{{ ucfirst($statusValue) }}</span>@else<span class="admin-status">Data tunggal</span>@endif</td>
                    <td>{{ $record->display_order ?? $record->event_date?->format('d M Y') ?? $record->posted_at?->format('d M Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ 4 + (int) $hasMediaPreview }}" class="admin-empty">Belum ada data {{ strtolower($pageTitle) }}.</td></tr>
            @endforelse
        </tbody></table></div>
        @include('admin.partials.pagination', ['paginator' => $records, 'label' => 'konten'])
    </section>
@endif
@endsection
