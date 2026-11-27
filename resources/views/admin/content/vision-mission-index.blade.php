@extends('admin.layout')
@section('title', 'Visi & Misi')
@section('page-heading', 'Visi & Misi Perusahaan')
@section('breadcrumb', 'Konten Landing Page  /  Tentang  /  Visi & Misi')
@section('page-description', 'Kelola pernyataan visi perusahaan dan butir misi yang ditampilkan pada halaman Tentang.')
@section('page-actions')
    <a class="admin-button" href="{{ $visionBlock->exists ? route('admin.content.preview', ['resource' => 'content-blocks', 'id' => 'about_vision']) : url('/id/tentang') }}" target="_blank" rel="noopener"><span class="material-symbols-rounded" aria-hidden="true">visibility</span>Preview Landing Page</a>
@endsection
@section('content')
    @php
        $missionTotal = array_sum($missionStats);
        $missionPublished = $missionStats['published'] ?? 0;
        $missionDraft = $missionStats['draft'] ?? 0;
    @endphp

    <section class="admin-management-stats admin-management-stats--mission" aria-label="Ringkasan misi">
        <article class="admin-management-stat admin-summary-card"><span>Total Poin Misi</span><strong>{{ $missionTotal }}</strong><span class="admin-management-stat__note">Semua butir misi terdaftar</span><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">format_list_bulleted</span></article>
        <article class="admin-management-stat admin-management-stat--success admin-summary-card admin-summary-card--success"><span>Published</span><strong>{{ $missionPublished }}</strong><span class="admin-management-stat__note">Ditampilkan di landing page</span><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">check_circle</span></article>
        <article class="admin-management-stat admin-management-stat--muted admin-summary-card admin-summary-card--warning"><span>Draft</span><strong>{{ $missionDraft }}</strong><span class="admin-management-stat__note">Belum tampil untuk publik</span><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">pending</span></article>
    </section>

    <form class="admin-vision-editor admin-panel" novalidate data-admin-form data-live-editor="coverage" data-resource="content-blocks" data-api-url="/api/v1/admin/content-blocks/about_vision" data-is-editing="{{ $visionBlock->exists ? '1' : '0' }}" data-redirect-url="{{ route('admin.content.index', 'mission-items') }}">
        @include('admin.partials.record-version', ['record' => $visionBlock])
        @csrf
        <input type="hidden" name="block_key" value="about_vision">
        <input type="hidden" name="status" value="{{ $visionBlock->status ?? 'draft' }}" data-editor-status>
        <div class="admin-panel__header"><div><p class="admin-eyebrow">Pernyataan Visi</p><h2>Visi Perusahaan</h2><p>Tujuan aspiratif jangka panjang yang menjadi dasar arah perusahaan.</p></div><span class="admin-status admin-status--{{ ($visionBlock->status ?? 'draft') === 'published' ? 'published' : 'draft' }}" data-editor-status-label>{{ ($visionBlock->status ?? 'draft') === 'published' ? 'Published' : 'Draft' }}</span></div>
        <div class="admin-editor__language" role="tablist" aria-label="Bahasa visi"><button type="button" class="is-active" data-language-tab="id">Bahasa Indonesia <span>*</span></button><button type="button" data-language-tab="en">Bahasa Inggris</button><button type="button" data-language-tab="zh">Mandarin</button></div>
        @foreach (['id' => ['Bahasa Indonesia', $visionTranslationId], 'en' => ['Bahasa Inggris', $visionTranslationEn], 'zh' => ['Mandarin', $visionTranslationZh ?? null]] as $language => [$languageLabel, $translation])
            <div class="admin-language-panel {{ $language !== 'id' ? 'is-hidden' : '' }}" data-language-panel="{{ $language }}"><div class="admin-editor__fields admin-vision-fields">
                <label class="admin-field"><span>Judul Pernyataan Visi <b>*</b></span><input type="text" name="translations[{{ $language }}][title]" value="{{ $translation?->title }}" maxlength="120" data-preview-field="title" @required($language === 'id')></label>
                <label class="admin-field"><span>Teks Pernyataan Visi <b>*</b></span><textarea name="translations[{{ $language }}][body]" maxlength="500" rows="4" data-preview-field="body" @required($language === 'id')>{{ $translation?->body }}</textarea></label>
            </div></div>
        @endforeach
        <div class="admin-editor__publish"><div><strong>Status Penayangan Visi <b>*</b></strong><small>Atur apakah pernyataan visi muncul pada halaman publik.</small></div><div class="admin-editor__status-options"><button type="button" data-set-status="published" class="{{ ($visionBlock->status ?? 'draft') === 'published' ? 'is-selected' : '' }}"><span class="admin-status-dot admin-status-dot--published"></span><span><b>Published (Tayang di Web)</b><small>Terlihat oleh pengunjung.</small></span></button><button type="button" data-set-status="draft" class="{{ ($visionBlock->status ?? 'draft') !== 'published' ? 'is-selected' : '' }}"><span class="admin-status-dot admin-status-dot--draft"></span><span><b>Draft</b><small>Hanya disimpan di CMS.</small></span></button></div></div>
        <div class="admin-form__error" data-form-errors hidden></div>
        <div class="admin-editor__actions"><button class="admin-button" type="reset">Reset</button><button class="admin-button admin-button--primary" type="submit"><span class="material-symbols-rounded" aria-hidden="true">save</span>Simpan Pernyataan Visi</button></div>
    </form>

    <section class="admin-panel admin-mission-list">
        <div class="admin-mission-list__heading"><div><h2>Daftar Butir Misi</h2><p>Kelola butir misi perusahaan dalam Bahasa Indonesia dan Bahasa Inggris.</p></div><a class="admin-button admin-button--primary" href="{{ route('admin.content.create', 'mission-items') }}"><span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah Poin Misi</a></div>
        <form class="admin-brand-manager__filters" method="GET" action="{{ route('admin.content.index', 'mission-items') }}">
            <label for="mission-status-filter">Status<select id="mission-status-filter" name="status"><option value="">Semua Status</option>@foreach (['published' => 'Published', 'draft' => 'Draft'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label class="admin-brand-manager__search" for="mission-search">Cari poin misi<span class="admin-brand-manager__search-control"><span class="material-symbols-rounded" aria-hidden="true">search</span><input id="mission-search" type="search" name="q" value="{{ request('q') }}" placeholder="Cari isi poin misi..."></span></label>
            <label>Baris <select name="per_page">@foreach ([10, 25, 50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>@endforeach</select></label>
            <div class="admin-filter-actions"><button class="admin-button admin-button--primary" type="submit">Cari</button><a class="admin-button" href="{{ route('admin.content.index', 'mission-items') }}">Reset</a></div>
        </form>
        <div class="admin-table-wrap" data-reorder-list="{{ implode(',', $missionIds) }}" data-reorder-resource="mission-items"><table class="admin-table admin-mission-table"><thead><tr><th>Aksi</th><th>Urutan</th><th>Isi Butir Misi (Bahasa Indonesia)</th><th>Isi Butir Misi (Bahasa Inggris)</th><th>Status</th><th>Terakhir Diperbarui</th></tr></thead><tbody>
            @forelse ($records as $record)
                @php($translationId = $record->translations->firstWhere('lang', 'id'))
                @php($translationEn = $record->translations->firstWhere('lang', 'en'))
                <tr data-reorder-id="{{ $record->id }}"><td><div class="admin-brand-table__actions"><a class="admin-icon-button" href="{{ route('admin.content.edit', ['resource' => 'mission-items', 'id' => $record->id]) }}" aria-label="Edit poin misi" title="Edit"><span class="material-symbols-rounded" aria-hidden="true">edit</span></a><button class="admin-icon-button" type="button" aria-label="Ubah status poin misi" title="Ubah status" data-status-url="{{ url('/api/v1/admin/mission-items/'.$record->id.'/status') }}" data-next-status="{{ $record->status === 'published' ? 'draft' : 'published' }}"><span class="material-symbols-rounded" aria-hidden="true">{{ $record->status === 'published' ? 'visibility_off' : 'visibility' }}</span></button><button class="admin-icon-button is-danger" type="button" aria-label="Hapus poin misi" title="Hapus" data-delete-url="{{ url('/api/v1/admin/mission-items/'.$record->id) }}"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></div></td><td>@include('admin.partials.order-controls', ['orderIds' => $missionIds])</td><td><strong>{{ $translationId?->text ?: 'Belum ada isi Bahasa Indonesia' }}</strong></td><td>{{ $translationEn?->text ?: 'Belum ada terjemahan' }}</td><td><span class="admin-status admin-status--{{ $record->status === 'published' ? 'active' : ($record->status === 'draft' ? 'draft' : 'inactive') }}"><i></i>{{ $record->status === 'published' ? 'Active' : ucfirst($record->status) }}</span></td><td>{{ $record->updated_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
            @empty
                <tr><td class="admin-empty" colspan="6">Belum ada poin misi yang sesuai dengan filter.</td></tr>
            @endforelse
        </tbody></table></div>
        @include('admin.partials.pagination', ['paginator' => $records, 'label' => 'butir misi'])
    </section>
@endsection
