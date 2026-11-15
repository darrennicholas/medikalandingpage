@extends('admin.layout')
@php($hasPendingDraft = is_array($record->draft_payload))
@section('title', 'Tentang Perusahaan')
@section('page-heading', 'Tentang Perusahaan')
@section('breadcrumb', 'Konten Landing Page  /  Tentang  /  Tentang Perusahaan')
@section('page-description', 'Kelola narasi profil perusahaan dan foto utama yang ditampilkan pada halaman Tentang.')
@section('page-actions')
    <a class="admin-button" href="{{ $record->exists ? route('admin.content.preview', ['resource' => 'content-blocks', 'id' => 'about_company']) : url('/id/tentang') }}" target="_blank" rel="noopener"><span class="material-symbols-rounded" aria-hidden="true">visibility</span>Pratinjau halaman</a>
@endsection
@section('content')
    @if ($hasPendingDraft)<div class="admin-alert admin-alert--info"><span class="material-symbols-rounded" aria-hidden="true">info</span><span>Draft tersimpan. Versi Published tetap tampil hingga perubahan dipublikasikan.</span></div>@endif

    <form class="admin-editor admin-editor--block admin-company-editor" novalidate data-admin-form data-live-editor="coverage" data-preview-title-fallback="Profil perusahaan" data-preview-body-fallback="Narasi profil perusahaan akan ditampilkan di sini." data-resource="content-blocks" data-api-url="{{ $apiUrl }}" data-is-editing="{{ $record->exists ? '1' : '0' }}" data-redirect-url="{{ $redirectUrl }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="block_key" value="about_company">
        <input type="hidden" name="status" value="{{ $hasPendingDraft ? 'draft' : ($record->status ?? 'draft') }}" data-editor-status>

        <div class="admin-editor__main">
            <section class="admin-panel admin-company-editor__panel">
                <div class="admin-panel__header"><div><p class="admin-eyebrow">Tentang</p><h2>Profil Perusahaan</h2></div><span class="admin-status admin-status--{{ $hasPendingDraft ? 'draft' : ($record->status ?? 'draft') }}" data-editor-status-label>{{ $hasPendingDraft ? 'Draft perubahan' : (($record->status ?? 'draft') === 'published' ? 'Published' : 'Draft') }}</span></div>
                <div class="admin-editor__language" role="tablist" aria-label="Bahasa konten"><button type="button" class="is-active" data-language-tab="id">Bahasa Indonesia <span>*</span></button><button type="button" data-language-tab="en">English</button></div>
                @foreach (['id' => ['Bahasa Indonesia', $translationId], 'en' => ['English', $translationEn]] as $language => [$languageLabel, $translation])
                    <div class="admin-language-panel {{ $language === 'en' ? 'is-hidden' : '' }}" data-language-panel="{{ $language }}">
                        <div class="admin-editor__fields admin-company-editor__fields">
                            <label class="admin-field"><span>{{ $language === 'id' ? 'Judul Tentang Perusahaan' : 'Company Profile Title' }} <b>*</b></span><input type="text" name="translations[{{ $language }}][title]" value="{{ $translation?->title }}" maxlength="120" data-preview-field="title" @required($language === 'id')><small>Judul utama profil perusahaan, maksimal 120 karakter.</small></label>
                            <label class="admin-field"><span>{{ $language === 'id' ? 'Subjudul' : 'Subtitle' }}</span><input type="text" name="translations[{{ $language }}][subtitle]" value="{{ $translation?->subtitle }}" maxlength="300"><small>Ringkasan singkat yang mendukung judul.</small></label>
                            <label class="admin-field"><span>{{ $language === 'id' ? 'Isi Profil Perusahaan' : 'Company Profile Content' }} <b>*</b></span><textarea name="translations[{{ $language }}][body]" maxlength="3000" rows="10" data-preview-field="body" @required($language === 'id')>{{ $translation?->body }}</textarea><small>Isi akan ditampilkan sebagai narasi profil pada halaman Tentang.</small></label>
                        </div>
                    </div>
                @endforeach

                <section class="admin-company-media">
                    <div><h3>Gambar Utama / Foto Profil Perusahaan</h3><p>Gunakan gambar landscape yang membantu pengunjung mengenali perusahaan.</p></div>
                    <label class="admin-field"><span>Pilih gambar dari Media Library</span><select name="media_id" data-block-media><option value="">Tanpa gambar</option>@foreach ($mediaOptions as $media)<option value="{{ $media->id }}" data-image-url="{{ $media->admin_preview_url }}" @selected((int) $record->media_id === $media->id)>{{ $media->original_name }} · {{ number_format($media->size_bytes / 1024, 0) }} KB</option>@endforeach</select><small>Untuk mengunggah gambar baru, gunakan <a href="{{ route('admin.media.index') }}">Media Library</a>.</small></label>
                    @if ($record->media)<p class="admin-company-media__alt"><strong>Alt text saat ini:</strong> {{ $record->media->alt_text_id ?: 'Belum diisi' }}. Alt text dapat diperbarui di Media Library.</p>@endif
                </section>

                <div class="admin-editor__publish"><div><strong>Status Publikasi <b>*</b></strong><small>Konten Published tampil pada halaman Tentang publik.</small></div><div class="admin-editor__status-options"><button type="button" data-set-status="published" class="{{ ! $hasPendingDraft && ($record->status ?? 'draft') === 'published' ? 'is-selected' : '' }}"><span class="admin-status-dot admin-status-dot--published"></span><span><b>Published (Tayang)</b><small>Pengunjung dapat melihat perubahan.</small></span></button><button type="button" data-set-status="draft" class="{{ $hasPendingDraft || ($record->status ?? 'draft') !== 'published' ? 'is-selected' : '' }}"><span class="admin-status-dot admin-status-dot--draft"></span><span><b>Draft Internal</b><small>Simpan tanpa mengubah konten publik.</small></span></button></div></div>
                <div class="admin-form__error" data-form-errors hidden></div>
                <div class="admin-editor__actions"><a class="admin-button" href="{{ route('admin.content.index', ['resource' => 'content-blocks', 'section' => 'about_company']) }}">Batal</a><button class="admin-button" type="submit" data-save-status="draft">Simpan Draft</button><button class="admin-button admin-button--primary" type="submit" data-save-status="published"><span class="material-symbols-rounded" aria-hidden="true">publish</span>Simpan &amp; Publish</button></div>
            </section>
        </div>

        <aside class="admin-editor__aside">
            <section class="admin-panel admin-editor-preview"><div class="admin-panel__header"><div><p class="admin-eyebrow">Pratinjau</p><h2>Halaman Tentang</h2></div><div class="admin-preview-switch"><button type="button" class="is-active" data-preview-device="desktop" aria-label="Pratinjau desktop"><span class="material-symbols-rounded" aria-hidden="true">desktop_windows</span></button><button type="button" data-preview-device="mobile" aria-label="Pratinjau ponsel"><span class="material-symbols-rounded" aria-hidden="true">smartphone</span></button></div></div><div class="admin-coverage-preview admin-company-preview"><span class="admin-coverage-preview__tag">TENTANG PERUSAHAAN</span><h3 data-preview-output="title">{{ $translationId?->title ?: 'Profil perusahaan' }}</h3><p data-preview-output="body">{{ $translationId?->body ?: 'Narasi profil perusahaan akan ditampilkan di sini.' }}</p><div class="admin-coverage-preview__image" data-block-image-preview data-empty-text="Pilih gambar profil perusahaan">@if ($mediaUrl)<img src="{{ $mediaUrl }}" alt="{{ $record->media?->alt_text_id ?? '' }}" data-preview-image>@else<span>Pilih gambar profil perusahaan</span>@endif</div></div><p class="admin-editor-preview__note">Preview merespons perubahan teks, media, dan ukuran layar.</p></section>
            <section class="admin-panel admin-company-sidecard"><h2>Visibilitas Publik</h2><p><span class="admin-status-dot admin-status-dot--{{ ($record->status ?? 'draft') === 'published' ? 'published' : 'draft' }}"></span>{{ ($record->status ?? 'draft') === 'published' ? 'Published (Tayang Aktif)' : 'Draft Internal' }}</p><a class="admin-button" href="{{ url('/id/tentang') }}" target="_blank" rel="noopener"><span class="material-symbols-rounded" aria-hidden="true">open_in_new</span>Lihat halaman publik</a></section>
            <section class="admin-panel admin-editor-audit"><div class="admin-panel__header"><div><p class="admin-eyebrow">Riwayat &amp; Audit</p><h2>Informasi Konten</h2></div></div><dl><div><dt>Dibuat</dt><dd>{{ $record->created_at?->format('d M Y, H:i') ?? 'Belum tersimpan' }}</dd></div><div><dt>Diperbarui</dt><dd>{{ $record->updated_at?->format('d M Y, H:i') ?? 'Belum tersimpan' }}</dd></div><div><dt>Status saat ini</dt><dd>{{ ($record->status ?? 'draft') === 'published' ? 'Published' : 'Draft' }}</dd></div></dl></section>
            <section class="admin-panel admin-company-sidecard"><h2>Pengaturan SEO</h2><p>Judul dan deskripsi mesin pencari dikelola terpisah agar metadata halaman tetap konsisten.</p><a class="admin-button" href="{{ route('admin.content.index', 'seo-metas') }}"><span class="material-symbols-rounded" aria-hidden="true">manage_search</span>Buka Pengaturan SEO</a></section>
        </aside>
    </form>
@endsection
