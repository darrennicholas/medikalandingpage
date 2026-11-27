@extends('admin.layout')
@section('title', ($isEditing ? 'Ubah ' : 'Tambah ').$title)
@section('heading', ($isEditing ? 'Ubah ' : 'Tambah ').$title)
@if ($resource === 'product-categories')
    @section('page-heading', ($isEditing ? 'Ubah ' : 'Tambah ').$title)
    @section('breadcrumb', 'Konten Landing Page  /  Produk  /  Pengaturan Kategori')
    @section('page-description', 'Kelola nama kategori, slug, urutan tab, dan status katalog.')
@endif
@section('content')
<section class="admin-panel {{ $resource === 'product-categories' ? 'admin-category-editor' : '' }}">
    <div class="admin-panel__header"><div><p class="admin-eyebrow">{{ $title }}</p><h2>{{ $isEditing ? 'Perbarui data' : 'Data baru' }}</h2></div><a class="admin-button admin-button--back" href="{{ route('admin.content.index', $resource) }}"><span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>Kembali ke daftar</a></div>
    <form class="admin-form admin-cms-form {{ $resource === 'brands' ? 'admin-cms-form--brand' : '' }} {{ $resource === 'products' ? 'admin-cms-form--product' : '' }}" data-admin-form data-resource="{{ $resource }}" data-api-url="{{ $apiUrl }}" data-is-editing="{{ $isEditing ? '1' : '0' }}" enctype="multipart/form-data">
        @include('admin.partials.record-version', ['record' => $record])
        @csrf
        @foreach ($definitions['common'] as $field)
            @php
                $fieldName = $field['name'];
                $fieldValue = data_get($record, $fieldName);
                if ($resource === 'products' && $fieldName === 'new_status') { $fieldValue = $record->new_until && $record->new_until->gte(today()) ? 'new' : 'not_new'; }
                if (! $record->exists && in_array($fieldName, ['event_date', 'posted_at'], true)) { $fieldValue = now()->toDateString(); }
                if (in_array($fieldName, ['event_date', 'posted_at', 'closing_date', 'new_until'], true) && $fieldValue instanceof \DateTimeInterface) { $fieldValue = $fieldValue->format('Y-m-d'); }
                if ($fieldName === 'parameters' && is_array($fieldValue)) { $fieldValue = implode(', ', $fieldValue); }
                if (preg_match('/^translations\[([^\]]+)\]\[([^\]]+)\]$/', $fieldName, $matches)) { $fieldValue = $translations->get($matches[1])?->{$matches[2]}; }
            @endphp
            @if ($resource === 'products' && in_array($fieldName, ['category_id', 'media_id', 'buy_url', 'status'], true))
                <div class="admin-product-form-heading"><h3>{{ match ($fieldName) { 'category_id' => 'Kategori produk', 'media_id' => 'Gambar produk', 'buy_url' => 'Informasi produk', default => 'Status publikasi' } }}</h3><p>{{ match ($fieldName) { 'category_id' => 'Tentukan kategori utama dan subkategori agar produk mudah ditemukan.', 'media_id' => 'Pilih gambar utama dan logo produk.', 'buy_url' => 'Lengkapi tautan, brosur, dan parameter produk.', default => 'Tentukan apakah produk sudah siap ditayangkan.' } }}</p></div>
            @endif
            @if ($resource === 'products' && $fieldName === 'media_id')<div class="admin-product-media-group">@endif
            @if ($field['type'] === 'hidden')
                <input type="hidden" name="{{ $fieldName }}" value="{{ old($fieldName, $field['value'] ?? $fieldValue) }}">
            @else
            <label class="admin-field" @if ($resource === 'products' && $fieldName === 'new_until') data-product-new-until-field @if (! $record->new_until || $record->new_until->lt(today())) hidden @endif @endif><span>{{ $field['label'] }} @if ($field['required'] || ($resource === 'documentations' && $fieldName === 'cover_media_id'))<b>*</b>@endif</span>
                @if ($field['type'] === 'date')
                    <input type="date" name="{{ $fieldName }}" value="{{ old($fieldName, $fieldValue) }}" @if ($resource === 'products') data-product-new-until @endif><small>Wajib diisi jika status produk New. Tanda New otomatis berhenti setelah tanggal ini.</small>
                @elseif ($field['type'] === 'textarea')
                    <textarea name="{{ $fieldName }}" maxlength="{{ $field['maxlength'] ?? 3000 }}" @required($field['required'])>{{ old($fieldName, $fieldValue) }}</textarea>
                @elseif ($field['type'] === 'select')
                    <select name="{{ $fieldName }}" @required($field['required']) @if ($resource === 'products' && $fieldName === 'new_status') data-product-new-status @endif><option value="">Pilih...</option>@foreach ($field['options'] ?? [] as $value => $label)<option value="{{ $value }}" @selected((string) old($fieldName, $fieldValue) === (string) $value)>{{ $label }}</option>@endforeach</select>
                @elseif ($field['type'] === 'media')
                    @php
                        $selectedMedia = $mediaOptions->firstWhere('id', (int) old($fieldName, $fieldValue));
                        $selectedMediaUrl = $selectedMedia?->admin_preview_url;
                    @endphp
                    <select name="{{ $fieldName }}" data-media-preview-select @required($field['required'])><option value="" data-image-url="">Pilih media...</option>@foreach ($mediaOptions as $media)<option value="{{ $media->id }}" data-image-url="{{ $media->admin_preview_url }}" @selected((string) old($fieldName, $fieldValue) === (string) $media->id)>{{ $media->original_name }} ({{ number_format($media->size_bytes / 1024, 0) }} KB)</option>@endforeach</select>
                    <span class="admin-media-preview {{ in_array($fieldName, ['logo_top_media_id', 'logo_bottom_media_id'], true) ? 'admin-media-preview--product-logo' : '' }}" data-media-preview>@if ($selectedMediaUrl)<img src="{{ $selectedMediaUrl }}" alt="{{ $selectedMedia?->alt_text_id ?: $selectedMedia?->original_name }}" loading="lazy"><span>{{ $selectedMedia?->original_name }}</span>@else<span>Pilih media untuk melihat pratinjau</span>@endif</span>
                @elseif ($field['type'] === 'file')
                    <div class="admin-local-file-picker" data-local-file-picker>
                        <button class="admin-button admin-button--soft" type="button" data-file-picker-trigger="{{ $fieldName }}-upload" aria-controls="{{ $fieldName }}-upload"><span class="material-symbols-rounded" aria-hidden="true">upload_file</span>Upload brosur PDF</button>
                        <input id="{{ $fieldName }}-upload" type="file" name="{{ $fieldName }}" accept="application/pdf" @required($field['required']) hidden>
                        <span class="admin-local-file-picker__status" data-file-picker-status data-empty-label="Belum ada PDF dipilih" data-selected-unit="file" aria-live="polite">Belum ada PDF dipilih</span>
                    </div>
                    @if ($record->brochure_path)@php $brochurePath = preg_replace('#^(?:public/)?brochures/#', '', ltrim($record->brochure_path, '/')); $brochureUrl = route('stored.brochure', ['path' => $brochurePath]); @endphp<a class="admin-current-pdf" href="{{ $brochureUrl }}" target="_blank" rel="noopener noreferrer">Buka brosur PDF tersimpan</a>@endif
                @elseif ($field['type'] === 'gallery')
                    <span>Tambahkan gambar baru untuk galeri</span>
                    <div class="admin-local-file-picker" data-local-file-picker>
                        <button class="admin-button admin-button--soft" type="button" data-file-picker-trigger="gallery-images" aria-controls="gallery-images"><span class="material-symbols-rounded" aria-hidden="true">upload_file</span>Upload gambar</button>
                        <input id="gallery-images" type="file" accept="image/jpeg,image/png,image/webp" multiple data-gallery-upload hidden>
                        <span class="admin-local-file-picker__status" data-file-picker-status data-empty-label="Belum ada gambar dipilih" data-selected-unit="gambar" aria-live="polite">Belum ada gambar dipilih</span>
                    </div>
                    <small>JPG, PNG, atau WebP, maksimal 2 MB per gambar. Gambar disimpan saat konten disimpan.</small>
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
                            <div class="admin-language-tabs" data-language-scope role="tablist" aria-label="Bahasa keterangan foto"><button class="is-active" type="button" data-language-tab="id">Bahasa Indonesia</button><button type="button" data-language-tab="en">Bahasa Inggris</button><button type="button" data-language-tab="zh">Mandarin</button></div>
                            <div class="admin-language-panel" data-language-panel="id"><label>Keterangan Bahasa Indonesia<input type="text" data-gallery-dialog-caption="id" maxlength="200"></label></div>
                            <div class="admin-language-panel is-hidden" data-language-panel="en"><label>Keterangan Bahasa Inggris<input type="text" data-gallery-dialog-caption="en" maxlength="200"></label></div>
                            <div class="admin-language-panel is-hidden" data-language-panel="zh"><label>Keterangan Mandarin<input type="text" data-gallery-dialog-caption="zh" maxlength="200"></label></div>
                            <div class="admin-gallery-caption-dialog__actions"><button class="admin-button" type="button" data-gallery-caption-cancel>Batal</button><button class="admin-button admin-button--primary" type="button" data-gallery-caption-save>Simpan keterangan</button></div>
                        </dialog>
                    </div>
                @elseif ($fieldName === 'subcategory')
                    <select name="subcategory" data-product-subcategory><option value="">Tanpa subkategori</option>@foreach ($field['suggestions'] ?? [] as $suggestion)<option value="{{ $suggestion->name }}" data-category-id="{{ $suggestion->category_id }}" @selected((string) old($fieldName, $fieldValue) === $suggestion->name && (int) old('category_id', $record->category_id) === $suggestion->category_id)>{{ $suggestion->name }}</option>@endforeach</select>
                    <small>Tambahkan pilihan melalui menu Pengaturan SubKategori.</small>
                @else
                    <input type="{{ in_array($fieldName, ['event_date', 'posted_at', 'closing_date'], true) ? 'date' : (in_array($fieldName, ['established_year', 'partner_count', 'province_count', 'display_order', 'issued_year'], true) ? 'number' : 'text') }}" name="{{ $fieldName }}" value="{{ old($fieldName, $fieldValue) }}" maxlength="{{ $field['maxlength'] ?? 500 }}" @if ($fieldName === 'subcategory') list="product-subcategory-options" placeholder="Contoh: Drugs of Abuse (DOA)" @endif @required($field['required'])>
                    @if ($fieldName === 'subcategory')<datalist id="product-subcategory-options">@foreach ($field['suggestions'] ?? [] as $suggestion)<option value="{{ $suggestion }}">@endforeach</datalist><small>Pilih atau tulis subkategori di bawah kategori utama.</small>@endif
                @endif
            </label>
            @endif
            @if ($resource === 'products' && $fieldName === 'logo_bottom_media_id')</div>@endif
        @endforeach

        @if ($definitions['id'] !== [] || $definitions['en'] !== [] || $definitions['zh'] !== [])
            @if ($resource === 'products')<div class="admin-product-form-heading"><h3>Nama dan deskripsi produk</h3><p>Isi informasi produk dalam bahasa yang tersedia. Subkategori diatur pada bagian kategori di atas.</p></div>@endif
            <div class="admin-language-tabs" role="tablist"><button type="button" class="is-active" data-language-tab="id">Bahasa Indonesia <b>*</b></button><button type="button" data-language-tab="en">Bahasa Inggris</button><button type="button" data-language-tab="zh">Mandarin</button></div>
            @foreach (['id' => 'Bahasa Indonesia', 'en' => 'Bahasa Inggris', 'zh' => 'Mandarin'] as $language => $label)
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
                                <div class="admin-rich-editor" data-rich-editor @if ($resource === 'documentations' && str_contains($fieldName, '[body]')) data-article-editor @endif data-placeholder="Tulis {{ mb_strtolower($field['label']) }} di sini...">
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
            <p>Kolom bertanda * wajib untuk publikasi. Draft dapat disimpan dengan data parsial.</p>
        @endif
        <div class="admin-form__error" data-form-errors hidden></div>
        <div class="admin-form__actions">
            <a class="admin-button" href="{{ route('admin.content.index', $resource) }}">Batal</a>
            @if (in_array($resource, ['products', 'product-categories', 'documentations', 'job-openings', 'brands', 'mission-items', 'certificates', 'social-links'], true))
                <input type="hidden" name="preview_lang" value="id" data-unsaved-preview-language>
                <button class="admin-button" type="submit" data-preview-unsaved formaction="{{ route('admin.content.preview-unsaved', ['resource' => $resource]) }}" formmethod="post" formtarget="_blank" formnovalidate><span class="material-symbols-rounded" aria-hidden="true">visibility</span>Preview Perubahan</button>
            @endif
            @if (in_array('status', $record->getFillable(), true))
                <button class="admin-button" type="submit" data-save-status="draft" formnovalidate>Simpan Draft</button>
                <button class="admin-button admin-button--primary" type="submit" data-save-status="{{ $resource === 'job-openings' ? 'open' : 'published' }}">{{ $resource === 'job-openings' ? 'Simpan & Buka' : 'Simpan & Publish' }}</button>
            @else
                <button class="admin-button admin-button--primary" type="submit" data-save-status="">Simpan</button>
            @endif
            @if ($isEditing && in_array($resource, ['products', 'product-categories', 'documentations', 'job-openings', 'brands', 'mission-items', 'certificates', 'social-links'], true))<a class="admin-button" href="{{ route('admin.content.preview', ['resource' => $resource, 'id' => $record->id]) }}" target="_blank" rel="noopener">Preview Landing Page</a>@endif
        </div>
    </form>
</section>
@endsection
