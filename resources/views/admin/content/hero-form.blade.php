@extends('admin.layout')
@php($hasPendingDraft = is_array($record->draft_payload))
@php($translationId = $translationId ?? null)
@section('title', ($isEditing ? 'Ubah Banner' : 'Tambah Banner').' | Hero / Banner')
@section('page-heading', $isEditing ? 'Ubah Banner' : 'Tambah Banner')
@section('breadcrumb', 'Konten Landing Page  /  Beranda  /  Hero / Banner  /  '.($isEditing ? 'Ubah Banner' : 'Tambah Banner'))
@section('page-description', 'Atur gambar, teks, tombol, dan status publikasi banner beranda.')
@section('page-actions')
    @if ($isEditing)<a class="admin-button" href="{{ route('admin.content.show', ['resource' => 'hero-slides', 'id' => $record->id]) }}"><span class="material-symbols-rounded" aria-hidden="true">visibility</span>Lihat detail</a>@endif
    @if ($isEditing)<a class="admin-button" href="{{ route('admin.content.preview', ['resource' => 'hero-slides', 'id' => $record->id]) }}" target="_blank" rel="noopener">Pratinjau Landing Page</a>@endif
@endsection
@section('content')
    @if ($hasPendingDraft)<div class="admin-alert admin-alert--info"><span class="material-symbols-rounded" aria-hidden="true">info</span><span>Draft perubahan tersimpan. Banner yang sedang tayang tidak berubah sampai Anda memilih <strong>Simpan &amp; Publish</strong>.</span></div>@endif
    <form class="admin-editor" novalidate data-admin-form data-live-editor="hero" data-resource="hero-slides" data-api-url="{{ $apiUrl }}" data-is-editing="{{ $isEditing ? '1' : '0' }}" data-redirect-url="{{ $redirectUrl }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="status" value="{{ $record->status ?? 'draft' }}" data-editor-status>
        <div class="admin-editor__main">
            <section class="admin-panel admin-editor__panel">
                <div class="admin-panel__header"><div><p class="admin-eyebrow">Hero / Banner</p><h2>Informasi Banner</h2></div><span class="admin-status admin-status--{{ $hasPendingDraft ? 'draft' : ($record->status ?? 'draft') }}" data-editor-status-label>{{ $hasPendingDraft ? 'Draft perubahan' : ucfirst($record->status ?? 'draft') }}</span></div>
                <div class="admin-editor__language" role="tablist" aria-label="Bahasa banner"><button type="button" class="is-active" data-language-tab="id">Indonesia <span>*</span></button><button type="button" data-language-tab="en">English</button></div>

                @foreach (['id' => 'Bahasa Indonesia', 'en' => 'English'] as $language => $languageLabel)
                    @php($translation = $language === 'id' ? $translationId : $translationEn)
                    <div class="admin-language-panel {{ $language === 'en' ? 'is-hidden' : '' }}" data-language-panel="{{ $language }}">
                        <div class="admin-editor__fields">
                            <label class="admin-field"><span>{{ $language === 'id' ? 'Judul Banner' : 'Banner Title' }} <b>*</b></span><input type="text" name="translations[{{ $language }}][title]" value="{{ $translation?->title }}" maxlength="120" data-preview-field="title"><small>Maksimal 120 karakter.</small></label>
                            <label class="admin-field"><span>{{ $language === 'id' ? 'Subjudul / Deskripsi Singkat' : 'Subtitle / Short Description' }}</span><textarea name="translations[{{ $language }}][subtitle]" maxlength="250" rows="3" data-preview-field="subtitle">{{ $translation?->subtitle }}</textarea><small>Maksimal 250 karakter.</small></label>
                            <label class="admin-field"><span>{{ $language === 'id' ? 'Label Tombol CTA' : 'CTA Button Label' }}</span><input type="text" name="translations[{{ $language }}][cta_label]" value="{{ $translation?->cta_label }}" maxlength="40" data-preview-field="cta_label"></label>
                        </div>
                    </div>
                @endforeach

                <label class="admin-field admin-editor__url"><span>Tautan Tujuan CTA</span><input type="text" name="cta_url" value="{{ $record->cta_url }}" maxlength="500" placeholder="/kontak atau https://..." data-preview-field="cta_url"><small>Gunakan halaman internal atau tautan HTTPS.</small></label>

                <div class="admin-editor__media-block">
                    <div><strong>Gambar Banner</strong><small>Gunakan gambar landscape dengan resolusi yang memadai.</small></div>
                    <label class="admin-field"><span>Pilih dari Media Library <b>*</b></span><select name="media_id" data-hero-media><option value="">Pilih gambar banner...</option>@foreach ($mediaOptions as $media)<option value="{{ $media->id }}" data-image-url="{{ $media->admin_preview_url }}" @selected((int) $record->media_id === $media->id)>{{ $media->original_name }} · {{ number_format($media->size_bytes / 1024, 0) }} KB</option>@endforeach</select></label>
                </div>

                <div class="admin-editor__publish">
                    <div><strong>Status Publikasi</strong><small>Banner Published tampil pada beranda publik.</small></div>
                    <div class="admin-editor__status-options"><button type="button" data-set-status="published" class="{{ ! $hasPendingDraft && ($record->status ?? 'draft') === 'published' ? 'is-selected' : '' }}"><span class="admin-status-dot admin-status-dot--published"></span><span><b>Published</b><small>Tampilkan di beranda</small></span></button><button type="button" data-set-status="draft" class="{{ $hasPendingDraft || ($record->status ?? 'draft') !== 'published' ? 'is-selected' : '' }}"><span class="admin-status-dot admin-status-dot--draft"></span><span><b>Draft</b><small>Simpan tanpa mengubah versi yang sedang tayang</small></span></button></div>
                </div>

                <div class="admin-form__error" data-form-errors hidden></div>
                <div class="admin-editor__actions"><a class="admin-button" href="{{ route('admin.content.index', 'hero-slides') }}">Batal</a>@if ($isEditing)<a class="admin-button" href="{{ route('admin.content.show', ['resource' => 'hero-slides', 'id' => $record->id]) }}"><span class="material-symbols-rounded" aria-hidden="true">visibility</span>Preview</a>@endif<button class="admin-button" type="submit" data-save-status="draft">Simpan Draft</button><button class="admin-button admin-button--primary" type="submit" data-save-status="published"><span class="material-symbols-rounded" aria-hidden="true">publish</span>Simpan &amp; Publish</button></div>
            </section>
        </div>

        <aside class="admin-editor__aside">
            <section class="admin-panel admin-editor-preview"><div class="admin-panel__header"><div><p class="admin-eyebrow">Pratinjau Beranda</p><h2>Preview Banner</h2></div><span class="admin-live-indicator"><i></i> Live preview</span></div><div class="admin-hero-preview" data-hero-preview style="@if ($mediaUrl) --hero-preview-image: url('{{ $mediaUrl }}'); @endif"><div class="admin-hero-preview__shade"></div><div class="admin-hero-preview__content"><span class="admin-hero-preview__eyebrow">PT Interskala Medika Indonesia</span><h3 data-preview-output="title">{{ $translationId?->title ?: 'Judul banner tampil di sini' }}</h3><p data-preview-output="subtitle">{{ $translationId?->subtitle ?: 'Deskripsi singkat banner.' }}</p><span class="admin-hero-preview__button" data-preview-output="cta_label">{{ $translationId?->cta_label ?: 'Hubungi Kami' }} <span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span></span></div></div><p class="admin-editor-preview__note">Pratinjau mengikuti tampilan banner pada halaman beranda.</p></section>
            <section class="admin-panel admin-editor-audit"><div class="admin-panel__header"><div><p class="admin-eyebrow">Informasi Konten</p><h2>Detail Publikasi</h2></div></div><dl><div><dt>Status saat ini</dt><dd>{{ ucfirst($record->status ?? 'draft') }}</dd></div><div><dt>Dibuat</dt><dd>{{ $record->created_at?->format('d M Y, H:i') ?? 'Belum tersimpan' }}</dd></div><div><dt>Pembaruan terakhir</dt><dd>{{ $record->updated_at?->format('d M Y, H:i') ?? 'Belum tersimpan' }}</dd></div><div><dt>Urutan banner</dt><dd>{{ $record->display_order ?: 'Otomatis saat disimpan' }}</dd></div></dl></section>
        </aside>
    </form>
@endsection
