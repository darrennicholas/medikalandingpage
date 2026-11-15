@extends('admin.layout')
@section('title', 'Brand Kami')
@section('page-heading', 'Brand Kami')
@section('breadcrumb', 'Konten Landing Page  /  Beranda  /  Brand Kami')
@section('page-description', 'Kelola logo brand, partner, dan principal mitra yang ditampilkan pada halaman beranda.')
@section('page-actions')
    <a class="admin-button admin-button--primary" href="{{ route('admin.content.create', 'brands') }}"><span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah Brand</a>
@endsection
@section('content')
    @php
        $brandTotal = array_sum($brandStats);
        $brandPublished = $brandStats['published'] ?? 0;
        $brandInactive = ($brandStats['draft'] ?? 0) + ($brandStats['unpublished'] ?? 0);
    @endphp

    <section class="admin-management-stats" aria-label="Ringkasan brand">
        <article class="admin-management-stat"><span>Total Brand</span><strong>{{ $brandTotal }}</strong><a href="{{ route('admin.content.index', 'brands') }}">Semua Brand <span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span></a></article>
        <article class="admin-management-stat admin-management-stat--success"><span>Brand Published</span><strong>{{ $brandPublished }}</strong><span class="admin-management-stat__note">Tampil di beranda</span></article>
        <article class="admin-management-stat admin-management-stat--muted"><span>Draft / Unpublished</span><strong>{{ $brandInactive }}</strong><span class="admin-management-stat__note">Belum tampil di beranda</span></article>
    </section>

    <section class="admin-panel admin-brand-manager">
        <form class="admin-brand-manager__filters" method="GET" action="{{ route('admin.content.index', 'brands') }}">
            <label for="brand-status-filter">Filter Status
                <select id="brand-status-filter" name="status">
                    <option value="">Semua Status</option>
                    @foreach (['published' => 'Published', 'draft' => 'Draft', 'unpublished' => 'Unpublished'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="admin-brand-manager__search" for="brand-search">Cari Brand
                <span class="admin-brand-manager__search-control"><span class="material-symbols-rounded" aria-hidden="true">search</span><input id="brand-search" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama brand..."></span>
            </label>
            <label>Baris <select name="per_page">@foreach ([10, 25, 50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>@endforeach</select></label>
            <button class="admin-button" type="submit"><span class="material-symbols-rounded" aria-hidden="true">filter_alt</span>Terapkan</button>
        </form>

        <div class="admin-table-wrap" data-reorder-list="{{ implode(',', $brandIds) }}" data-reorder-resource="brands">
            <table class="admin-table admin-brand-table">
                <thead><tr><th>Aksi</th><th>Urutan</th><th>Logo</th><th>Nama Brand</th><th>Link Website</th><th>Status</th><th>Dibuat / Diperbarui</th></tr></thead>
                <tbody>
                    @forelse ($records as $record)
                        @php
                            $statusClass = $record->status === 'published' ? 'active' : ($record->status === 'draft' ? 'draft' : 'inactive');
                            $statusLabel = $record->status === 'published' ? 'Active' : ucfirst($record->status);
                        @endphp
                        <tr data-reorder-id="{{ $record->id }}">
                            <td><div class="admin-brand-table__actions">
                                <a class="admin-icon-button" href="{{ route('admin.content.edit', ['resource' => 'brands', 'id' => $record->id]) }}" aria-label="Edit {{ $record->name }}" title="Edit"><span class="material-symbols-rounded" aria-hidden="true">edit</span></a>
                                <button class="admin-icon-button" type="button" aria-label="Ubah status {{ $record->name }}" title="Ubah status" data-status-url="{{ url('/api/v1/admin/brands/'.$record->id.'/status') }}" data-next-status="{{ $record->status === 'published' ? 'unpublished' : 'published' }}"><span class="material-symbols-rounded" aria-hidden="true">{{ $record->status === 'published' ? 'toggle_on' : 'toggle_off' }}</span></button>
                                <button class="admin-icon-button is-danger" type="button" aria-label="Hapus {{ $record->name }}" title="Hapus" data-delete-url="{{ url('/api/v1/admin/brands/'.$record->id) }}"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button>
                            </div></td>
                            <td>@include('admin.partials.order-controls', ['orderIds' => $brandIds])</td>
                            <td><span class="admin-brand-logo">@if ($record->logo)<img src="{{ $record->logo->admin_preview_url }}" alt="Logo {{ $record->name }}">@else<span class="material-symbols-rounded" aria-hidden="true">image</span>@endif</span></td>
                            <td><strong>{{ $record->name }}</strong></td>
                            <td>@if ($record->website_url)<a class="admin-brand-link" href="{{ $record->website_url }}" target="_blank" rel="noopener noreferrer">{{ $record->website_url }} <span class="material-symbols-rounded" aria-hidden="true">open_in_new</span></a>@else<span class="admin-muted-text">—</span>@endif</td>
                            <td><span class="admin-status admin-status--{{ $statusClass }}"><i></i>{{ $statusLabel }}</span></td>
                            <td><span class="admin-brand-dates"><strong>Dibuat</strong> {{ $record->created_at?->format('d/m/Y H:i') ?? '—' }}<small><strong>Diperbarui</strong> {{ $record->updated_at?->format('d/m/Y H:i') ?? '—' }}</small></span></td>
                        </tr>
                    @empty
                        <tr><td class="admin-empty" colspan="7">Belum ada brand yang sesuai dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $records, 'label' => 'brand'])
    </section>
@endsection
