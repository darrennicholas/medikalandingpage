@extends('admin.layout')

@section('title', 'Kategori Pemeriksaan')
@section('page-heading', 'Kategori Pemeriksaan')
@section('breadcrumb', 'Konten Landing Page  /  Produk  /  Kategori Pemeriksaan')
@section('page-description', 'Kelola kategori dan urutan tab yang tampil pada katalog produk.')
@section('page-actions')
    <button class="admin-button admin-button--primary" type="button" data-open-category-modal>
        <span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah Kategori
    </button>
@endsection

@section('content')
    @php
        $categoryTotal = array_sum($categoryStats);
        $categoryPublished = $categoryStats['published'] ?? 0;
        $categoryInactive = $categoryTotal - $categoryPublished;
    @endphp

    <section class="admin-management-stats admin-category-stats" aria-label="Ringkasan kategori">
        <article class="admin-management-stat">
            <span>Total Kategori</span>
            <strong>{{ $categoryTotal }}</strong>
            <a href="{{ route('admin.content.index', 'product-categories') }}">Semua kategori <span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span></a>
        </article>
        <article class="admin-management-stat admin-management-stat--success">
            <span>Published</span>
            <strong>{{ $categoryPublished }}</strong>
            <span class="admin-management-stat__note">Tampil di katalog</span>
        </article>
        <article class="admin-management-stat admin-management-stat--muted">
            <span>Draft / Nonaktif</span>
            <strong>{{ $categoryInactive }}</strong>
            <span class="admin-management-stat__note">Tidak tampil di katalog</span>
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
                    @foreach (['published' => 'Published', 'draft' => 'Draft', 'unpublished' => 'Nonaktif'] as $value => $label)
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
            <button class="admin-button" type="submit">Terapkan</button>
            @if (request()->filled('q') || request()->filled('status'))
                <a class="admin-button admin-button--light" href="{{ route('admin.content.index', 'product-categories') }}">Reset</a>
            @endif
        </form>

        <div class="admin-table-wrap admin-category-table-wrap" data-reorder-list="{{ implode(',', $categoryIds) }}" data-reorder-resource="product-categories">
            <table class="admin-table admin-category-table">
                <thead>
                    <tr>
                        <th>Aksi</th>
                        <th>Urutan</th>
                        <th>Nama Kategori</th>
                        <th>Slug</th>
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
                            $statusLabel = $record->status === 'published' ? 'Published' : ($record->status === 'unpublished' ? 'Nonaktif' : 'Draft');
                            $statusClass = $record->status === 'published' ? 'published' : ($record->status === 'unpublished' ? 'unpublished' : 'draft');
                        @endphp
                        <tr data-category-row data-reorder-id="{{ $record->id }}">
                            <td>
                                <div class="admin-category-actions">
                                    <a class="admin-icon-button" href="{{ route('admin.content.preview', ['resource' => 'product-categories', 'id' => $record->id]) }}" target="_blank" rel="noopener" aria-label="Pratinjau kategori" title="Pratinjau"><span class="material-symbols-rounded" aria-hidden="true">visibility</span></a>
                                    <button class="admin-icon-button" type="button" data-edit-category
                                        data-category-id="{{ $record->id }}"
                                        data-category-name-id="{{ $translationId?->name }}"
                                        data-category-slug-id="{{ $translationId?->slug }}"
                                        data-category-name-en="{{ $translationEn?->name }}"
                                        data-category-slug-en="{{ $translationEn?->slug }}"
                                        data-category-order="{{ $record->display_order }}"
                                        data-category-status="{{ $record->status }}"
                                        aria-label="Ubah {{ $translationId?->name ?? 'kategori' }}" title="Ubah">
                                        <span class="material-symbols-rounded" aria-hidden="true">edit</span>
                                    </button>
                                    <button class="admin-icon-button" type="button" aria-label="Ubah status {{ $translationId?->name ?? 'kategori' }}" title="Ubah status" data-status-url="{{ url('/api/v1/admin/product-categories/'.$record->id.'/status') }}" data-next-status="{{ $record->status === 'published' ? 'unpublished' : 'published' }}">
                                        <span class="material-symbols-rounded" aria-hidden="true">{{ $record->status === 'published' ? 'toggle_on' : 'toggle_off' }}</span>
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
                            <td class="admin-category-slug">
                                <span>ID: {{ $translationId?->slug ?: '—' }}</span>
                                @if ($translationEn?->slug)<span>EN: {{ $translationEn->slug }}</span>@endif
                            </td>
                            <td><span class="admin-category-product-count"><span class="material-symbols-rounded" aria-hidden="true">inventory_2</span>{{ $record->products_count }}</span></td>
                            <td><span class="admin-status admin-status--{{ $statusClass }}"><i></i>{{ $statusLabel }}</span></td>
                            <td class="admin-category-date">{{ $record->updated_at?->format('d M Y, H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td class="admin-empty" colspan="7">Belum ada kategori yang sesuai dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('admin.partials.pagination', ['paginator' => $records, 'label' => 'kategori'])
    </section>

    <div class="admin-category-modal" data-category-modal data-next-order="{{ count($categoryIds) + 1 }}" hidden>
        <button class="admin-category-modal__backdrop" type="button" aria-label="Tutup form kategori" data-close-category-modal></button>
        <section class="admin-category-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="category-modal-title">
            <header class="admin-category-modal__header">
                <div>
                    <span class="admin-category-modal__marker" aria-hidden="true"></span>
                    <h2 id="category-modal-title" data-category-modal-title>Tambah Kategori Pemeriksaan</h2>
                    <p>Atur nama kategori, slug, urutan, dan publikasi.</p>
                </div>
                <button class="admin-icon-button" type="button" aria-label="Tutup form kategori" data-close-category-modal><span class="material-symbols-rounded" aria-hidden="true">close</span></button>
            </header>

            <form class="admin-category-form" data-admin-form data-resource="product-categories" data-is-editing="0" data-api-url="/api/v1/admin/product-categories" data-redirect-url="{{ request()->fullUrl() }}">
                @csrf
                <div class="admin-category-language-tabs" role="tablist" aria-label="Bahasa kategori">
                    <span>Bahasa konten</span>
                    <div>
                        <button class="is-active" type="button" role="tab" aria-selected="true" data-category-language="id"><b>ID</b> Indonesia</button>
                        <button type="button" role="tab" aria-selected="false" data-category-language="en"><b>EN</b> English</button>
                    </div>
                </div>

                <div class="admin-category-form__body">
                    <div class="admin-category-form__grid">
                        <label class="admin-category-field">
                            <span>Nama Kategori (ID) <b>*</b></span>
                            <input type="text" name="translations[id][name]" id="category-name-id" maxlength="80" required autocomplete="off" placeholder="Contoh: Hematologi & Koagulasi">
                        </label>
                        <label class="admin-category-field">
                            <span>Nama Kategori (EN)</span>
                            <input type="text" name="translations[en][name]" id="category-name-en" maxlength="80" autocomplete="off" placeholder="Hematology & Coagulation">
                        </label>
                        <label class="admin-category-field">
                            <span>Slug (ID) <b>*</b></span>
                            <input type="text" name="translations[id][slug]" id="category-slug-id" maxlength="100" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" autocomplete="off" placeholder="hematologi-koagulasi">
                        </label>
                        <label class="admin-category-field">
                            <span>Slug (EN)</span>
                            <input type="text" name="translations[en][slug]" id="category-slug-en" maxlength="100" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" autocomplete="off" placeholder="hematology-coagulation">
                        </label>
                    </div>

                    <div class="admin-category-form__grid admin-category-form__grid--settings">
                        <label class="admin-category-field admin-category-order-field">
                            <span>Urutan Tab</span>
                            <input type="number" name="display_order" id="category-display-order" min="1" step="1" inputmode="numeric">
                        </label>
                        <fieldset class="admin-category-status-field">
                            <legend>Status Publikasi</legend>
                            <div>
                                <label><input type="radio" name="status" value="published"><span><strong>Published</strong><small>Tampil pada katalog publik.</small></span></label>
                                <label><input type="radio" name="status" value="draft"><span><strong>Draft</strong><small>Tersimpan di CMS.</small></span></label>
                                <label><input type="radio" name="status" value="unpublished"><span><strong>Nonaktif</strong><small>Disembunyikan dari katalog.</small></span></label>
                            </div>
                        </fieldset>
                    </div>

                    <div class="admin-category-public-preview">
                        <div><span>Pratinjau tab publik</span><strong data-category-preview>(Nama kategori)</strong></div>
                        <span class="admin-category-public-preview__language" data-category-preview-language>ID</span>
                    </div>
                </div>

                <div class="admin-category-form__error admin-alert admin-alert--error" data-form-errors hidden role="alert"></div>
                <footer class="admin-category-modal__footer">
                    <button class="admin-button admin-button--light" type="button" data-close-category-modal>Batal</button>
                    <button class="admin-button admin-button--light" type="submit" data-save-status="draft">Simpan Draft</button>
                    <button class="admin-button admin-button--primary" type="submit" data-save-status="published"><span class="material-symbols-rounded" aria-hidden="true">publish</span>Simpan &amp; Publikasikan</button>
                </footer>
            </form>
        </section>
    </div>
@endsection
