@extends('admin.layout')
@section('title', ($isEditing ? 'Ubah ' : 'Tambah ').$title)
@section('heading', ($isEditing ? 'Ubah ' : 'Tambah ').$title)
@if ($resource === 'product-categories')
    @section('page-heading', ($isEditing ? 'Ubah ' : 'Tambah ').$title)
    @section('breadcrumb', 'Konten Landing Page  /  Produk  /  Kategori Pemeriksaan')
    @section('page-description', 'Kelola nama kategori, slug, urutan tab, dan status katalog.')
@endif
@section('content')
<section class="admin-panel {{ $resource === 'product-categories' ? 'admin-category-editor' : '' }}">
    <div class="admin-panel__header"><div><p class="admin-eyebrow">{{ $title }}</p><h2>{{ $isEditing ? 'Perbarui data' : 'Data baru' }}</h2></div><a class="admin-button admin-button--back" href="{{ route('admin.content.index', $resource) }}"><span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>Kembali ke daftar</a></div>
    <form class="admin-form admin-cms-form {{ $resource === 'brands' ? 'admin-cms-form--brand' : '' }}" data-admin-form data-resource="{{ $resource }}" data-api-url="{{ $apiUrl }}" data-is-editing="{{ $isEditing ? '1' : '0' }}" enctype="multipart/form-data">
        @include('admin.partials.record-version', ['record' => $record])
        @csrf
        @foreach ($definitions['common'] as $field)
            @php
                $fieldName = $field['name'];
                $fieldValue = data_get($record, $fieldName);
                if (! $record->exists && in_array($fieldName, ['event_date', 'posted_at'], true)) { $fieldValue = now()->toDateString(); }
                if (in_array($fieldName, ['event_date', 'posted_at', 'closing_date'], true) && $fieldValue instanceof \DateTimeInterface) { $fieldValue = $fieldValue->format('Y-m-d'); }
                if ($fieldName === 'parameters' && is_array($fieldValue)) { $fieldValue = implode(', ', $fieldValue); }
                if (preg_match('/^translations\[([^\]]+)\]\[([^\]]+)\]$/', $fieldName, $matches)) { $fieldValue = $translations->get($matches[1])?->{$matches[2]}; }
            @endphp
            @if ($field['type'] === 'hidden')
                <input type="hidden" name="{{ $fieldName }}" value="{{ old($fieldName, $field['value'] ?? $fieldValue) }}">
            @else
            <label class="admin-field"><span>{{ $field['label'] }} @if ($field['required'] || ($resource === 'documentations' && $fieldName === 'cover_media_id'))<b>*</b>@endif</span>
                @if ($field['type'] === 'textarea')
                    <textarea name="{{ $fieldName }}" maxlength="{{ $field['maxlength'] ?? 3000 }}" @required($field['required'])>{{ old($fieldName, $fieldValue) }}</textarea>
                @elseif ($field['type'] === 'select')
                    <select name="{{ $fieldName }}" @required($field['required'])><option value="">Pilih...</option>@foreach ($field['options'] ?? [] as $value => $label)<option value="{{ $value }}" @selected((string) old($fieldName, $fieldValue) === (string) $value)>{{ $label }}</option>@endforeach</select>
                @elseif ($field['type'] === 'media')
                    @php
                        $selectedMedia = $mediaOptions->firstWhere('id', (int) old($fieldName, $fieldValue));
                        $selectedMediaUrl = $selectedMedia?->admin_preview_url;
                    @endphp
                    <select name="{{ $fieldName }}" data-media-preview-select @required($field['required'])><option value="" data-image-url="">Pilih media...</option>@foreach ($mediaOptions as $media)<option value="{{ $media->id }}" data-image-url="{{ $media->admin_preview_url }}" @selected((string) old($fieldName, $fieldValue) === (string) $media->id)>{{ $media->original_name }} ({{ number_format($media->size_bytes / 1024, 0) }} KB)</option>@endforeach</select>
                    <span class="admin-media-preview {{ in_array($fieldName, ['logo_top_media_id', 'logo_bottom_media_id'], true) ? 'admin-media-preview--product-logo' : '' }}" data-media-preview>@if ($selectedMediaUrl)<img src="{{ $selectedMediaUrl }}" alt="{{ $selectedMedia?->alt_text_id ?: $selectedMedia?->original_name }}" loading="lazy"><span>{{ $selectedMedia?->original_name }}</span>@else<span>Pilih media untuk melihat pratinjau</span>@endif</span>
                @elseif ($field['type'] === 'file')
                    <input type="file" name="{{ $fieldName }}" accept="application/pdf" @required($field['required'])>@if ($record->brochure_path)@php $brochurePath = ltrim($record->brochure_path, '/'); $brochureUrl = file_exists(public_path($brochurePath)) ? asset($brochurePath) : Illuminate\Support\Facades\Storage::disk(config('cms.media.disk'))->url($brochurePath); @endphp<a class="admin-current-pdf" href="{{ $brochureUrl }}" target="_blank" rel="noopener noreferrer">Buka brosur PDF tersimpan</a>@endif
                @elseif ($field['type'] === 'gallery')
                    <div class="admin-gallery-picker" data-gallery-picker>
                        @foreach ($mediaOptions->sortBy(fn ($item) => $record->exists ? ($record->gallery->firstWhere('id', $item->id)?->pivot?->display_order ?? PHP_INT_MAX) : PHP_INT_MAX) as $media)
                            @php
                                $galleryMedia = $record->exists ? $record->gallery->firstWhere('id', $media->id) : null;
                                $galleryUrl = $media->admin_preview_url;
                            @endphp
                            <article class="admin-gallery-item" data-gallery-item>
                                <label class="admin-gallery-item__select"><input type="checkbox" name="gallery_media_ids[]" value="{{ $media->id }}" @checked($galleryMedia)><img src="{{ $galleryUrl }}" alt="{{ $media->alt_text_id ?: $media->original_name }}" loading="lazy"><span>{{ $media->original_name }}<small>Alt: {{ $media->alt_text_id ?: $media->original_name }}</small></span></label>
                                <button class="admin-gallery-item__caption-button" type="button" data-gallery-caption-open>{{ $galleryMedia?->pivot?->caption_id || $galleryMedia?->pivot?->caption_en || $galleryMedia?->pivot?->caption_zh ? 'Ubah keterangan' : 'Tambah keterangan' }}</button>
                                <div hidden>
                                    <input type="text" data-gallery-caption-id name="gallery_captions[{{ $media->id }}][id]" value="{{ $galleryMedia?->pivot?->caption_id }}" maxlength="200">
                                    <input type="text" data-gallery-caption-en name="gallery_captions[{{ $media->id }}][en]" value="{{ $galleryMedia?->pivot?->caption_en }}" maxlength="200">
                                    <input type="text" data-gallery-caption-zh name="gallery_captions[{{ $media->id }}][zh]" value="{{ $galleryMedia?->pivot?->caption_zh }}" maxlength="200">
                                </div>
                                <div class="admin-gallery-item__order"><button type="button" data-gallery-move="up" aria-label="Naikkan urutan {{ $media->original_name }}">↑ Naik</button><button type="button" data-gallery-move="down" aria-label="Turunkan urutan {{ $media->original_name }}">↓ Turun</button></div>
                            </article>
                        @endforeach
                        <dialog class="admin-gallery-caption-dialog" data-gallery-caption-dialog aria-labelledby="gallery-caption-title">
                            <h2 id="gallery-caption-title">Keterangan foto</h2>
                            <label>Keterangan Bahasa Indonesia<input type="text" data-gallery-dialog-caption="id" maxlength="200"></label>
                            <label>Keterangan English<input type="text" data-gallery-dialog-caption="en" maxlength="200"></label>
                            <label>Keterangan 中文<input type="text" data-gallery-dialog-caption="zh" maxlength="200"></label>
                            <div class="admin-gallery-caption-dialog__actions"><button class="admin-button" type="button" data-gallery-caption-cancel>Batal</button><button class="admin-button admin-button--primary" type="button" data-gallery-caption-save>Simpan keterangan</button></div>
                        </dialog>
                    </div>
                @else
                    <input type="{{ in_array($fieldName, ['event_date', 'posted_at', 'closing_date'], true) ? 'date' : (in_array($fieldName, ['established_year', 'partner_count', 'province_count', 'display_order', 'issued_year'], true) ? 'number' : 'text') }}" name="{{ $fieldName }}" value="{{ old($fieldName, $fieldValue) }}" maxlength="{{ $field['maxlength'] ?? 500 }}" @required($field['required'])>
                @endif
            </label>
            @endif
        @endforeach

        @if ($definitions['id'] !== [] || $definitions['en'] !== [] || $definitions['zh'] !== [])
            <div class="admin-language-tabs" role="tablist"><button type="button" class="is-active" data-language-tab="id">Bahasa Indonesia <b>*</b></button><button type="button" data-language-tab="en">English</button><button type="button" data-language-tab="zh">中文</button></div>
            @foreach (['id' => 'Bahasa Indonesia', 'en' => 'English', 'zh' => '中文'] as $language => $label)
                <div class="admin-language-panel {{ $language !== 'id' ? 'is-hidden' : '' }}" data-language-panel="{{ $language }}">
                    <h3>{{ $label }}</h3>
                    @foreach ($definitions[$language] as $field)
                        @php
                            $fieldName = $field['name'];
                            preg_match('/^translations\[([^\]]+)\]\[([^\]]+)\]$/', $fieldName, $matches);
                            $fieldValue = isset($matches[2]) ? $translations->get($matches[1])?->{$matches[2]} : null;
                            $isRichText = $field['type'] === 'textarea'
                                && in_array($resource, ['products', 'documentations', 'job-openings'], true)
                                && (str_contains($fieldName, '[description]') || str_contains($fieldName, '[body]') || str_contains($fieldName, '[requirements]'));
                            $isRequired = $field['required'] && $language === 'id';
                            $requiredMarker = $field['required'] || ($resource === 'documentations' && $record->type === 'article' && $fieldName === 'translations[id][body]');
                        @endphp
                        @if ($isRichText)
                            <div class="admin-field admin-field--rich" data-rich-field>
                                <span>{{ $field['label'] }} @if ($requiredMarker)<b>*</b>@endif</span>
                                <div class="admin-rich-editor" data-rich-editor data-placeholder="Tulis {{ mb_strtolower($field['label']) }} di sini...">
                                    <textarea name="{{ $fieldName }}" @required($isRequired) @if ($isRequired) data-rich-required @endif>{{ old($fieldName, $fieldValue) }}</textarea>
                                </div>
                                <small>Gunakan tombol format untuk menebalkan, memiringkan, membuat daftar, atau menambahkan tautan.</small>
                            </div>
                        @else
                            <label class="admin-field {{ str_contains($fieldName, '[excerpt]') || ($resource === 'job-openings' && str_ends_with($fieldName, '[title]')) ? 'admin-field--fill-row' : '' }}"><span>{{ $field['label'] }} @if ($requiredMarker)<b>*</b>@endif</span>
                                @if ($field['type'] === 'textarea')<textarea name="{{ $fieldName }}" maxlength="{{ $field['maxlength'] ?? 3000 }}" @required($isRequired)>{{ old($fieldName, $fieldValue) }}</textarea>
                                @else<input type="text" name="{{ $fieldName }}" value="{{ old($fieldName, $fieldValue) }}" maxlength="{{ $field['maxlength'] ?? 500 }}" @required($isRequired)>@endif
                            </label>
                        @endif
                    @endforeach
                </div>
            @endforeach
        @endif
        @if ($resource === 'documentations')
            <p>Kolom bertanda * wajib untuk publikasi. Draft dapat disimpan dengan data parsial. Galeri media bersifat opsional.</p>
        @endif
        <div class="admin-form__error" data-form-errors hidden></div>
        <div class="admin-form__actions">
            <a class="admin-button" href="{{ route('admin.content.index', $resource) }}">Batal</a>
            @if (in_array('status', $record->getFillable(), true))
                <button class="admin-button" type="submit" data-save-status="draft" formnovalidate>Simpan Draft</button>
                <button class="admin-button admin-button--primary" type="submit" data-save-status="{{ $resource === 'job-openings' ? 'open' : 'published' }}">{{ $resource === 'job-openings' ? 'Simpan & Buka' : 'Simpan & Publish' }}</button>
            @else
                <button class="admin-button admin-button--primary" type="submit" data-save-status="">Simpan</button>
            @endif
            @if ($isEditing && in_array($resource, ['products', 'product-categories', 'documentations', 'job-openings', 'brands', 'mission-items', 'certificates', 'social-links'], true))<a class="admin-button" href="{{ route('admin.content.preview', ['resource' => $resource, 'id' => $record->id]) }}" target="_blank" rel="noopener">Pratinjau Landing Page</a>@endif
        </div>
    </form>
</section>
@endsection
