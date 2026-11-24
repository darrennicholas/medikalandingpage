<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <meta name="description" content="Form tambah artikel kesehatan IMI Medika.">
    <title>Tambah Artikel - Interskala Medika Indonesia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/documentation.css', 'resources/js/app.js'])
</head>
<body class="documentation-page media-list-page article-create-page">
    <header class="site-header" id="site-header">
        <div class="site-header__inner">
            <a class="site-logo" href="{{ route('home') }}" aria-label="PT Interskala Medika Indonesia, beranda">
                <img src="{{ asset('images/medika-logo.png') }}" alt="IMI Medika" width="120" height="72">
            </a>

            <button class="menu-toggle" id="menu-toggle" type="button" aria-controls="primary-navigation" aria-expanded="false" aria-label="Buka menu">
                <span></span><span></span><span></span>
            </button>

            <nav class="primary-navigation" id="primary-navigation" aria-label="Navigasi utama">
                <a class="nav-link" href="{{ route('home') }}" data-i18n="home">Beranda</a>
                <a class="nav-link" href="{{ route('about') }}" data-i18n="about">Tentang</a>
                <a class="nav-link" href="{{ route('products') }}" data-i18n="products">Produk</a>
                <a class="nav-link" href="https://imimedika.com/karir.php" data-i18n="careers">Karir</a>
                <a class="nav-link" href="{{ route('contact') }}" data-i18n="contact">Kontak</a>
                <a class="nav-link nav-link--active" href="{{ route('documentation') }}" data-i18n="articlesNews">Artikel &amp; Berita</a>

                @include('partials.language-switcher')
            </nav>
        </div>
    </header>

    <main class="article-create-main">
        <div class="article-create-main__band" aria-hidden="true"></div>
        <form class="article-form article-create-form" id="article-create-form" data-list-url="{{ route('articles') }}">
            <div class="article-form__header">
                <h1 id="article-form-title">Tambah Berita / Artikel Interskala Medika Indonesia</h1>
            </div>

            <label class="article-form__field">
                <span>Kategori</span>
                <select name="category" required>
                    <option value="" disabled selected>Pilih Kategori</option>
                    <option>Edukasi</option>
                    <option>Tips Kesehatan</option>
                    <option>Gaya Hidup</option>
                </select>
            </label>

            <label class="article-form__field">
                <span>Judul</span>
                <input name="title" type="text" placeholder="Judul artikel" required>
            </label>

            <label class="article-form__field">
                <span>Penulis</span>
                <input name="author" type="text" placeholder="Nama penulis" required>
            </label>

            <div class="article-form__row">
                <label class="article-form__field">
                    <span>Tanggal</span>
                    <input name="date" type="date" required>
                </label>
                <label class="article-form__field">
                    <span>Custom URL Slug / Permalink</span>
                    <input name="slug" type="text" placeholder="judul-artikel-anda">
                </label>
            </div>

            <label class="article-form__field">
                <span>Kategori &amp; Tags</span>
                <input name="tags" type="text" placeholder="Kesehatan, edukasi">
            </label>

            <label class="article-form__field">
                <span>Isi Artikel</span>
                <div class="article-editor">
                    <div class="article-editor__toolbar" role="toolbar" aria-label="Format isi artikel">
                        <button type="button" data-editor-prefix="# " aria-label="Judul">H1</button>
                        <button type="button" data-editor-wrap="**" aria-label="Tebal"><strong>B</strong></button>
                        <button type="button" data-editor-wrap="*" aria-label="Miring"><em>I</em></button>
                        <button type="button" data-editor-wrap="__" aria-label="Garis bawah"><u>U</u></button>
                        <button type="button" data-editor-prefix="• " aria-label="Daftar berpoin">•</button>
                        <button type="button" data-editor-prefix="1. " aria-label="Daftar bernomor">1.</button>
                        <button type="button" data-editor-wrap="`" aria-label="Kode">&lt;/&gt;</button>
                    </div>
                    <textarea name="content" rows="6" placeholder="Tulis isi artikel di sini..." required></textarea>
                </div>
            </label>

            <div class="article-form__row">
                <label class="article-form__field">
                    <span>Gambar Cover</span>
                    <input name="cover" type="file" accept="image/*">
                </label>
                <label class="article-form__field">
                    <span>Alt text gambar</span>
                    <input name="coverAlt" type="text" placeholder="Deskripsi gambar">
                </label>
            </div>

            <p class="article-form__status" id="article-form-status" role="status"></p>
            <button class="article-form__save" type="submit">Simpan</button>
            <a class="article-form__dashboard" href="{{ route('articles') }}">← Kembali ke Dashboard</a>
        </form>
    </main>
</body>
</html>
