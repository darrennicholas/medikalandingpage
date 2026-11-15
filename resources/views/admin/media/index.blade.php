@extends('admin.layout')
@section('title', 'Media Library')
@section('page-heading', 'Media Library')
@section('breadcrumb', 'CMS Landing  /  Media & SEO  /  Media Library')
@section('page-description', 'Kelola file gambar landing page dan teks alternatif dari satu tempat.')
@section('page-actions')<button class="admin-button admin-button--primary" type="button" data-media-upload-trigger><span class="material-symbols-rounded" aria-hidden="true">cloud_upload</span>Unggah Media Baru</button>@endsection
@section('content')
<section class="admin-feature-stats admin-media-stats" aria-label="Ringkasan media">
    <article><span>Total File Media</span><strong>{{ number_format($mediaTotal) }}</strong><small>JPG, PNG, WebP</small></article>
    <article><span>Kapasitas Terpakai</span><strong>{{ number_format($mediaBytes / 1024 / 1024, 1) }} MB</strong><small>Total ukuran file tersimpan</small></article>
    <article class="is-green"><span>Dipakai di Halaman</span><strong>{{ number_format($linkedMediaCount) }}</strong><small>Aset terhubung ke konten</small></article>
    <article class="is-red"><span>Berkas Tanpa Relasi</span><strong>{{ number_format($unlinkedMediaCount) }}</strong><small>Belum terhubung ke konten</small></article>
</section>

<section class="admin-panel admin-media-upload-panel" data-media-upload-panel hidden>
    <div class="admin-panel__header"><div><p class="admin-eyebrow">JPG, PNG, WebP · Maksimal 2 MB per file</p><h2>Unggah Media Baru</h2></div><button class="admin-icon-link" type="button" data-media-upload-close aria-label="Tutup formulir"><span class="material-symbols-rounded" aria-hidden="true">close</span></button></div>
    <form class="admin-media-upload" data-media-upload data-api-url="{{ url('/api/v1/admin/media') }}" enctype="multipart/form-data"><input type="file" name="files[]" accept="image/jpeg,image/png,image/webp" multiple required><label>Alt text Indonesia <input name="alt_text_id[]" maxlength="200" placeholder="Deskripsikan gambar..."></label><label>Alt text English <input name="alt_text_en[]" maxlength="200" placeholder="Describe the image..."></label><button class="admin-button admin-button--primary" type="submit"><span class="material-symbols-rounded" aria-hidden="true">upload</span>Unggah File</button><div data-media-errors class="admin-form__error" hidden></div></form>
</section>

<section class="admin-panel admin-media-library-panel">
    <div class="admin-panel__header"><div><p class="admin-eyebrow">Aset visual website</p><h2>Perpustakaan Media</h2></div><span class="admin-count-badge">{{ $mediaTotal }} file</span></div>
    <form class="admin-toolbar admin-media-toolbar" method="GET" action="{{ route('admin.media.index') }}"><label class="admin-toolbar__search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama file atau alt text..."></label><label>Format<select name="format"><option value="">Semua Format</option>@foreach (['image/jpeg' => 'JPG', 'image/png' => 'PNG', 'image/webp' => 'WebP'] as $mime => $label)<option value="{{ $mime }}" @selected(request('format') === $mime)>{{ $label }}</option>@endforeach</select></label><button class="admin-button" type="submit">Cari</button>@if (request()->filled('q') || request()->filled('format'))<a class="admin-button" href="{{ route('admin.media.index') }}">Reset</a>@endif</form>
    <div class="admin-media-grid admin-media-grid--library">
        @forelse ($mediaItems as $media)
            <article class="admin-media-card admin-media-card--library" data-media-id="{{ $media->id }}" data-api-url="{{ url('/api/v1/admin/media/'.$media->id) }}">
                <div class="admin-media-card__image"><img src="{{ $media->public_url }}" alt="{{ $media->alt_text_id }}" width="{{ $media->width }}" height="{{ $media->height }}" loading="lazy"></div>
                <div class="admin-media-card__name"><div><strong title="{{ $media->original_name }}">{{ $media->original_name }}</strong><span class="admin-media-format">{{ strtoupper(str_replace('image/', '', $media->mime_type)) }}</span></div><small>{{ number_format($media->size_bytes / 1024, 0) }} KB · {{ $media->width }} × {{ $media->height }} px</small></div>
                <div class="admin-media-card__usage"><small>{{ $media->usage_labels ? 'Digunakan: '.implode(', ', $media->usage_labels) : 'Belum digunakan' }}</small></div>
                <div class="admin-media-card__fields"><label>Alt Indonesia<input name="alt_text_id" maxlength="200" value="{{ $media->alt_text_id }}" placeholder="Alt text Indonesia"></label><label>Alt English<input name="alt_text_en" maxlength="200" value="{{ $media->alt_text_en }}" placeholder="Alt text English"></label></div>
                <div class="admin-media-card__actions"><button class="admin-button admin-button--soft" type="button" data-media-save><span class="material-symbols-rounded" aria-hidden="true">save</span>Simpan Alt Text</button><button class="admin-icon-link is-danger" type="button" data-media-delete aria-label="Hapus {{ $media->original_name }}"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></div>
            </article>
        @empty<p class="admin-empty">Belum ada file media. Unggah gambar pertama untuk mulai menggunakan Media Library.</p>@endforelse
    </div>
    <div class="admin-media-pagination">
        <p>Showing {{ $mediaItems->firstItem() ?? 0 }} to {{ $mediaItems->lastItem() ?? 0 }} of {{ $mediaItems->total() }} results</p>
        <nav aria-label="Navigasi Media Library">
            @if ($mediaItems->previousPageUrl())
                <a href="{{ $mediaItems->previousPageUrl() }}">« Previous</a>
            @else
                <span aria-disabled="true">« Previous</span>
            @endif
            @if ($mediaItems->nextPageUrl())
                <a href="{{ $mediaItems->nextPageUrl() }}">Next »</a>
            @else
                <span aria-disabled="true">Next »</span>
            @endif
        </nav>
    </div>
</section>
@endsection
