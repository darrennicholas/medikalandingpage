@extends('admin.layout')
@section('title', 'Sosial Media & Footer')
@section('page-heading', 'Sosial Media & Footer')
@section('breadcrumb', 'CMS Landing  /  Kontak & Footer  /  Sosial Media & Footer')
@section('page-description', 'Kelola ringkasan perusahaan, hak cipta, dan tautan sosial yang tampil di footer landing page.')
@section('page-actions')
    <a class="admin-button admin-button--primary" href="{{ route('admin.content.create', 'social-links') }}"><span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah Akun Sosial</a>
@endsection
@section('content')
<section class="admin-feature-stats" aria-label="Ringkasan sosial media">
    <article class="admin-summary-card"><span>Total Akun Sosial</span><strong>{{ $socialTotal }}</strong><small>Kanal tersimpan di CMS</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">share</span></article>
    <article class="admin-summary-card admin-summary-card--success"><span>Tampil di Footer</span><strong>{{ $socialActive }}</strong><small>Akun dengan status published</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">visibility</span></article>
    <article class="admin-summary-card"><span>Bahasa Footer</span><strong>3</strong><small>Indonesia, Inggris, dan Mandarin</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">translate</span></article>
</section>

<section class="admin-panel admin-social-footer-panel">
    <div class="admin-panel__header"><div><p class="admin-eyebrow">Teks yang tampil pada bagian bawah landing page</p><h2>Teks Penutup &amp; Hak Cipta</h2></div><span class="admin-status admin-status--published">Tersambung ke footer</span></div>
    <div class="admin-info-callout"><span class="material-symbols-rounded" aria-hidden="true">info</span><span>Informasi kontak pada footer diambil otomatis dari menu <a href="{{ route('admin.content.index', 'contact-info') }}">Informasi Kontak</a>.</span></div>
    <form class="admin-form admin-footer-form" data-admin-form data-resource="content-blocks" data-api-url="/api/v1/admin/content-blocks/footer_about" data-is-editing="1" data-redirect-url="{{ route('admin.content.index', 'social-links') }}">
        @include('admin.partials.record-version', ['record' => $footerBlock])
        @csrf
        <input type="hidden" name="block_key" value="footer_about">
        <label class="admin-field"><span>Status Publikasi <b>*</b></span><select name="status" required><option value="published" @selected(($footerBlock->status ?? 'published') === 'published')>Published (Tampil di footer)</option><option value="draft" @selected(($footerBlock->status ?? 'published') !== 'published')>Draft (Tidak ditampilkan)</option></select><small>Draft tersimpan di CMS dan tidak ditampilkan pada landing page.</small></label>
        <fieldset class="admin-footer-visibility">
            <legend>Tampilan kontak di footer</legend>
            <p>Pilih informasi yang tampil di bagian bawah website. Pengaturan ini tidak mengubah halaman Kontak.</p>
            <div class="admin-footer-visibility__options">
                <label><input type="hidden" name="footer_show_phone" value="0"><input type="checkbox" name="footer_show_phone" value="1" @checked(old('footer_show_phone', $footerBlock->footer_show_phone ?? true))><span><strong>Nomor telepon</strong><small>Tampilkan nomor telepon di footer</small></span></label>
                <label><input type="hidden" name="footer_show_email" value="0"><input type="checkbox" name="footer_show_email" value="1" @checked(old('footer_show_email', $footerBlock->footer_show_email ?? true))><span><strong>Email</strong><small>Tampilkan alamat email di footer</small></span></label>
            </div>
        </fieldset>
        <div class="admin-language-tabs" role="tablist"><button type="button" class="is-active" data-language-tab="id">Bahasa Indonesia</button><button type="button" data-language-tab="en">Bahasa Inggris</button><button type="button" data-language-tab="zh">Mandarin</button></div>
        @foreach (['id' => 'Ringkasan perusahaan', 'en' => 'Ringkasan perusahaan', 'zh' => 'Ringkasan perusahaan'] as $language => $label)
            @php
                $translation = $footerTranslations->get($language);
                $defaultCopyright = '© '.now()->year.' PT Interskala Medika Indonesia | Member of Interskala Group';
                $copyrightText = $translation?->copyright_text ?: ($language === 'en' ? $footerTranslations->get('id')?->copyright_text : null) ?: ($language === 'zh' ? null : $defaultCopyright);
            @endphp
            <div class="admin-language-panel admin-footer-language-panel {{ $language !== 'id' ? 'is-hidden' : '' }}" data-language-panel="{{ $language }}">
                <label class="admin-field"><span>{{ $label }} <small>(maks. 300 karakter)</small></span><textarea name="translations[{{ $language }}][body]" maxlength="300">{{ old("translations.$language.body", $translation?->body) }}</textarea></label>
                <label class="admin-field"><span>Teks Hak Cipta <small>(maks. 150 karakter)</small></span><input name="translations[{{ $language }}][copyright_text]" maxlength="150" value="{{ old("translations.$language.copyright_text", $copyrightText) }}"></label>
            </div>
        @endforeach
        <div class="admin-form__error" data-form-errors hidden></div>
        <div class="admin-form__actions">@if ($footerBlock->exists)<a class="admin-button" href="{{ route('admin.content.preview', ['resource' => 'content-blocks', 'id' => 'footer_about']) }}" target="_blank" rel="noopener">Pratinjau Footer</a>@endif<button class="admin-button admin-button--primary" type="submit"><span class="material-symbols-rounded" aria-hidden="true">save</span>Simpan Pengaturan Footer</button></div>
    </form>
</section>

<section class="admin-panel admin-social-list-panel">
    <div class="admin-panel__header"><div><p class="admin-eyebrow">Kanal media sosial resmi</p><h2>Tautan Sosial Media <span class="admin-count-badge">{{ $socialTotal }} kanal</span></h2><p>Urutan dan status menentukan akun yang ditampilkan pada landing page.</p></div></div>
    <form class="admin-toolbar" method="GET" action="{{ route('admin.content.index', 'social-links') }}">
        <label>Status<select name="status"><option value="">Semua Status</option>@foreach (['published' => 'Published', 'draft' => 'Draft'] as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select></label>
        <label class="admin-toolbar__search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari platform atau URL..."></label>
        <label>Baris <select name="per_page">@foreach ([10, 25, 50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>@endforeach</select></label>
        <div class="admin-filter-actions"><button class="admin-button admin-button--primary" type="submit">Cari</button><a class="admin-button" href="{{ route('admin.content.index', 'social-links') }}">Reset</a></div>
    </form>
    <div class="admin-table-wrap"><table class="admin-table admin-social-table"><thead><tr><th>Aksi</th><th>Urutan</th><th>Platform</th><th>URL / Tautan Resmi</th><th>Status</th></tr></thead><tbody>
        @forelse ($records as $record)
            <tr><td><div class="admin-actions"><a href="{{ route('admin.content.edit', ['resource' => 'social-links', 'id' => $record->id]) }}" aria-label="Ubah {{ $record->platform }}"><span class="material-symbols-rounded" aria-hidden="true">edit</span></a><button type="button" data-status-url="{{ url('/api/v1/admin/social-links/'.$record->id.'/status') }}" data-next-status="{{ $record->status === 'published' ? 'draft' : 'published' }}" aria-label="Ubah status"><span class="material-symbols-rounded" aria-hidden="true">{{ $record->status === 'published' ? 'visibility_off' : 'visibility' }}</span></button><button type="button" class="is-danger" data-delete-url="{{ url('/api/v1/admin/social-links/'.$record->id) }}" aria-label="Hapus {{ $record->platform }}"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></div></td><td><span class="admin-order-pill">#{{ $record->display_order }}</span></td><td><strong>{{ $record->platform }}</strong></td><td><a class="admin-social-url" href="{{ $record->url }}" target="_blank" rel="noopener noreferrer">{{ $record->url }} <span class="material-symbols-rounded" aria-hidden="true">open_in_new</span></a></td><td><span class="admin-status admin-status--{{ $record->status }}">{{ ucfirst($record->status) }}</span></td></tr>
        @empty<tr><td colspan="5" class="admin-empty">Belum ada tautan sosial untuk ditampilkan.</td></tr>@endforelse
    </tbody></table></div>
    @include('admin.partials.pagination', ['paginator' => $records, 'label' => 'tautan sosial'])
</section>
@endsection
        
