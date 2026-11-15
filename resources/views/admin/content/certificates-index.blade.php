@extends('admin.layout')
@php
    $certificateTotal = array_sum($certificateStats);
    $certificatePublished = $certificateStats['published'] ?? 0;
    $certificateInactive = ($certificateStats['draft'] ?? 0) + ($certificateStats['unpublished'] ?? 0);
@endphp
@section('title', 'Sertifikat & Penghargaan')
@section('page-heading', 'Sertifikat & Penghargaan')
@section('breadcrumb', 'Konten Landing Page  /  Tentang  /  Sertifikat')
@section('page-actions')
    <a class="admin-button admin-button--primary" href="{{ route('admin.content.create', 'certificates') }}"><span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah Sertifikat</a>
@endsection
@section('content')
    <section class="admin-management-stats admin-certificate-stats" aria-label="Ringkasan sertifikat">
        <article class="admin-management-stat"><span>Total Sertifikat</span><strong>{{ $certificateTotal }}</strong><span class="admin-management-stat__note">Semua data</span></article>
        <article class="admin-management-stat admin-management-stat--success"><span>Sertifikat Aktif</span><strong>{{ $certificatePublished }}</strong><span class="admin-management-stat__note">Tayang di halaman publik</span></article>
        <article class="admin-management-stat admin-management-stat--muted"><span>Draft / Nonaktif</span><strong>{{ $certificateInactive }}</strong><span class="admin-management-stat__note">Belum ditampilkan</span></article>
    </section>

    <section class="admin-panel admin-certificate-list">
        <form class="admin-certificate-filters" method="GET" action="{{ route('admin.content.index', 'certificates') }}">
            <label>Status<select name="status"><option value="">Semua Status</option>@foreach (['published' => 'Published', 'draft' => 'Draft', 'unpublished' => 'Unpublish'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label>Tahun<select name="year"><option value="">Semua Tahun</option>@foreach ($certificateYears as $year)<option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>@endforeach</select></label>
            <label class="admin-certificate-search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama sertifikat..."></label>
            <button class="admin-button" type="submit">Filter</button>
        </form>
        <div class="admin-table-wrap" data-reorder-list="{{ implode(',', $certificateIds) }}" data-reorder-resource="certificates"><table class="admin-table admin-certificate-table"><thead><tr><th>Aksi</th><th>Urutan</th><th>Dokumen</th><th>Nama Sertifikat / Penghargaan</th><th>Tahun</th><th>Status</th><th>Diperbarui</th></tr></thead><tbody>
            @forelse ($records as $record)
                @php($translation = $record->translations->firstWhere('lang', 'id') ?? $record->translations->first())
                <tr data-reorder-id="{{ $record->id }}">
                    <td><div class="admin-brand-table__actions"><a class="admin-icon-button" href="{{ route('admin.content.edit', ['resource' => 'certificates', 'id' => $record->id]) }}" aria-label="Ubah sertifikat" title="Ubah"><span class="material-symbols-rounded" aria-hidden="true">edit</span></a><button class="admin-icon-button" type="button" aria-label="Ubah status sertifikat" title="Ubah status" data-status-url="{{ url('/api/v1/admin/certificates/'.$record->id.'/status') }}" data-next-status="{{ $record->status === 'published' ? 'unpublished' : 'published' }}"><span class="material-symbols-rounded" aria-hidden="true">{{ $record->status === 'published' ? 'visibility_off' : 'check_circle' }}</span></button><button class="admin-icon-button is-danger" type="button" aria-label="Hapus sertifikat" title="Hapus" data-delete-url="{{ url('/api/v1/admin/certificates/'.$record->id) }}"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></div></td>
                    <td>@include('admin.partials.order-controls', ['orderIds' => $certificateIds])</td>
                    <td>@if ($record->media?->admin_preview_url)<button class="admin-certificate-thumb" type="button" data-preview-url="{{ $record->media->admin_preview_url }}" aria-label="Lihat dokumen {{ $translation?->title ?? 'sertifikat' }}"><img src="{{ $record->media->admin_preview_url }}" alt="{{ $record->media->alt_text_id ?: $translation?->title }}" loading="lazy"></button>@else<span class="admin-certificate-thumb admin-certificate-thumb--empty">—</span>@endif</td>
                    <td><strong>{{ $translation?->title ?? 'Tanpa judul' }}</strong>@if ($translation?->description)<small>{{ $translation->description }}</small>@endif</td>
                    <td><span class="admin-certificate-year">{{ $record->issued_year ?: '—' }}</span></td>
                    <td><span class="admin-status admin-status--{{ $record->status === 'published' ? 'published' : ($record->status === 'draft' ? 'draft' : 'unpublished') }}">{{ ucfirst($record->status) }}</span></td>
                    <td class="admin-certificate-date">{{ $record->updated_at?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="admin-empty">Belum ada sertifikat yang sesuai filter.</td></tr>
            @endforelse
        </tbody></table></div>
        @include('admin.partials.pagination', ['paginator' => $records, 'label' => 'sertifikat'])
    </section>
@endsection
