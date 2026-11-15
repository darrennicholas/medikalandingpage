@extends('admin.layout')
@section('title', 'Pengaturan SEO')
@section('page-heading', 'Pengaturan SEO')
@section('breadcrumb', 'CMS Landing  /  Media & SEO  /  Pengaturan SEO')
@section('page-description', 'Kelola metadata pencarian untuk halaman utama landing page perusahaan.')
@section('page-actions')<a class="admin-button" href="{{ route('sitemap') }}" target="_blank" rel="noopener"><span class="material-symbols-rounded" aria-hidden="true">map</span>Lihat Sitemap</a>@endsection
@section('content')
<section class="admin-feature-stats admin-seo-stats" aria-label="Ringkasan SEO">
    <article><span>Halaman Terdaftar</span><strong>{{ count($pageLabels) }}</strong><small>Halaman inti landing page</small></article>
    <article class="is-green"><span>Index Aktif</span><strong>{{ $seoIndexed }}</strong><small>Konfigurasi mengizinkan indeks</small></article>
    <article><span>Metadata Lengkap</span><strong>{{ $seoComplete }}</strong><small>Memiliki title dan description</small></article>
</section>

<section class="admin-panel admin-seo-list-panel">
    <div class="admin-panel__header"><div><p class="admin-eyebrow">Metadata per halaman dan bahasa</p><h2>Daftar Halaman SEO <span class="admin-count-badge">{{ $seoTotal }} konfigurasi</span></h2></div></div>
    <form class="admin-toolbar" method="GET" action="{{ route('admin.content.index', 'seo-metas') }}">
        <label class="admin-toolbar__search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari halaman atau meta title..."></label><button class="admin-button" type="submit">Cari</button><a class="admin-button" href="{{ route('admin.content.index', 'seo-metas') }}">Reset</a>
    </form>
    <div class="admin-table-wrap"><table class="admin-table admin-seo-table"><thead><tr><th>Aksi</th><th>Halaman &amp; Route</th><th>Bahasa</th><th>Meta Title</th><th>Meta Description</th><th>Robot Index</th><th>Kelengkapan</th></tr></thead><tbody>
        @forelse ($records as $record)
            @php($complete = filled($record->meta_title) && filled($record->meta_description))
            <tr class="{{ $record->page_key === $selectedPage && $record->lang === $selectedLanguage ? 'is-selected' : '' }}"><td><a class="admin-icon-button" href="{{ route('admin.content.index', ['resource' => 'seo-metas', 'page' => $record->page_key, 'lang' => $record->lang]) }}" aria-label="Ubah SEO {{ $pageLabels[$record->page_key] ?? $record->page_key }} {{ strtoupper($record->lang) }}" title="Ubah"><span class="material-symbols-rounded" aria-hidden="true">edit</span></a></td><td><strong>{{ $pageLabels[$record->page_key] ?? ucfirst($record->page_key) }}</strong><small>{{ $record->page_key === 'home' ? '/' : '/'.($record->page_key === 'about' ? 'tentang' : $record->page_key) }}</small></td><td><span class="admin-language-badge">{{ strtoupper($record->lang) }}</span></td><td>{{ $record->meta_title ?: 'Belum diisi' }}</td><td>{{ $record->meta_description ?: 'Belum diisi' }}</td><td><span class="admin-status admin-status--{{ $record->robots_index ? 'published' : 'draft' }}">{{ $record->robots_index ? 'Index' : 'Noindex' }}</span></td><td><span class="admin-status admin-status--{{ $complete ? 'published' : 'unpublished' }}">{{ $complete ? 'Lengkap' : 'Belum lengkap' }}</span></td></tr>
        @empty<tr><td colspan="7" class="admin-empty">Belum ada metadata SEO. Pilih halaman di bawah untuk membuat konfigurasi pertamanya.</td></tr>@endforelse
    </tbody></table></div>
</section>

<section class="admin-panel admin-seo-editor">
    <div class="admin-seo-editor__heading"><div><span class="admin-seo-editor__icon material-symbols-rounded" aria-hidden="true">tune</span><div><p class="admin-eyebrow">Edit konfigurasi SEO</p><h2>{{ $pageLabels[$selectedPage] }} <span>({{ $selectedPage === 'home' ? '/' : '/'.($selectedPage === 'about' ? 'tentang' : $selectedPage) }})</span></h2></div></div><div class="admin-seo-editor__switches"><label>Target Halaman<select data-seo-page-select>@foreach ($pageLabels as $key => $label)<option value="{{ $key }}" @selected($selectedPage === $key)>{{ $label }}</option>@endforeach</select></label><div class="admin-language-tabs admin-language-tabs--compact"><a class="{{ $selectedLanguage === 'id' ? 'is-active' : '' }}" href="{{ route('admin.content.index', ['resource' => 'seo-metas', 'page' => $selectedPage, 'lang' => 'id']) }}">ID · Indonesia</a><a class="{{ $selectedLanguage === 'en' ? 'is-active' : '' }}" href="{{ route('admin.content.index', ['resource' => 'seo-metas', 'page' => $selectedPage, 'lang' => 'en']) }}">EN · English</a></div></div></div>
    <div class="admin-info-callout"><span class="material-symbols-rounded" aria-hidden="true">lightbulb</span><span>Title dan deskripsi yang kosong dapat dibuat otomatis dari konten publik halaman terkait.</span></div>
    <form class="admin-form admin-seo-form" data-admin-form data-resource="seo-metas" data-api-url="/api/v1/admin/seo-metas" data-is-editing="1" data-redirect-url="{{ route('admin.content.index', ['resource' => 'seo-metas', 'page' => $selectedPage, 'lang' => $selectedLanguage]) }}">
        @csrf<input type="hidden" name="page_key" value="{{ $selectedPage }}"><input type="hidden" name="lang" value="{{ $selectedLanguage }}"><input type="hidden" name="status" value="published"><input type="hidden" name="robots_index" value="0">
        <div class="admin-seo-form__fields">
            <label class="admin-field"><span>Meta Title <small>{{ $selectedLanguage === 'id' ? 'Judul halaman SEO' : 'SEO page title' }}</small></span><input name="meta_title" maxlength="70" value="{{ old('meta_title', $selectedMeta->meta_title) }}" placeholder="Maksimal 70 karakter"><small class="admin-seo-counter" data-count-for="meta_title">{{ mb_strlen((string) $selectedMeta->meta_title) }}/70 karakter</small></label>
            <label class="admin-field"><span>Meta Description <small>{{ $selectedLanguage === 'id' ? 'Ringkasan hasil pencarian' : 'Search result summary' }}</small></span><textarea name="meta_description" maxlength="160" placeholder="Tulis ringkasan yang jelas dan relevan...">{{ old('meta_description', $selectedMeta->meta_description) }}</textarea><small class="admin-seo-counter" data-count-for="meta_description">{{ mb_strlen((string) $selectedMeta->meta_description) }}/160 karakter</small></label>
            <label class="admin-field"><span>Canonical URL</span><input type="url" name="canonical_url" maxlength="500" value="{{ old('canonical_url', $selectedMeta->canonical_url) }}" placeholder="https://contoh.co.id/halaman"><small>Opsional, untuk menetapkan URL kanonis halaman ini.</small></label>
            <label class="admin-field"><span>Gambar Open Graph</span><select name="og_media_id"><option value="">Gunakan gambar bawaan halaman</option>@foreach ($mediaOptions as $media)<option value="{{ $media->id }}" @selected((string) old('og_media_id', $selectedMeta->og_media_id) === (string) $media->id)>{{ $media->original_name }}</option>@endforeach</select><small>Gambar yang dipakai saat halaman dibagikan ke sosial media.</small></label>
            <label class="admin-seo-index-toggle"><span><strong>Izinkan indeks mesin pencari</strong><small>Nonaktifkan untuk menambahkan noindex pada halaman.</small></span><input type="checkbox" name="robots_index" value="1" @checked(old('robots_index', $selectedMeta->robots_index ?? true))><i aria-hidden="true"></i></label>
        </div>
        <aside class="admin-seo-preview"><p class="admin-eyebrow">Pratinjau hasil pencarian</p><article><small>PT Interskala Medika Indonesia</small><a data-seo-preview-title>{{ $selectedMeta->meta_title ?: $pageLabels[$selectedPage].' | PT Interskala Medika Indonesia' }}</a><p data-seo-preview-description>{{ $selectedMeta->meta_description ?: 'Informasi resmi PT Interskala Medika Indonesia.' }}</p></article></aside>
        <div class="admin-form__error" data-form-errors hidden></div>
        <div class="admin-form__actions"><a class="admin-button" href="{{ route('admin.content.index', 'seo-metas') }}">Batal</a><button class="admin-button admin-button--primary" type="submit"><span class="material-symbols-rounded" aria-hidden="true">verified</span>Simpan &amp; Terapkan Perubahan</button></div>
    </form>
</section>
@endsection
