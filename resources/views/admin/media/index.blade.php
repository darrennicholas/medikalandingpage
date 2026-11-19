@extends('admin.layout')
@section('title', 'Media Library')
@section('page-heading', 'Media Library')
@section('breadcrumb', 'CMS Landing  /  Media & SEO  /  Media Library')
@section('page-description', 'Kelola file gambar, video, dan teks alternatif landing page dari satu tempat.')
@section('page-actions')<button class="admin-button admin-button--primary" type="button" data-media-upload-trigger><span class="material-symbols-rounded" aria-hidden="true">cloud_upload</span>Unggah Media Baru</button>@endsection
@section('content')
<section class="admin-feature-stats admin-media-stats" aria-label="Ringkasan media">
    <article class="admin-summary-card"><span>Total File Media</span><strong>{{ number_format($mediaTotal) }}</strong><small>JPG, PNG, WebP, MP4, WebM</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">perm_media</span></article>
    <article class="admin-summary-card"><span>Kapasitas Terpakai</span><strong>{{ number_format($mediaBytes / 1024 / 1024, 1) }} MB</strong><small>Total ukuran file tersimpan</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">database</span></article>
    <article class="admin-summary-card admin-summary-card--success"><span>Dipakai di Halaman</span><strong>{{ number_format($linkedMediaCount) }}</strong><small>Aset terhubung ke konten</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">link</span></article>
    <article class="admin-summary-card admin-summary-card--warning"><span>Berkas Tanpa Relasi</span><strong>{{ number_format($unlinkedMediaCount) }}</strong><small>Belum terhubung ke konten</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">link_off</span></article>
</section>

<section class="admin-panel admin-media-upload-panel" data-media-upload-panel hidden>
    <div class="admin-panel__header"><div><p class="admin-eyebrow">Gambar maksimal 2 MB · Video maksimal 50 MB, khusus Hero / Banner</p><h2>Unggah Media Baru</h2></div><button class="admin-icon-link" type="button" data-media-upload-close aria-label="Tutup formulir"><span class="material-symbols-rounded" aria-hidden="true">close</span></button></div>
    <form class="admin-media-upload" data-media-upload data-api-url="{{ url('/api/v1/admin/media') }}" enctype="multipart/form-data"><input type="file" name="files[]" aria-label="Pilih gambar atau video untuk diunggah" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm" multiple required><button class="admin-button admin-button--primary" type="submit"><span class="material-symbols-rounded" aria-hidden="true">upload</span>Unggah File</button><div data-media-errors class="admin-form__error" hidden></div></form>
</section>

<section class="admin-panel admin-media-library-panel">
    <div class="admin-panel__header"><div><p class="admin-eyebrow">Aset visual website</p><h2>Perpustakaan Media</h2><p>Alt text adalah deskripsi untuk pembaca layar dan tidak ditampilkan sebagai tulisan pada gambar. Isi lalu klik Simpan Alt Text.</p></div><span class="admin-count-badge">{{ $mediaTotal }} file</span></div>
    <form class="admin-toolbar admin-media-toolbar" method="GET" action="{{ route('admin.media.index') }}"><label class="admin-toolbar__search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama file atau alt text..."></label><label>Section landing page<select name="section"><option value="">Semua Section</option>@foreach ($landingSections as $sectionKey => $sectionLabel)<option value="{{ $sectionKey }}" @selected(request('section') === $sectionKey)>{{ $sectionLabel }}</option>@endforeach</select></label><label>Format<select name="format"><option value="">Semua Format</option>@foreach (['image/jpeg' => 'JPG', 'image/png' => 'PNG', 'image/webp' => 'WebP', 'video/mp4' => 'MP4', 'video/webm' => 'WebM'] as $mime => $label)<option value="{{ $mime }}" @selected(request('format') === $mime)>{{ $label }}</option>@endforeach</select></label><div class="admin-filter-actions"><button class="admin-button admin-button--primary" type="submit">Cari</button><a class="admin-button" href="{{ route('admin.media.index') }}">Reset</a></div></form>
    <div class="admin-media-grid admin-media-grid--library">
        @forelse ($mediaItems as $media)
            <article class="admin-media-card admin-media-card--library" data-media-id="{{ $media->id }}" data-api-url="{{ url('/api/v1/admin/media/'.$media->id) }}">
                <div class="admin-media-card__image">@if (str_starts_with($media->mime_type, 'video/'))<video src="{{ $media->public_url }}" controls muted playsinline preload="metadata" aria-label="Pratinjau {{ $media->original_name }}"></video>@else<img src="{{ $media->public_url }}" alt="{{ $media->alt_text_id }}" width="{{ $media->width }}" height="{{ $media->height }}" loading="lazy">@endif</div>
                <div class="admin-media-card__name"><div><strong title="{{ $media->original_name }}">{{ $media->original_name }}</strong><span class="admin-media-format">{{ strtoupper(str_replace(['image/', 'video/'], '', $media->mime_type)) }}</span></div><small>{{ number_format($media->size_bytes / 1024, 0) }} KB@if ($media->width && $media->height) · {{ $media->width }} × {{ $media->height }} px@endif</small></div>
                <div class="admin-media-card__usage"><small>{{ $media->usage_sections ? 'Digunakan di: '.implode(', ', $media->usage_sections) : 'Belum digunakan pada konten CMS' }}</small>@if ($media->usage_details)<small>Penggunaan: {{ implode(', ', $media->usage_details) }}</small>@endif</div>
                <div class="admin-media-card__fields"><label>Alt Indonesia<input name="alt_text_id" maxlength="200" value="{{ $media->alt_text_id ?? '' }}" placeholder="Deskripsi gambar dalam Bahasa Indonesia"></label><label>Alt English<input name="alt_text_en" maxlength="200" value="{{ $media->alt_text_en ?? '' }}" placeholder="Image description in English"></label><label>Alt 中文<input name="alt_text_zh" maxlength="200" value="{{ $media->alt_text_zh ?? '' }}" placeholder="图片描述"></label></div>
                <div class="admin-media-card__actions"><button class="admin-button admin-button--soft" type="button" data-media-save><span class="material-symbols-rounded" aria-hidden="true">save</span>Simpan Alt Text</button><button class="admin-icon-link is-danger" type="button" data-media-delete aria-label="Hapus {{ $media->original_name }}" title="Hapus media"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></div>
            </article>
        @empty<p class="admin-empty">Belum ada file media. Unggah gambar atau video pertama untuk mulai menggunakan Media Library.</p>@endforelse
    </div>
    @include('admin.partials.pagination', ['paginator' => $mediaItems, 'label' => 'media'])
</section>
@endsection
