@extends('admin.layout')
@php
    $isCoverage = $blockKey === 'home_coverage';
    $hasPendingDraft = is_array($record->draft_payload);
    $translationId = $translations->get('id');
    $translationEn = $translations->get('en');
    $previewMetricRows = [];

    if ($fallbackMetrics !== null) {
        $previewMetricRows = [
            ['value' => $fallbackMetrics->province_count, 'label' => 'Provinsi'],
            ['value' => number_format($fallbackMetrics->partner_count), 'label' => 'Mitra'],
        ];
    }
@endphp
@section('title', $title.' | Beranda')
@section('page-heading', $title)
@section('breadcrumb', 'Konten Landing Page  /  Beranda  /  '.$title)
@section('page-description', $isCoverage ? 'Kelola informasi jangkauan, metrik, dan gambar yang ditampilkan di beranda.' : 'Kelola judul ajakan, pesan pendukung, dan tombol aksi pada beranda.')
@section('page-actions')
    @if ($record->exists)<a class="admin-button" href="{{ route('admin.content.preview', ['resource' => 'content-blocks', 'id' => $blockKey]) }}" target="_blank" rel="noopener">Pratinjau Landing Page</a>@endif
@endsection
@section('content')
    @if ($hasPendingDraft)<div class="admin-alert admin-alert--info"><span class="material-symbols-rounded" aria-hidden="true">info</span><span>Draft perubahan tersimpan. Versi Published pada halaman beranda tetap aktif sampai Anda memilih <strong>Simpan &amp; Publish</strong>.</span></div>@endif
    <form class="admin-editor admin-editor--block" novalidate data-admin-form data-live-editor="{{ $isCoverage ? 'coverage' : 'cta' }}" data-resource="content-blocks" data-api-url="{{ $apiUrl }}" data-is-editing="{{ $record->exists ? '1' : '0' }}" data-redirect-url="{{ $redirectUrl }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="block_key" value="{{ $blockKey }}">
        <input type="hidden" name="status" value="{{ $record->status ?? 'draft' }}" data-editor-status>
        <input type="hidden" name="cta_url" value="{{ $record->cta_url }}" data-legacy-cta-url>
        <div class="admin-editor__main">
            <section class="admin-panel admin-editor__panel">
                <div class="admin-panel__header"><div><p class="admin-eyebrow">Beranda</p><h2>{{ $isCoverage ? 'Informasi Jangkauan' : 'Konten & Aksi CTA' }}</h2></div><span class="admin-status admin-status--{{ $hasPendingDraft ? 'draft' : ($record->status ?? 'draft') }}" data-editor-status-label>{{ $hasPendingDraft ? 'Draft perubahan' : (($record->status ?? 'draft') === 'published' ? 'Published' : 'Draft') }}</span></div>
                <div class="admin-editor__language" role="tablist" aria-label="Bahasa konten"><button type="button" class="is-active" data-language-tab="id">Indonesia <span>*</span></button><button type="button" data-language-tab="en">English</button></div>

                @foreach (['id' => 'Bahasa Indonesia', 'en' => 'English'] as $language => $languageLabel)
                    @php($translation = $language === 'id' ? $translationId : $translationEn)
                    @php($metricRows = $translation?->metrics ?? [])
                    @php($buttonRows = $translation?->buttons ?? [])
                    @if (! $isCoverage && $buttonRows === [] && filled($translation?->cta_label))
                        @php($buttonRows = [['label' => $translation->cta_label, 'url' => $record->cta_url, 'style' => 'primary', 'icon' => 'chat']])
                    @endif
                    <div class="admin-language-panel {{ $language === 'en' ? 'is-hidden' : '' }}" data-language-panel="{{ $language }}">
                        <div class="admin-editor__fields">
                            <label class="admin-field"><span>{{ $language === 'id' ? 'Judul Bagian / Headline' : 'Section Title / Headline' }} <b>*</b></span><input type="text" name="translations[{{ $language }}][title]" value="{{ $translation?->title }}" maxlength="120" data-preview-field="title"><small>Maksimal 120 karakter.</small></label>
                            @if ($isCoverage)
                                <label class="admin-field"><span>{{ $language === 'id' ? 'Subjudul' : 'Subtitle' }}</span><input type="text" name="translations[{{ $language }}][subtitle]" value="{{ $translation?->subtitle }}" maxlength="300" data-preview-field="subtitle"></label>
                                <label class="admin-field"><span>{{ $language === 'id' ? 'Deskripsi Singkat' : 'Short Description' }}</span><textarea name="translations[{{ $language }}][body]" maxlength="500" rows="4" data-preview-field="body">{{ $translation?->body }}</textarea><small>Maksimal 500 karakter.</small></label>
                                <section class="admin-editor-repeater"><div class="admin-editor-repeater__heading"><div><h3>Indikator &amp; Angka Statistik Jangkauan</h3><p>Maksimal 6 metrik. Nilai ditampilkan sebagai kartu statistik.</p></div><button type="button" class="admin-button" data-repeater-add="metrics-{{ $language }}"><span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah Metrik</button></div><input type="hidden" name="translations[{{ $language }}][metrics_present]" value="1"><div class="admin-editor-repeater__list" data-repeater-list="metrics-{{ $language }}">@foreach ($metricRows as $index => $metric)<div class="admin-editor-repeater__item" data-repeater-item><label class="admin-field"><span>Nilai / Angka</span><input type="text" name="translations[{{ $language }}][metrics][{{ $index }}][value]" value="{{ $metric['value'] ?? '' }}" maxlength="40" data-metric-value></label><label class="admin-field"><span>Label Keterangan</span><input type="text" name="translations[{{ $language }}][metrics][{{ $index }}][label]" value="{{ $metric['label'] ?? '' }}" maxlength="60" data-metric-label></label><button type="button" class="admin-icon-button is-danger" data-repeater-remove aria-label="Hapus metrik"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></div>@endforeach</div><template data-repeater-template="metrics-{{ $language }}"><div class="admin-editor-repeater__item" data-repeater-item><label class="admin-field"><span>Nilai / Angka</span><input type="text" name="translations[{{ $language }}][metrics][__index__][value]" maxlength="40" data-metric-value></label><label class="admin-field"><span>Label Keterangan</span><input type="text" name="translations[{{ $language }}][metrics][__index__][label]" maxlength="60" data-metric-label></label><button type="button" class="admin-icon-button is-danger" data-repeater-remove aria-label="Hapus metrik"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></div></template></section>
                            @else
                                <label class="admin-field"><span>Teks Pendukung / Sub-headline</span><textarea name="translations[{{ $language }}][body]" maxlength="{{ $blockKey === 'home_cta' ? 300 : 10000 }}" rows="4" data-preview-field="body">{{ $translation?->body }}</textarea><small>Maksimal {{ $blockKey === 'home_cta' ? '300' : '10.000' }} karakter.</small></label>
                                <input type="hidden" name="translations[{{ $language }}][subtitle]" value="{{ $translation?->subtitle }}">
                                <input type="hidden" name="translations[{{ $language }}][cta_label]" value="{{ $translation?->cta_label }}" data-legacy-cta-label="{{ $language }}">
                                <section class="admin-editor-repeater admin-editor-repeater--buttons"><div class="admin-editor-repeater__heading"><div><h3>Tombol Aksi &amp; Call-to-Action</h3><p>Atur label, tautan, dan variasi tampilan tombol.</p></div><button type="button" class="admin-button" data-repeater-add="buttons-{{ $language }}"><span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah Tombol</button></div><input type="hidden" name="translations[{{ $language }}][buttons_present]" value="1"><div class="admin-editor-repeater__list" data-repeater-list="buttons-{{ $language }}">@foreach ($buttonRows as $index => $button)<div class="admin-editor-repeater__item admin-editor-repeater__item--button" data-repeater-item><div class="admin-editor-repeater__item-heading"><strong>Tombol {{ $index + 1 }}</strong><button type="button" class="admin-icon-button is-danger" data-repeater-remove aria-label="Hapus tombol"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></div><label class="admin-field"><span>Label Tombol</span><input type="text" name="translations[{{ $language }}][buttons][{{ $index }}][label]" value="{{ $button['label'] ?? '' }}" maxlength="40" data-button-label></label><label class="admin-field"><span>Tautan URL Tujuan</span><input type="text" name="translations[{{ $language }}][buttons][{{ $index }}][url]" value="{{ $button['url'] ?? '' }}" maxlength="500" placeholder="/kontak atau https://..." data-button-url></label><div class="admin-editor__fields admin-editor__fields--compact"><label class="admin-field"><span>Variasi Tampilan</span><select name="translations[{{ $language }}][buttons][{{ $index }}][style]" data-button-style><option value="primary" @selected(($button['style'] ?? 'primary') === 'primary')>Primer / Solid</option><option value="secondary" @selected(($button['style'] ?? '') === 'secondary')>Sekunder / Outline</option><option value="tertiary" @selected(($button['style'] ?? '') === 'tertiary')>Tersier / Tautan</option></select></label><label class="admin-field"><span>Ikon Material</span><input type="text" name="translations[{{ $language }}][buttons][{{ $index }}][icon]" value="{{ $button['icon'] ?? '' }}" maxlength="30" placeholder="chat, download, event" data-button-icon></label></div></div>@endforeach</div><template data-repeater-template="buttons-{{ $language }}"><div class="admin-editor-repeater__item admin-editor-repeater__item--button" data-repeater-item><div class="admin-editor-repeater__item-heading"><strong>Tombol</strong><button type="button" class="admin-icon-button is-danger" data-repeater-remove aria-label="Hapus tombol"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></div><label class="admin-field"><span>Label Tombol</span><input type="text" name="translations[{{ $language }}][buttons][__index__][label]" maxlength="40" data-button-label></label><label class="admin-field"><span>Tautan URL Tujuan</span><input type="text" name="translations[{{ $language }}][buttons][__index__][url]" maxlength="500" placeholder="/kontak atau https://..." data-button-url></label><div class="admin-editor__fields admin-editor__fields--compact"><label class="admin-field"><span>Variasi Tampilan</span><select name="translations[{{ $language }}][buttons][__index__][style]" data-button-style><option value="primary">Primer / Solid</option><option value="secondary">Sekunder / Outline</option><option value="tertiary">Tersier / Tautan</option></select></label><label class="admin-field"><span>Ikon Material</span><input type="text" name="translations[{{ $language }}][buttons][__index__][icon]" maxlength="30" placeholder="chat, download, event" data-button-icon></label></div></div></template></section>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if ($isCoverage)
                    <section class="admin-editor__media-block"><div><strong>Gambar Ilustrasi / Peta Jangkauan <b>*</b></strong><small>Pilih gambar yang digunakan pada bagian jangkauan di halaman beranda.</small></div><label class="admin-field"><span>Media Library</span><select name="media_id" data-block-media><option value="">Pilih gambar jangkauan...</option>@foreach ($mediaOptions as $media)<option value="{{ $media->id }}" data-image-url="{{ $media->admin_preview_url }}" @selected((int) $record->media_id === $media->id)>{{ $media->original_name }} · {{ number_format($media->size_bytes / 1024, 0) }} KB</option>@endforeach</select></label></section>
                @endif

                <div class="admin-editor__publish"><div><strong>Status Publikasi <b>*</b></strong><small>Published memperbarui bagian beranda publik setelah disimpan.</small></div><div class="admin-editor__status-options"><button type="button" data-set-status="published" class="{{ ! $hasPendingDraft && ($record->status ?? 'draft') === 'published' ? 'is-selected' : '' }}"><span class="admin-status-dot admin-status-dot--published"></span><span><b>Published (Tayang)</b><small>Perubahan tampil pada landing page.</small></span></button><button type="button" data-set-status="draft" class="{{ $hasPendingDraft || ($record->status ?? 'draft') !== 'published' ? 'is-selected' : '' }}"><span class="admin-status-dot admin-status-dot--draft"></span><span><b>Draft Internal</b><small>Simpan tanpa mengubah konten publik.</small></span></button></div></div>
                <div class="admin-form__error" data-form-errors hidden></div>
                <div class="admin-editor__actions"><a class="admin-button" href="{{ route('admin.content.index', ['resource' => 'content-blocks', 'section' => $blockKey]) }}">Batal</a><button class="admin-button" type="submit" data-save-status="draft">Simpan Draft</button><button class="admin-button admin-button--primary" type="submit" data-save-status="published"><span class="material-symbols-rounded" aria-hidden="true">publish</span>Simpan &amp; Publish</button></div>
            </section>
        </div>

        <aside class="admin-editor__aside">
            <section class="admin-panel admin-editor-preview"><div class="admin-panel__header"><div><p class="admin-eyebrow">Pratinjau Beranda</p><h2>{{ $isCoverage ? 'Pratinjau Jangkauan' : 'Pratinjau Live CTA' }}</h2></div><div class="admin-preview-switch"><button type="button" class="is-active" data-preview-device="desktop" aria-label="Pratinjau desktop"><span class="material-symbols-rounded" aria-hidden="true">desktop_windows</span></button><button type="button" data-preview-device="mobile" aria-label="Pratinjau mobile"><span class="material-symbols-rounded" aria-hidden="true">smartphone</span></button></div></div>
                @if ($isCoverage)
                    <div class="admin-coverage-preview" data-coverage-preview><span class="admin-coverage-preview__tag">JANGKAUAN KAMI</span><span class="admin-live-indicator"><i></i>Live Sync</span><h3 data-preview-output="title">{{ $translationId?->title ?: 'Judul jangkauan' }}</h3><h4 data-preview-output="subtitle">{{ $translationId?->subtitle }}</h4><p data-preview-output="body">{{ $translationId?->body ?: 'Deskripsi jangkauan tampil di sini.' }}</p><div class="admin-coverage-preview__image" data-block-image-preview>@if ($mediaUrl)<img src="{{ $mediaUrl }}" alt="{{ $record->media?->alt_text_id ?? '' }}" data-preview-image>@else<span>Pilih gambar ilustrasi atau peta</span>@endif</div><div class="admin-coverage-preview__metrics" data-preview-metrics data-default-metrics='@json($previewMetricRows)'>@if ($translationId?->metrics)@foreach ($translationId->metrics as $metric)<div><strong>{{ $metric['value'] ?? '' }}</strong><span>{{ $metric['label'] ?? '' }}</span></div>@endforeach@elseif ($fallbackMetrics)<div><strong>{{ $fallbackMetrics->province_count }}</strong><span>Provinsi</span></div><div><strong>{{ number_format($fallbackMetrics->partner_count) }}</strong><span>Mitra</span></div>@endif</div></div>
                @else
                    <div class="admin-cta-preview" data-cta-preview>
                        <section class="contact-cta" id="kontak" aria-labelledby="contact-title">
                            <div class="container">
                                <h2 id="contact-title" data-preview-output="title" @if (blank($translationId?->title)) hidden @endif>{{ $translationId?->title }}</h2>
                                <div data-preview-output="body" @if (blank($translationId?->body)) hidden @endif>{{ $translationId?->body }}</div>
                                <div class="contact-cta__actions" data-preview-buttons>
                                    @if ($translationId?->buttons)
                                        @foreach ($translationId->buttons as $button)
                                            @if (filled($button['label'] ?? null) && filled($button['url'] ?? null))
                                                @php($buttonStyle = in_array($button['style'] ?? 'primary', ['primary', 'secondary', 'tertiary'], true) ? ($button['style'] ?? 'primary') : 'primary')
                                                <span class="contact-cta__button contact-cta__button--{{ $buttonStyle }}">
                                                    @if (filled($button['icon'] ?? null))<span class="material-symbols-rounded" aria-hidden="true">{{ $button['icon'] }}</span>@endif{{ $button['label'] }}
                                                </span>
                                            @endif
                                        @endforeach
                                    @elseif ($translationId?->cta_label && $record->cta_url)
                                        <span class="contact-cta__button">{{ $translationId->cta_label }}</span>
                                    @endif
                                </div>
                            </div>
                        </section>
                    </div>
                @endif
                <p class="admin-editor-preview__note">Simulasi menyesuaikan bahasa dan ukuran layar yang dipilih.</p>
            </section>
            <section class="admin-panel admin-editor-audit"><div class="admin-panel__header"><div><p class="admin-eyebrow">Informasi Audit Konten</p><h2>Riwayat Konten</h2></div></div><dl><div><dt>Status</dt><dd>{{ ($record->status ?? 'draft') === 'published' ? 'Published' : 'Draft' }}</dd></div><div><dt>Dibuat</dt><dd>{{ $record->created_at?->format('d M Y, H:i') ?? 'Belum tersimpan' }}</dd></div><div><dt>Pembaruan terakhir</dt><dd>{{ $record->updated_at?->format('d M Y, H:i') ?? 'Belum tersimpan' }}</dd></div></dl></section>
        </aside>
    </form>
@endsection
