<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dokumentasi artikel dan berita seputar kesehatan dari IMI Medika.">
    <title>Artikel &amp; Berita - Interskala Medika Indonesia</title>
    @vite(['resources/css/app.css', 'resources/css/documentation.css', 'resources/js/app.js'])
</head>
<body class="documentation-page">
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
                <a class="nav-link nav-link--active" href="{{ route('documentation') }}" aria-current="page" data-i18n="news">Artikel &amp; Berita</a>

                <div class="language-control">
                    <span class="google-mark" aria-hidden="true">G</span>
                    <label class="sr-only" for="language-picker">Pilih bahasa</label>
                    <select id="language-picker" aria-label="Pilih bahasa">
                        <option value="" selected>Select Language</option>
                        <option value="id">Bahasa Indonesia</option>
                        <option value="en">English</option>
                    </select>
                    <span class="language-control__arrow" aria-hidden="true">▼</span>
                </div>
            </nav>
        </div>
    </header>

    <main class="documentation-main">
        <section class="documentation-hero" aria-labelledby="documentation-title">
            <div class="container documentation-hero__content">
                <h1 id="documentation-title" data-i18n="documentationTitle">DOKUMENTASI</h1>
                <p data-i18n="documentationIntro">Temukan artikel dan berita terkini seputar dunia kesehatan</p>
                <a href="#content" data-i18n="exploreNow">JELAJAHI SEKARANG</a>
            </div>
        </section>

        <section class="documentation-content" id="content" aria-labelledby="media-heading">
            <div class="container">
                <div class="documentation-heading">
                    <h2 id="media-heading" data-i18n="mediaHeading">IMI MEDIKA MEDIA</h2>
                    <span aria-hidden="true"></span>
                </div>

                <div class="media-grid">
                    <a class="media-card" href="{{ route('articles') }}" aria-label="Lihat artikel selengkapnya">
                        <svg viewBox="0 0 64 64" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M13 4h28l11 11v45H13V4Zm28 2v10h10L41 6ZM21 28h23v3H21v-3Zm0 8h23v3H21v-3Zm0 8h17v3H21v-3Z"/></svg>
                        <h3 data-i18n="articleCardTitle">Artikel</h3>
                        <span data-i18n="readMore">Selengkapnya</span>
                    </a>
                    <a class="media-card" href="{{ route('news') }}" aria-label="Lihat berita selengkapnya">
                        <svg viewBox="0 0 64 64" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M9 8h46v43a5 5 0 0 1-5 5H14a5 5 0 0 1-5-5V8Zm8 8h18v17H17V16Zm23 1h9v3h-9v-3Zm0 7h9v3h-9v-3ZM17 39h32v3H17v-3Zm0 7h32v3H17v-3Z"/></svg>
                        <h3 data-i18n="newsCardTitle">Berita</h3>
                        <span data-i18n="readMore">Selengkapnya</span>
                    </a>
                </div>

                <div class="media-stats" aria-label="Statistik artikel dan berita">
                    <div><strong>30</strong><span data-i18n="articlesAvailable">Artikel Tersedia</span></div>
                    <div><strong>30</strong><span data-i18n="latestNews">Berita Terbaru</span></div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <p>© 2026 PT Interskala Medika Indonesia | Member of Interskala Group</p>
    </footer>
</body>
</html>
