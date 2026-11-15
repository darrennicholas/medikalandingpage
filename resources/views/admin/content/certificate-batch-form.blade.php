@extends('admin.layout')
@section('title', 'Tambah Sertifikat & Penghargaan')
@section('page-heading', 'Sertifikat & Penghargaan')
@section('breadcrumb', 'Konten Landing Page  /  Tentang  /  Sertifikat')
@section('content')
    <div class="admin-certificate-modal-backdrop">
        <section class="admin-certificate-batch-dialog" aria-labelledby="certificate-batch-title">
            <header class="admin-certificate-batch-header">
                <div class="admin-certificate-batch-heading-icon"><span class="material-symbols-rounded" aria-hidden="true">workspace_premium</span></div>
                <div>
                    <h1 id="certificate-batch-title">Tambah Sertifikat &amp; Penghargaan</h1>
                    <p>Lengkapi beberapa sertifikat dalam satu kali pengisian.</p>
                </div>
                <a class="admin-certificate-batch-close" href="{{ $redirectUrl }}" aria-label="Tutup"><span class="material-symbols-rounded" aria-hidden="true">close</span></a>
            </header>

            <div class="admin-certificate-batch-toolbar">
                <div class="admin-certificate-batch-tabs" data-certificate-entry-tabs></div>
                <button class="admin-button admin-button--outline" type="button" data-add-certificate-entry><span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah Sertifikat Lainnya</button>
                <span class="admin-certificate-batch-count" data-certificate-entry-count>0 item</span>
            </div>

            <form class="admin-certificate-batch-form" data-certificate-batch data-api-url="{{ $apiUrl }}" data-redirect-url="{{ $redirectUrl }}">
                @csrf
                <div class="admin-certificate-batch-list" data-certificate-entry-list></div>
                <div class="admin-form__error admin-certificate-batch-error" data-certificate-batch-error hidden></div>
                <footer class="admin-certificate-batch-footer">
                    <p><span class="material-symbols-rounded" aria-hidden="true">inventory_2</span>Total: <strong data-certificate-entry-total>0 Sertifikat</strong> dalam antrean</p>
                    <div>
                        <a class="admin-button" href="{{ $redirectUrl }}">Batal</a>
                        <button class="admin-button" type="submit" data-certificate-batch-status="draft">Simpan Semua Draft</button>
                        <button class="admin-button admin-button--primary" type="submit" data-certificate-batch-status="published"><span class="material-symbols-rounded" aria-hidden="true">publish</span>Simpan &amp; Publish Semua</button>
                    </div>
                </footer>
            </form>
        </section>
    </div>

    <template data-certificate-entry-template>
        <article class="admin-certificate-entry" data-certificate-entry>
            <header class="admin-certificate-entry__header">
                <button class="admin-certificate-entry__toggle" type="button" data-certificate-entry-toggle aria-expanded="true">
                    <span class="admin-certificate-entry__number" data-certificate-entry-number>1</span>
                    <strong>Sertifikat <span data-certificate-entry-label>#1</span>: <span data-certificate-entry-title>(Belum diberi judul)</span></strong>
                    <span class="admin-status admin-status--draft" data-certificate-entry-state>Draft</span>
                    <span class="material-symbols-rounded" aria-hidden="true">expand_less</span>
                </button>
                <button class="admin-certificate-entry__remove" type="button" data-remove-certificate-entry aria-label="Hapus item"><span class="material-symbols-rounded" aria-hidden="true">delete</span>Hapus</button>
            </header>
            <div class="admin-certificate-entry__body" data-certificate-entry-body>
                <div class="admin-certificate-entry__languages" role="tablist" aria-label="Bahasa sertifikat">
                    <button class="is-active" type="button" data-batch-language="id">Bahasa Indonesia <span>*</span></button>
                    <button type="button" data-batch-language="en">English <small>Opsional</small></button>
                </div>
                <div class="admin-certificate-entry__upload">
                    <label class="admin-field">
                        <span>Gambar / Dokumen Sertifikat</span>
                        <select data-certificate-field="media_id" data-media-preview-select>
                            <option value="" data-image-url="">Pilih media sertifikat</option>
                            @foreach ($mediaOptions as $media)
                                <option value="{{ $media->id }}" data-image-url="{{ $media->admin_preview_url }}">{{ $media->original_name }}</option>
                            @endforeach
                        </select>
                        <span class="admin-certificate-upload__preview" data-media-preview><span class="material-symbols-rounded" aria-hidden="true">description</span><span>Pilih dokumen sertifikat</span></span>
                    </label>
                    <a class="admin-button" href="{{ route('admin.media.index') }}"><span class="material-symbols-rounded" aria-hidden="true">photo_library</span>Media Library</a>
                </div>
                <div class="admin-certificate-entry__language-panel" data-batch-language-panel="id">
                    <label class="admin-field"><span>Judul Sertifikat / Penghargaan <b>*</b></span><input type="text" data-certificate-field="translations[id][title]" maxlength="150" data-certificate-title placeholder="Masukkan judul"></label>
                    <label class="admin-field"><span>Keterangan</span><textarea data-certificate-field="translations[id][description]" maxlength="300" placeholder="Keterangan singkat (opsional)"></textarea></label>
                </div>
                <div class="admin-certificate-entry__language-panel is-hidden" data-batch-language-panel="en">
                    <label class="admin-field"><span>Certificate / Award Title</span><input type="text" data-certificate-field="translations[en][title]" maxlength="150" placeholder="Enter title"></label>
                    <label class="admin-field"><span>Description</span><textarea data-certificate-field="translations[en][description]" maxlength="300" placeholder="Short description (optional)"></textarea></label>
                </div>
                <div class="admin-certificate-entry__fields">
                    <label class="admin-field"><span>Tahun Terbit</span><input type="number" data-certificate-field="issued_year" min="1900" max="{{ now()->year }}" placeholder="{{ now()->year }}"></label>
                    <label class="admin-field"><span>Urutan Tampilan</span><input type="number" data-certificate-field="display_order" min="1" placeholder="1"></label>
                </div>
            </div>
        </article>
    </template>
@endsection
