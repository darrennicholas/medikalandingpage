@extends('admin.layout')

@section('title', ($editingSubcategory || request()->has('create') || request()->has('category_id') ? ($editingSubcategory ? 'Ubah ' : 'Tambah ') : '').'Pengaturan SubKategori')
@section('page-heading', ($editingSubcategory || request()->has('create') || request()->has('category_id') ? ($editingSubcategory ? 'Ubah ' : 'Tambah ') : '').'Pengaturan SubKategori')
@section('breadcrumb', 'Konten Landing Page  /  Produk  /  Pengaturan SubKategori')
@section('page-description', 'Kelola subkategori berdasarkan kategori utama produk.')

@section('page-actions')
    @if (! ($editingSubcategory || request()->has('create') || request()->has('category_id')) && (auth()->user()->hasRole('superadmin') || auth()->user()->role?->hasPermission('products_categories', 'create')))
    <a class="admin-button admin-button--primary" href="{{ route('admin.subcategories.index', ['create' => 1]) }}#subcategory-form"><span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah Subkategori</a>
    @endif
@endsection

@section('content')
    @php
        $canCreate = auth()->user()->hasRole('superadmin') || auth()->user()->role?->hasPermission('products_categories', 'create');
        $canUpdate = auth()->user()->hasRole('superadmin') || auth()->user()->role?->hasPermission('products_categories', 'update');
        $canDelete = auth()->user()->hasRole('superadmin') || auth()->user()->role?->hasPermission('products_categories', 'delete');
    @endphp
    @if (! ($editingSubcategory || request()->has('create') || request()->has('category_id')))
    <section class="admin-management-stats admin-category-stats" aria-label="Ringkasan subkategori">
        <article class="admin-management-stat admin-summary-card"><span>Total Kategori</span><strong>{{ $subcategoryCategories->count() }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">category</span></article>
        <article class="admin-management-stat admin-management-stat--success admin-summary-card admin-summary-card--success"><span>Memiliki Subkategori</span><strong>{{ $subcategoryCategories->filter(fn ($category) => ($subcategories[$category->id] ?? collect())->contains(fn ($item) => filled($item->name) && strtolower(trim($item->name)) !== 'none'))->count() }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">check_circle</span></article>
        <article class="admin-management-stat admin-management-stat--muted admin-summary-card admin-summary-card--warning"><span>Belum Ada Subkategori</span><strong>{{ $subcategoryCategories->filter(fn ($category) => ! ($subcategories[$category->id] ?? collect())->contains(fn ($item) => filled($item->name) && strtolower(trim($item->name)) !== 'none'))->count() }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">pending</span></article>
    </section>
    @else
    <section class="admin-panel admin-category-editor admin-subcategory-editor" aria-labelledby="subcategory-heading">
        <div class="admin-panel__header"><div><h2 id="subcategory-heading">{{ $editingSubcategory ? 'Perbarui data' : 'Data baru' }}</h2></div><a class="admin-button admin-button--back" href="{{ route('admin.subcategories.index') }}"><span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>Kembali ke daftar</a></div>
        @if ($editingSubcategory ? $canUpdate : $canCreate)
            @if ($editingSubcategory && ($subcategories[$editingSubcategory->category_id] ?? collect())->count() > 1)
                <label class="admin-field admin-subcategory-editor__selector"><span>Pilih subkategori untuk diubah</span><select onchange="if (this.value) window.location.href = this.value">@foreach ($subcategories[$editingSubcategory->category_id] as $item)<option value="{{ route('admin.subcategories.index', ['edit' => $item->id]) }}" @selected($item->id == $editingSubcategory->id)>{{ $item->name }}</option>@endforeach</select></label>
            @endif
            <form id="subcategory-form" method="POST" action="{{ $editingSubcategory ? route('admin.subcategories.update', $editingSubcategory->id) : route('admin.subcategories.store') }}" class="admin-form admin-cms-form">
                @csrf
                @if ($editingSubcategory) @method('PUT') @endif
                <label class="admin-field"><span>Kategori utama</span><select name="category_id" required><option value="">Pilih kategori</option>@foreach ($subcategoryCategories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $editingSubcategory?->category_id ?? request('category_id')) == $category->id)>{{ $category->translations->firstWhere('lang', 'id')?->name ?: 'Kategori #'.$category->id }}</option>@endforeach</select></label>
                <label class="admin-field"><span>Nama subkategori</span><input name="name" spellcheck="false" autocomplete="off" maxlength="100" required value="{{ old('name', $editingSubcategory?->name) }}" placeholder="Contoh: Drugs of Abuse (DOA)"></label>
                <div class="admin-form__actions">
                    <a class="admin-button" href="{{ route('admin.subcategories.index') }}">Batal</a>
                    @if ($editingSubcategory && $canDelete)<button form="delete-subcategory" class="admin-button" type="submit">Hapus Subkategori</button>@endif
                    <button class="admin-button admin-button--primary" type="submit">{{ $editingSubcategory ? 'Simpan Perubahan' : 'Tambah Subkategori' }}</button>
                </div>
            </form>
            @if ($editingSubcategory && $canDelete)<form id="delete-subcategory" method="POST" action="{{ route('admin.subcategories.destroy', $editingSubcategory->id) }}" data-confirm="Hapus subkategori ini beserta seluruh produk di dalamnya?">@csrf @method('DELETE')</form>@endif
        @endif
    </section>
    @endif
    @if (! ($editingSubcategory || request()->has('create') || request()->has('category_id')))
    <section class="admin-panel" aria-labelledby="subcategory-table-heading">
        <div class="admin-panel__header"><h2 id="subcategory-table-heading">Daftar Subkategori</h2></div>
        <div class="admin-table-wrap">
            <table class="admin-table admin-category-table">
                <thead><tr><th>Aksi</th><th>Kategori Utama</th><th>Nama Subkategori</th><th>Jumlah Produk</th><th>Diperbarui</th></tr></thead>
                <tbody>
                    @forelse ($subcategoryCategories as $category)
                        @php($categoryName = $category->translations->firstWhere('lang', 'id')?->name ?: 'Kategori #'.$category->id)
                        @php($items = ($subcategories[$category->id] ?? collect())->filter(fn ($item) => filled($item->name) && strtolower(trim($item->name)) !== 'none'))
                        <tr>
                            <td><div class="admin-category-actions">
                                @if (($items->isNotEmpty() && $canUpdate) || ($items->isEmpty() && $canCreate))<a class="admin-icon-button" href="{{ $items->isNotEmpty() ? route('admin.subcategories.index', ['edit' => $items->first()->id]) : route('admin.subcategories.index', ['category_id' => $category->id]) }}" aria-label="Ubah subkategori {{ $categoryName }}" title="Ubah subkategori"><span class="material-symbols-rounded" aria-hidden="true">edit</span></a>@endif
                                @if ($canDelete)<button class="admin-icon-button is-danger" type="button" aria-label="Hapus kategori {{ $categoryName }}" title="Hapus kategori" data-delete-confirm="Hapus kategori ini beserta seluruh subkategori dan produk di dalamnya?" data-delete-url="{{ url('/api/v1/admin/product-categories/'.$category->id) }}"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button>@endif
                            </div>
                            </td>
                            <td><strong>{{ $categoryName }}</strong></td>
                            <td>
                                {{ $items->pluck('name')->implode(', ') ?: 'None' }}
                            </td>
                            <td><span class="admin-category-product-count"><span class="material-symbols-rounded" aria-hidden="true">inventory_2</span>{{ $category->products_count }}</span></td>
                            <td>{{ $category->updated_at?->format('d M Y, H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td class="admin-empty" colspan="5">Belum ada kategori. Tambahkan kategori utama melalui Pengaturan Kategori.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endif
@endsection
