@extends('admin.layout')
@section('title', ($isEditing ? 'Ubah ' : 'Tambah ').'Sertifikat')
@section('page-heading', ($isEditing ? 'Ubah ' : 'Tambah ').'Sertifikat & Penghargaan')
@section('breadcrumb', 'Konten Landing Page  /  Tentang  /  Sertifikat')
@section('page-actions')
    @if ($isEditing)<a class="admin-button" href="{{ route('admin.content.preview', ['resource' => 'certificates', 'id' => $record->id]) }}" target="_blank" rel="noopener">Pratinjau Landing Page</a>@endif
@endsection
@section('content')
    <section class="admin-certificate-editor-wrap">
        <form class="admin-panel admin-certificate-editor" data-admin-form data-resource="certificates" data-api-url="{{ $apiUrl }}" data-is-editing="{{ $isEditing ? '1' : '0' }}" data-redirect-url="{{ $redirectUrl }}" enctype="multipart/form-data">
            @include('admin.partials.record-version', ['record' => $record])
            @csrf
            <div class="admin-panel__header"><div><h2>{{ $isEditing ? 'Perbarui sertifikat' : 'Data sertifikat baru' }}</h2><p>Isi dokumen, judul, tahun, dan status publikasi.</p></div><a class="admin-button admin-button--back" href="{{ route('admin.content.index', 'certificates') }}"><span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>Kembali ke daftar</a></div>
            <div class="admin-editor__language" role="tablist" aria-label="Bahasa sertifikat"><button type="button" class="is-active" data-language-tab="id">Bahasa Indonesia <b>*</b></button><button type="button" data-language-tab="en">Bahasa Inggris</button><button type="button" data-language-tab="zh">Mandarin</button></div>
            <section class="admin-certificate-upload">
                <label class="admin-field"><span>Gambar / Dokumen Sertifikat <b>*</b></span><select name="media_id" data-media-preview-select required><option value="" data-image-url="">Pilih dari Media Library</option>@foreach ($mediaOptions as $media)<option value="{{ $media->id }}" data-image-url="{{ $media->admin_preview_url }}" @selected((string) old('media_id', $record->media_id) === (string) $media->id)>{{ $media->original_name }}</option>@endforeach</select><span class="admin-certificate-upload__preview" data-media-preview>@if ($mediaUrl)<img src="{{ $mediaUrl }}" alt="{{ $record->media?->alt_text_id ?: 'Pratinjau sertifikat' }}" loading="lazy"><span>{{ $record->media?->original_name }}</span>@else<span class="material-symbols-rounded" aria-hidden="true">description</span><span>Pilih dokumen sertifikat</span>@endif</span></label>
            </section>
            @foreach (['id' => 'Bahasa Indonesia', 'en' => 'Bahasa Inggris', 'zh' => 'Mandarin'] as $language => $label)
                @php($translation = $translations->get($language))
                <div class="admin-language-panel {{ $language !== 'id' ? 'is-hidden' : '' }} admin-certificate-language" data-language-panel="{{ $language }}">
                    <label class="admin-field"><span>Judul Sertifikat / Penghargaan @if ($language === 'id')<b>*</b>@endif</span><input type="text" name="translations[{{ $language }}][title]" value="{{ old("translations.$language.title", $translation?->title) }}" maxlength="150" @required($language === 'id')></label>
                    <label class="admin-field"><span>Keterangan</span><textarea name="translations[{{ $language }}][description]" maxlength="300">{{ old("translations.$language.description", $translation?->description) }}</textarea></label>
                </div>
            @endforeach
            <div class="admin-certificate-fields">
                <label class="admin-field"><span>Tahun Terbit</span><input type="number" name="issued_year" value="{{ old('issued_year', $record->issued_year) }}" min="1900" max="{{ now()->year }}"></label>
                <label class="admin-field"><span>Urutan Tampilan</span><input type="number" name="display_order" value="{{ old('display_order', $record->display_order ?: 1) }}" min="1"></label>
                <label class="admin-field"><span>Status Publikasi <b>*</b></span><select name="status" required><option value="draft" @selected(old('status', $record->status ?? 'draft') !== 'published')>Draft</option><option value="published" @selected(old('status', $record->status ?? 'draft') === 'published')>Published (Tayang)</option></select></label>
            </div>
            <div class="admin-form__error" data-form-errors hidden></div>
            <div class="admin-form__actions"><a class="admin-button" href="{{ route('admin.content.index', 'certificates') }}">Batal</a><button class="admin-button" type="submit" data-save-status="draft" formnovalidate>Simpan Draft</button><button class="admin-button admin-button--primary" type="submit" data-save-status="published"><span class="material-symbols-rounded" aria-hidden="true">publish</span>Simpan &amp; Publish</button></div>
        </form>
    </section>
@endsection
