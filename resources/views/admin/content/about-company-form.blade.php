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
        @include('admin.partials.record-version', ['record' => $record])
        @csrf
        <input type="hidden" name="block_key" value="about_company">
        <input type="hidden" name="status" value="{{ $hasPendingDraft ? 'draft' : ($record->status ?? 'draft') }}" data-editor-status>

        <div class="admin-editor__main">
            <section class="admin-panel admin-company-editor__panel">
                <div class="admin-panel__header"><div><p class="admin-eyebrow">Tentang</p><h2>Profil Perusahaan</h2></div><span class="admin-status admin-status--{{ $hasPendingDraft ? 'draft' : ($record->status ?? 'draft') }}" data-editor-status-label>{{ $hasPendingDraft ? 'Draft perubahan' : (($record->status ?? 'draft') === 'published' ? 'Published' : 'Draft') }}</span></div>
                <div class="admin-editor__language" role="tablist" aria-label="Bahasa konten"><button type="button" class="is-active" data-language-tab="id">Bahasa Indonesia <span>*</span></button><button type="button" data-language-tab="en">English</button><button type="button" data-language-tab="zh">中文</button></div>
                @foreach (['id' => ['Bahasa Indonesia', $translationId], 'en' => ['English', $translationEn], 'zh' => ['中文', $translationZh]] as $language => [$languageLabel, $translation])
                    <div class="admin-language-panel {{ $language !== 'id' ? 'is-hidden' : '' }}" data-language-panel="{{ $language }}">
                        <div class="admin-editor__fields admin-company-editor__fields">
                            <label class="admin-field"><span>{{ $language === 'id' ? 'Judul Tentang Perusahaan' : 'Company Profile Title' }} <b>*</b></span><input type="text" name="translations[{{ $language }}][title]" value="{{ $translation?->title }}" maxlength="120" data-preview-field="title" @required($language === 'id')><small>Judul utama profil perusahaan, maksimal 120 karakter.</small></label>
                            <label class="admin-field admin-company-subtitle-field"><span>{{ $language === 'id' ? 'Subjudul' : 'Subtitle' }}</span><textarea name="translations[{{ $language }}][subtitle]" maxlength="300" rows="3" data-auto-resize data-preview-field="subtitle" placeholder="{{ $language === 'id' ? 'Tulis subjudul singkat untuk profil perusahaan' : 'Write a short subtitle for the company profile' }}">{{ old("translations.$language.subtitle", $translation?->subtitle) }}</textarea><small>{{ $language === 'id' ? 'Maksimal 300 karakter. Field akan mengikuti panjang teks.' : 'Maximum 300 characters. The field expands to fit the text.' }}</small></label>
                            <div class="admin-field admin-field--rich" data-rich-field>
                                <span>{{ $language === 'id' ? 'Isi Profil Perusahaan' : 'Company Profile Content' }} @if ($language === 'id')<b>*</b>@endif</span>
                                <div class="admin-rich-editor" data-rich-editor data-placeholder="{{ $language === 'id' ? 'Tulis narasi profil perusahaan...' : 'Write the company profile...' }}">
                                    <textarea name="translations[{{ $language }}][body]" maxlength="3000" rows="8" data-preview-field="body" placeholder="{{ $language === 'id' ? 'Tulis narasi profil dengan teks biasa. Gunakan baris kosong untuk memisahkan paragraf.' : 'Write the company profile in plain text. Use a blank line to separate paragraphs.' }}" @required($language === 'id') @if ($language === 'id') data-rich-required @endif>{{ old("translations.$language.body", $translation?->body) }}</textarea>
                                </div>
                                <small>{{ $language === 'id' ? 'Maksimal 3.000 karakter. Teks baru dimulai normal; pilih teks lalu gunakan tombol format bila diperlukan.' : 'Maximum 3,000 characters. New text is plain; select text and use formatting when needed.' }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach

                <section class="admin-company-media">
                    <div><h3>Gambar Utama / Foto Profil Perusahaan</h3><p>Gunakan gambar landscape yang membantu pengunjung mengenali perusahaan.</p></div>
                    <label class="admin-field"><span>Pilih gambar dari Media Library</span><select name="media_id" data-block-media><option value="">Tanpa gambar</option>@foreach ($mediaOptions as $media)<option value="{{ $media->id }}" data-image-url="{{ $media->admin_preview_url }}" @selected((int) $record->media_id === $media->id)>{{ $media->original_name }} · {{ number_format($media->size_bytes / 1024, 0) }} KB</option>@endforeach</select><small>Pilih gambar yang sudah ada atau upload gambar baru di sini.</small></label>
                </section>

                <div class="admin-editor__publish"><div><strong>Status Publikasi <b>*</b></strong><small>Konten Published tampil pada halaman Tentang publik.</small></div><div class="admin-editor__status-options"><button type="button" data-set-status="published" class="{{ ! $hasPendingDraft && ($record->status ?? 'draft') === 'published' ? 'is-selected' : '' }}"><span class="admin-status-dot admin-status-dot--published"></span><span><b>Published (Tayang)</b><small>Pengunjung dapat melihat perubahan.</small></span></button><button type="button" data-set-status="draft" class="{{ $hasPendingDraft || ($record->status ?? 'draft') !== 'published' ? 'is-selected' : '' }}"><span class="admin-status-dot admin-status-dot--draft"></span><span><b>Draft</b><small>Simpan tanpa menampilkan perubahan.</small></span></button></div></div>
                <div class="admin-form__error" data-form-errors hidden></div>
                <div class="admin-editor__actions"><a class="admin-button" href="{{ route('admin.content.index', ['resource' => 'content-blocks', 'section' => 'about_company']) }}">Batal</a><button class="admin-button" type="submit" data-save-status="draft" formnovalidate>Simpan Draft</button><button class="admin-button admin-button--primary" type="submit" data-save-status="published"><span class="material-symbols-rounded" aria-hidden="true">publish</span>Simpan &amp; Publish</button></div>
            </section>
        </div>

        <aside class="admin-editor__aside">
            <section class="admin-panel admin-editor-preview"><div class="admin-panel__header"><div><p class="admin-eyebrow">Pratinjau</p><h2>Halaman Tentang</h2></div><div class="admin-preview-switch"><button type="button" class="is-active" data-preview-device="desktop" aria-label="Pratinjau desktop"><span class="material-symbols-rounded" aria-hidden="true">desktop_windows</span></button><button type="button" data-preview-device="mobile" aria-label="Pratinjau ponsel"><span class="material-symbols-rounded" aria-hidden="true">smartphone</span></button></div></div><div class="admin-coverage-preview admin-company-preview"><span class="admin-coverage-preview__tag">TENTANG PERUSAHAAN</span><h3 data-preview-output="title">{{ $translationId?->title ?: 'Profil perusahaan' }}</h3><div class="admin-company-preview__subtitle" data-preview-output="subtitle">{{ $translationId?->subtitle }}</div><div class="admin-company-preview__copy" data-preview-output="body">{{ $translationBodyId ?: 'Narasi profil perusahaan akan ditampilkan di sini.' }}</div><div class="admin-coverage-preview__image" data-block-image-preview data-empty-text="Pilih gambar profil perusahaan">@if ($mediaUrl)<img src="{{ $mediaUrl }}" alt="{{ $record->media?->alt_text_id ?? '' }}" data-preview-image>@else<span>Pilih gambar profil perusahaan</span>@endif</div></div><p class="admin-editor-preview__note">Preview merespons perubahan teks, media, dan ukuran layar.</p></section>
            <section class="admin-panel admin-company-sidecard"><h2>Visibilitas Publik</h2><p><span class="admin-status-dot admin-status-dot--{{ ($record->status ?? 'draft') === 'published' ? 'published' : 'draft' }}"></span>{{ ($record->status ?? 'draft') === 'published' ? 'Published (Tayang Aktif)' : 'Draft Internal' }}</p><a class="admin-button" href="{{ url('/id/tentang') }}" target="_blank" rel="noopener"><span class="material-symbols-rounded" aria-hidden="true">open_in_new</span>Lihat halaman publik</a></section>
            <section class="admin-panel admin-editor-audit"><div class="admin-panel__header"><div><p class="admin-eyebrow">Riwayat &amp; Audit</p><h2>Informasi Konten</h2></div></div><dl><div><dt>Dibuat</dt><dd>{{ $record->created_at?->format('d M Y, H:i') ?? 'Belum tersimpan' }}</dd></div><div><dt>Diperbarui</dt><dd>{{ $record->updated_at?->format('d M Y, H:i') ?? 'Belum tersimpan' }}</dd></div><div><dt>Status saat ini</dt><dd>{{ ($record->status ?? 'draft') === 'published' ? 'Published' : 'Draft' }}</dd></div></dl></section>
            <section class="admin-panel admin-company-sidecard"><h2>Pengaturan SEO</h2><p>Judul dan deskripsi mesin pencari dikelola terpisah agar metadata halaman tetap konsisten.</p><a class="admin-button" href="{{ route('admin.content.index', 'seo-metas') }}"><span class="material-symbols-rounded" aria-hidden="true">manage_search</span>Buka Pengaturan SEO</a></section>
        </aside>
    </form>
@endsection
