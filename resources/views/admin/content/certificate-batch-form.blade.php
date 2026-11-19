@extends('admin.layout')
@section('title', 'Tambah Sertifikat & Penghargaan')
@section('page-heading', 'Tambah Sertifikat & Penghargaan')
@section('breadcrumb', 'Konten Landing Page  /  Tentang  /  Sertifikat  /  Tambah')
@section('page-actions')<a class="admin-button admin-button--back" href="{{ $redirectUrl }}"><span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>Kembali ke daftar</a>@endsection
@section('content')
    <div class="admin-certificate-create-page">
        <section class="admin-certificate-batch-dialog admin-certificate-batch-page" aria-labelledby="certificate-batch-title">
            <header class="admin-certificate-batch-header">
                <div class="admin-certificate-batch-heading-icon"><span class="material-symbols-rounded" aria-hidden="true">workspace_premium</span></div>
                <div>
                    <h1 id="certificate-batch-title">Tambah Sertifikat &amp; Penghargaan</h1>
                    <p>Isi informasi dan pilih gambar. Anda juga dapat menambahkan beberapa sertifikat sekaligus.</p>
                </div>
            </header>

            <div class="admin-certificate-batch-toolbar">
                <div class="admin-certificate-batch-tabs" data-certificate-entry-tabs></div>
                <button class="admin-button admin-button--outline" type="button" data-add-certificate-entry><span class="material-symbols-rounded" aria-hidden="true">add</span>Tambah Sertifikat Lainnya</button>
                <span class="admin-certificate-batch-count" data-certificate-entry-count>0 item</span>
            </div>

            <form class="admin-certificate-batch-form" data-certificate-batch data-certificate-preview-url="{{ route('admin.content.preview-unsaved', ['resource' => 'certificates']) }}" data-api-url="{{ $apiUrl }}" data-redirect-url="{{ $redirectUrl }}">
                @csrf
                <div class="admin-certificate-batch-list" data-certificate-entry-list></div>
                <div class="admin-form__error admin-certificate-batch-error" data-certificate-batch-error hidden></div>
                <footer class="admin-certificate-batch-footer">
                    <p><span class="material-symbols-rounded" aria-hidden="true">inventory_2</span><span><strong data-certificate-entry-total>0 Sertifikat</strong><small>Siap disimpan sebagai draft atau dipublikasikan</small></span></p>
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
                    <button type="button" data-batch-language="en">English</button>
                    <button type="button" data-batch-language="zh">中文</button>
                </div>
                <div class="admin-certificate-entry__upload">
                    <label class="admin-field">
                        <span>Gambar Sertifikat <b>*</b></span>
                        <select data-certificate-field="media_id" data-media-preview-select>
                            <option value="" data-image-url="">Pilih gambar dari Media Library</option>
                            @foreach ($mediaOptions as $media)
                                <option value="{{ $media->id }}" data-image-url="{{ $media->admin_preview_url }}">{{ $media->original_name }}</option>
                            @endforeach
                        </select>
                        <span class="admin-certificate-upload__preview" data-media-preview><span class="material-symbols-rounded" aria-hidden="true">image</span><span>Pratinjau gambar akan muncul di sini</span></span>
                    </label>
                </div>
                <div class="admin-certificate-entry__language-panel" data-batch-language-panel="id">
                    <label class="admin-field"><span>Judul Sertifikat / Penghargaan <b>*</b></span><input type="text" data-certificate-field="translations[id][title]" maxlength="150" data-certificate-title placeholder="Masukkan judul"></label>
                    <label class="admin-field"><span>Keterangan</span><textarea data-certificate-field="translations[id][description]" maxlength="300" placeholder="Keterangan singkat (opsional)"></textarea></label>
                </div>
                <div class="admin-certificate-entry__language-panel is-hidden" data-batch-language-panel="en">
                    <label class="admin-field"><span>Certificate / Award Title</span><input type="text" data-certificate-field="translations[en][title]" maxlength="150" placeholder="Enter title"></label>
                    <label class="admin-field"><span>Description</span><textarea data-certificate-field="translations[en][description]" maxlength="300" placeholder="Short description (optional)"></textarea></label>
                </div>
                <div class="admin-certificate-entry__language-panel is-hidden" data-batch-language-panel="zh">
                    <label class="admin-field"><span>证书 / 奖项名称</span><input type="text" data-certificate-field="translations[zh][title]" maxlength="150" placeholder="输入标题"></label>
                    <label class="admin-field"><span>说明</span><textarea data-certificate-field="translations[zh][description]" maxlength="300" placeholder="简短说明"></textarea></label>
                </div>
                <div class="admin-certificate-entry__fields">
                    <label class="admin-field"><span>Tahun Terbit</span><input type="number" data-certificate-field="issued_year" min="1900" max="{{ now()->year }}" placeholder="{{ now()->year }}"></label>
                    <label class="admin-field"><span>Urutan Tampilan</span><input type="number" data-certificate-field="display_order" min="1" placeholder="1"></label>
                </div>
            </div>
        </article>
    </template>
@endsection
