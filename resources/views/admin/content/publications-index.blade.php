@extends('admin.layout')
@php $isDocs = $resource === 'documentations'; $pageTitle = $isDocs ? 'Dokumentasi' : 'Karir & Lowongan Kerja'; @endphp
@section('title', $pageTitle)
@section('page-heading', $pageTitle)
@section('page-actions')
    @if ($isDocs)
        <a class="admin-button" href="{{ route('admin.content.create', ['resource' => $resource, 'type' => 'activity']) }}"><span class="material-symbols-rounded">add</span>Tambah Berita / Foto Kegiatan</a>
        <a class="admin-button admin-button--primary" href="{{ route('admin.content.create', ['resource' => $resource, 'type' => 'article']) }}"><span class="material-symbols-rounded">add</span>Tambah Artikel</a>
    @else
        <a class="admin-button admin-button--primary" href="{{ route('admin.content.create', $resource) }}"><span class="material-symbols-rounded">add</span>Tambah Lowongan</a>
    @endif
@endsection
@section('content')
<section class="admin-product-page admin-publication-page">
    <div class="admin-product-stats admin-publication-stats">
        <article class="admin-summary-card"><span>{{ $isDocs ? 'Total Konten' : 'Total Lowongan' }}</span><strong>{{ $total }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">{{ $isDocs ? 'article' : 'work' }}</span></article>
        <article class="admin-summary-card admin-summary-card--success"><span>{{ $isDocs ? 'Artikel' : 'Lowongan Open' }}</span><strong>{{ $primaryCount }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">{{ $isDocs ? 'description' : 'work_history' }}</span></article>
        @if ($isDocs)<article class="admin-summary-card"><span>Published</span><strong>{{ $publishedCount }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">check_circle</span></article>@else<article class="admin-summary-card"><span>Akan Ditutup ≤ 7 Hari</span><strong>{{ $closingCount }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">schedule</span></article>@endif
        <article class="admin-summary-card admin-summary-card--warning"><span>{{ $isDocs ? 'Berita / Foto Kegiatan' : 'Lowongan Closed' }}</span><strong>{{ $secondaryCount }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">{{ $isDocs ? 'photo_library' : 'work_off' }}</span></article>
    </div>
    <section class="admin-panel admin-product-panel">
        <form class="admin-product-filters" method="GET">
            @if ($isDocs)<label>Tipe<select name="type"><option value="">Semua Tipe</option><option value="article" @selected(request('type') === 'article')>Artikel</option><option value="activity" @selected(request('type') === 'activity')>Berita / Foto Kegiatan</option></select></label>@else<label>Lokasi<input type="search" name="location" value="{{ request('location') }}" placeholder="Lokasi"></label>@endif
            <label>Status<select name="status"><option value="">Semua Status</option>@foreach ($isDocs ? ['published' => 'Published', 'draft' => 'Draft'] : ['open' => 'Open', 'closed' => 'Closed', 'draft' => 'Draft'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label class="admin-product-filters__search"><input aria-label="Cari judul" type="search" name="q" value="{{ request('q') }}" placeholder="Cari judul..."></label>
            <label>Baris<select name="per_page">@foreach ([10, 25, 50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>@endforeach</select></label>
            <div class="admin-filter-actions"><button class="admin-button admin-button--primary" type="submit">Cari</button><a class="admin-button" href="{{ route('admin.content.index', $resource) }}">Reset</a></div>
        </form>
        <div class="admin-table-wrap admin-product-table-wrap"><table class="admin-table admin-product-table admin-publication-table {{ $isDocs ? 'admin-publication-table--docs' : '' }}"><thead><tr><th>Aksi</th>@if ($isDocs)<th>Sampul</th>@endif<th>{{ $isDocs ? 'Judul' : 'Posisi' }}</th><th>Tipe</th>@if (! $isDocs)<th>Lokasi</th>@endif<th>{{ $isDocs ? 'Tanggal' : 'Tanggal Posting' }}</th>@if ($isDocs)<th>Galeri</th>@else<th>Batas Ditutup</th>@endif<th>Status</th></tr></thead><tbody>
        @forelse ($records as $record)
            @php $translation = $record->translations->firstWhere('lang', 'id') ?? $record->translations->first(); $nextStatus = $isDocs ? ($record->status === 'published' ? 'draft' : 'published') : ($record->status === 'open' ? 'closed' : 'open'); @endphp
            <tr><td><div class="admin-product-actions"><a href="{{ route('admin.content.edit', ['resource' => $resource, 'id' => $record->id]) }}" title="Ubah" aria-label="Ubah"><span class="material-symbols-rounded">edit</span></a><button type="button" title="Ubah status" aria-label="Ubah status" data-status-url="{{ url('/api/v1/admin/'.$resource.'/'.$record->id.'/status') }}" data-next-status="{{ $nextStatus }}"><span class="material-symbols-rounded">{{ in_array($record->status, ['published', 'open'], true) ? 'visibility_off' : 'visibility' }}</span></button><button type="button" class="is-danger" title="Hapus" aria-label="Hapus" data-delete-url="{{ url('/api/v1/admin/'.$resource.'/'.$record->id) }}"><span class="material-symbols-rounded">delete</span></button></div></td>
            @if ($isDocs)<td>@if ($record->cover?->admin_preview_url)<button class="admin-product-image" type="button" data-preview-url="{{ $record->cover->admin_preview_url }}"><img src="{{ $record->cover->admin_preview_url }}" alt="{{ $translation?->title }}"></button>@else<span class="admin-product-image admin-product-image--empty"><span class="material-symbols-rounded">image</span></span>@endif</td>@endif
            <td class="admin-product-name"><strong>{{ $translation?->title ?? 'Tanpa judul' }}</strong></td><td><span class="admin-publication-type">{{ $isDocs ? ($record->type === 'article' ? 'Artikel' : 'Berita / Foto Kegiatan') : $record->employment_type }}</span></td>@if (! $isDocs)<td>{{ $record->location }}</td>@endif<td>{{ ($isDocs ? $record->event_date : $record->posted_at)?->format('d M Y') ?? '—' }}</td><td>{{ $isDocs ? $record->gallery_count.' foto' : ($record->closing_date?->format('d M Y') ?? '—') }}</td><td><span class="admin-status admin-status--{{ $record->status }}">{{ ucfirst($record->status) }}</span></td></tr>
        @empty<tr><td colspan="8" class="admin-empty">Belum ada data yang sesuai.</td></tr>@endforelse
        </tbody></table></div>@include('admin.partials.pagination', ['paginator' => $records, 'label' => $isDocs ? 'dokumentasi' : 'lowongan'])
    </section>
</section>
@endsection
