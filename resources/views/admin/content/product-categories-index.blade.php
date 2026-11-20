@extends('admin.layout')

@section('title', 'Kategori Pemeriksaan')
@section('page-heading', 'Kategori Pemeriksaan')
@section('breadcrumb', 'Konten Landing Page  /  Produk  /  Kategori Pemeriksaan')
@section('page-description', 'Kelola kategori dan urutan tab yang tampil pada katalog produk.')
@section('page-actions')
    <a class="admin-button admin-button--primary" href="{{ route('admin.content.create', 'product-categories') }}">
        <span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah Kategori
    </a>
@endsection

@section('content')
    @php
        $categoryTotal = array_sum($categoryStats);
        $categoryPublished = $categoryStats['published'] ?? 0;
        $categoryInactive = $categoryTotal - $categoryPublished;
    @endphp

    <section class="admin-management-stats admin-category-stats" aria-label="Ringkasan kategori">
        <article class="admin-management-stat admin-summary-card">
            <span>Total Kategori</span>
            <strong>{{ $categoryTotal }}</strong>
            <a href="{{ route('admin.content.index', 'product-categories') }}">Semua kategori <span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span></a>
            <span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">category</span>
        </article>
        <article class="admin-management-stat admin-management-stat--success admin-summary-card admin-summary-card--success">
            <span>Published</span>
            <strong>{{ $categoryPublished }}</strong>
            <span class="admin-management-stat__note">Tampil di katalog</span>
            <span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">check_circle</span>
        </article>
        <article class="admin-management-stat admin-management-stat--muted admin-summary-card admin-summary-card--warning">
            <span>Draft / Nonaktif</span>
            <strong>{{ $categoryInactive }}</strong>
            <span class="admin-management-stat__note">Tidak tampil di katalog</span>
            <span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">pending</span>
        </article>
    </section>

    <section class="admin-category-order-note" aria-label="Urutan kategori">
        <span class="material-symbols-rounded" aria-hidden="true">swap_vert</span>
        <p><strong>Urutan tab kategori</strong> mengikuti nomor urutan dan dapat diubah dengan tombol panah di tabel.</p>
    </section>

    <section class="admin-panel admin-category-manager">
        <form class="admin-category-filters" method="GET" action="{{ route('admin.content.index', 'product-categories') }}">
            <label class="admin-category-filters__status" for="category-status-filter">
                Status
                <select id="category-status-filter" name="status">
                    <option value="">Semua status</option>
                    @foreach (['published' => 'Published', 'draft' => 'Draft'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="admin-category-filters__search" for="category-search">
                <span class="material-symbols-rounded" aria-hidden="true">search</span>
                <input id="category-search" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau slug kategori...">
            </label>
            <label class="admin-category-filters__per-page" for="category-per-page">
                <span class="admin-visually-hidden">Jumlah baris</span>
                <select id="category-per-page" name="per_page">
                    @foreach ([10, 25, 50] as $pageSize)
                        <option value="{{ $pageSize }}" @selected((int) request('per_page', 10) === $pageSize)>{{ $pageSize }} baris</option>
                    @endforeach
                </select>
            </label>
            <div class="admin-filter-actions"><button class="admin-button admin-button--primary" type="submit">Cari</button><a class="admin-button" href="{{ route('admin.content.index', 'product-categories') }}">Reset</a></div>
        </form>

        <div class="admin-table-wrap admin-category-table-wrap" data-reorder-list="{{ implode(',', $categoryIds) }}" data-reorder-resource="product-categories">
            <table class="admin-table admin-category-table">
                <thead>
                    <tr>
                        <th>Aksi</th>
                        <th>Urutan</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Produk</th>
                        <th>Status</th>
                        <th>Diperbarui</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        @php
                            $translationId = $record->translations->firstWhere('lang', 'id');
                            $translationEn = $record->translations->firstWhere('lang', 'en');
                            $orderIndex = array_search($record->id, $categoryIds, true);
                            $statusLabel = $record->status === 'published' ? 'Published' : 'Draft';
                            $statusClass = $record->status === 'published' ? 'published' : 'draft';
                        @endphp
                        <tr data-category-row data-reorder-id="{{ $record->id }}">
                            <td>
                                <div class="admin-category-actions">
                                    <a class="admin-icon-button" href="{{ route('admin.content.preview', ['resource' => 'product-categories', 'id' => $record->id]) }}" target="_blank" rel="noopener" aria-label="Pratinjau kategori" title="Pratinjau"><span class="material-symbols-rounded" aria-hidden="true">visibility</span></a>
                                    <a class="admin-icon-button" href="{{ route('admin.content.edit', ['resource' => 'product-categories', 'id' => $record->id]) }}" aria-label="Ubah {{ $translationId?->name ?? 'kategori' }}" title="Ubah"><span class="material-symbols-rounded" aria-hidden="true">edit</span></a>
                                    <button class="admin-icon-button" type="button" aria-label="Ubah status {{ $translationId?->name ?? 'kategori' }}" title="Ubah status" data-status-url="{{ url('/api/v1/admin/product-categories/'.$record->id.'/status') }}" data-next-status="{{ $record->status === 'published' ? 'draft' : 'published' }}">
                                        <span class="material-symbols-rounded" aria-hidden="true">{{ $record->status === 'published' ? 'visibility_off' : 'visibility' }}</span>
                                    </button>
                                    <button class="admin-icon-button is-danger" type="button" aria-label="Hapus {{ $translationId?->name ?? 'kategori' }}" title="Hapus" data-delete-url="{{ url('/api/v1/admin/product-categories/'.$record->id) }}">
                                        <span class="material-symbols-rounded" aria-hidden="true">delete</span>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div class="admin-category-order">
                                    <span class="admin-order-pill">{{ $record->display_order }}</span>
                                    <span class="admin-category-order__controls">
                                        <button type="button" data-reorder-direction="up" aria-label="Naikkan {{ $translationId?->name ?? 'kategori' }}" title="Naikkan urutan" @disabled($orderIndex === 0)><span class="material-symbols-rounded" aria-hidden="true">keyboard_arrow_up</span></button>
                                        <button type="button" data-reorder-direction="down" aria-label="Turunkan {{ $translationId?->name ?? 'kategori' }}" title="Turunkan urutan" @disabled($orderIndex === count($categoryIds) - 1)><span class="material-symbols-rounded" aria-hidden="true">keyboard_arrow_down</span></button>
                                    </span>
                                </div>
                            </td>
                            <td class="admin-category-name">
                                <strong>{{ $translationId?->name ?: 'Tanpa nama' }}</strong>
                                @if ($translationEn?->name)<span>{{ $translationEn->name }}</span>@endif
                            </td>
                            <td><span class="admin-category-product-count"><span class="material-symbols-rounded" aria-hidden="true">inventory_2</span>{{ $record->products_count }}</span></td>
                            <td><span class="admin-status admin-status--{{ $statusClass }}"><i></i>{{ $statusLabel }}</span></td>
                            <td class="admin-category-date">{{ $record->updated_at?->format('d M Y, H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td class="admin-empty" colspan="6">Belum ada kategori yang sesuai dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('admin.partials.pagination', ['paginator' => $records, 'label' => 'kategori'])
    </section>

@endsection
