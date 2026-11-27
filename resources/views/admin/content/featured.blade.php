@extends('admin.layout')
@section('title', 'Produk Unggulan')
@section('heading', 'Produk Unggulan')
@section('content')
<section class="admin-featured-page">
    @if ($selectedCategory)
        <form method="GET" class="admin-featured-category-bar" data-filter-default-open>
            <label for="featured-category">Kategori pemeriksaan</label>
            <select id="featured-category" name="category">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($selectedCategory->id === $category->id)>{{ $category->translations->firstWhere('lang', 'id')?->name ?? 'Kategori tanpa nama' }}</option>
                @endforeach
            </select>
            <span>{{ $products->count() }} produk Published</span>
            <div class="admin-filter-actions"><button class="admin-button admin-button--primary" type="submit">Cari</button><a class="admin-button" href="{{ route('admin.featured.index') }}">Reset</a></div>
        </form>

        <section class="admin-featured-quota" aria-live="polite">
            <span class="material-symbols-rounded" aria-hidden="true">verified</span>
            <div><strong><span data-featured-count>{{ count($selectedIds) }}</span> / 6 produk dipilih</strong><small>Maksimal enam produk unggulan untuk kategori ini.</small></div>
            <div class="admin-featured-quota__track"><span data-featured-progress style="width: {{ min(100, count($selectedIds) / 6 * 100) }}%"></span></div>
            <button class="admin-button" type="button" data-featured-reset>Reset pilihan</button>
            <button class="admin-button admin-button--primary" type="submit" form="featured-products-form"><span class="material-symbols-rounded" aria-hidden="true">save</span>Simpan konfigurasi</button>
        </section>

        <form id="featured-products-form" class="admin-featured-workspace" data-featured-form data-category-id="{{ $selectedCategory->id }}" data-api-url="{{ url('/api/v1/admin/product-categories/'.$selectedCategory->id.'/featured') }}">
            <section class="admin-featured-column">
                <header><div><h2>Produk tersedia</h2><p>Produk Published pada kategori {{ $selectedCategory->translations->firstWhere('lang', 'id')?->name }}.</p></div><span>{{ $products->count() }} produk</span></header>
                <label class="admin-featured-search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input type="search" placeholder="Cari nama produk atau kategori…" data-featured-search></label>
                <div class="admin-featured-available" data-featured-available-list>
                    @forelse ($products as $product)
                        @php
                            $translation = $product->translations->firstWhere('lang', 'id') ?? $product->translations->first();
                            $categoryName = $product->category?->translations?->firstWhere('lang', 'id')?->name ?? '';
                            $imageUrl = $product->media?->admin_preview_url;
                        @endphp
                        @if (! in_array($product->id, $selectedIds, true))
                            <article class="admin-featured-available-item" data-featured-available-item data-search-value="{{ mb_strtolower(($translation?->name ?? '').' '.$categoryName) }}">
                                @if ($imageUrl)<img src="{{ $imageUrl }}" alt="{{ $product->media?->alt_text_id ?? $translation?->name ?? '' }}" loading="lazy">@else<span class="admin-featured-image-placeholder"><span class="material-symbols-rounded" aria-hidden="true">image</span></span>@endif
                                <div><strong>{{ $translation?->name ?? 'Produk tanpa nama' }}</strong><span>{{ $categoryName }}</span></div>
                                <button type="button" class="admin-button admin-button--soft" data-featured-add data-product-id="{{ $product->id }}" data-product-name="{{ $translation?->name ?? 'Produk tanpa nama' }}" data-product-image="{{ $imageUrl ?? '' }}" @disabled(count($selectedIds) >= 6)><span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah</button>
                            </article>
                        @endif
                    @empty
                        <p class="admin-empty">Belum ada produk Published pada kategori ini.</p>
                    @endforelse
                    @if ($products->isNotEmpty())
                        <p class="admin-featured-empty-available" data-featured-empty-available @if (count($selectedIds) !== $products->count()) hidden @endif>Semua produk kategori sudah dipilih.</p>
                    @endif
                </div>
            </section>

            <section class="admin-featured-column admin-featured-column--selected">
                <header><div><h2>Urutan tampil publik</h2><p>Atur prioritas produk yang tampil pada landing page.</p></div><span><b data-featured-count>{{ count($selectedIds) }}</b> dari 6 slot</span></header>
                <ol class="admin-featured-selected" data-featured-selected-list>
                    @foreach ($selectedProducts as $index => $product)
                        @php
                            $translation = $product->translations->firstWhere('lang', 'id') ?? $product->translations->first();
                            $imageUrl = $product->media?->admin_preview_url;
                        @endphp
                        <li class="admin-featured-selected-item" data-featured-selected-item draggable="true">
                            <span class="admin-featured-drag material-symbols-rounded" aria-label="Geser untuk mengurutkan" title="Geser untuk mengurutkan" aria-hidden="true">drag_indicator</span>
                            <span class="admin-featured-rank">{{ $index + 1 }}</span>
                            @if ($imageUrl)<img src="{{ $imageUrl }}" alt="{{ $product->media?->alt_text_id ?? $translation?->name ?? '' }}" loading="lazy">@else<span class="admin-featured-image-placeholder"><span class="material-symbols-rounded" aria-hidden="true">image</span></span>@endif
                            <div><small>{{ $product->translations->firstWhere('lang', 'id')?->slug }}</small><strong>{{ $translation?->name ?? 'Produk tanpa nama' }}</strong></div>
                            <input type="hidden" name="product_ids[]" value="{{ $product->id }}">
                            <div class="admin-featured-item-actions"><button type="button" aria-label="Naikkan urutan" data-featured-move="up"><span class="material-symbols-rounded" aria-hidden="true">keyboard_arrow_up</span></button><button type="button" aria-label="Turunkan urutan" data-featured-move="down"><span class="material-symbols-rounded" aria-hidden="true">keyboard_arrow_down</span></button><button type="button" aria-label="Hapus dari unggulan" data-featured-remove><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></div>
                        </li>
                    @endforeach
                    @for ($slot = count($selectedIds) + 1; $slot <= 6; $slot++)
                        <li class="admin-featured-slot" data-featured-slot><span>{{ $slot }}</span><span class="material-symbols-rounded" aria-hidden="true">add_circle</span><span>Slot kosong #{{ $slot }} — pilih produk dari kolom kiri</span><small>Tersedia</small></li>
                    @endfor
                </ol>
                <p class="admin-featured-selected-empty" data-featured-selected-empty @if ($selectedProducts->isNotEmpty()) hidden @endif>Belum ada produk unggulan. Landing page akan menggunakan urutan katalog sebagai fallback.</p>
            </section>
            <div class="admin-form__error admin-featured-errors" data-featured-errors hidden></div>
        </form>
    @else
        <section class="admin-panel"><p class="admin-empty">Belum ada kategori pemeriksaan. Tambahkan kategori dan produk Published terlebih dahulu.</p></section>
    @endif
</section>
@endsection
