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
                <a class="nav-link nav-link--active" href="{{ route('documentation') }}" aria-current="page" data-i18n="articlesNews">Artikel &amp; Berita</a>

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

                <!-- Font Awesome Free icons by Fonticons, Inc. (CC BY 4.0): https://fontawesome.com/license/free -->
                <div class="media-grid">
                    <a class="media-card" href="{{ route('articles') }}" aria-label="Lihat artikel selengkapnya">
                        <svg viewBox="0 0 384 512" aria-hidden="true"><path fill="currentColor" d="M64 0C28.7 0 0 28.7 0 64v384c0 35.3 28.7 64 64 64h256c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64Zm192 0v128h128L256 0ZM112 256h160c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16Zm0 64h160c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16Zm0 64h160c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16Z"/></svg>
                        <h3 data-i18n="articleCardTitle">Artikel</h3>
                        <span data-i18n="readMore">Selengkapnya</span>
                    </a>
                    <a class="media-card" href="{{ route('news') }}" aria-label="Lihat berita selengkapnya">
                        <svg viewBox="0 0 512 512" aria-hidden="true"><path fill="currentColor" d="M96 96c0-35.3 28.7-64 64-64h288c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H80c-44.2 0-80-35.8-80-80V128c0-17.7 14.3-32 32-32s32 14.3 32 32v272c0 8.8 7.2 16 16 16s16-7.2 16-16V96Zm64 24v80c0 13.3 10.7 24 24 24h112c13.3 0 24-10.7 24-24v-80c0-13.3-10.7-24-24-24H184c-13.3 0-24 10.7-24 24Zm208-8c0 8.8 7.2 16 16 16h48c8.8 0 16-7.2 16-16s-7.2-16-16-16h-48c-8.8 0-16 7.2-16 16Zm0 96c0 8.8 7.2 16 16 16h48c8.8 0 16-7.2 16-16s-7.2-16-16-16h-48c-8.8 0-16 7.2-16 16ZM160 304c0 8.8 7.2 16 16 16h256c8.8 0 16-7.2 16-16s-7.2-16-16-16H176c-8.8 0-16 7.2-16 16Zm0 96c0 8.8 7.2 16 16 16h256c8.8 0 16-7.2 16-16s-7.2-16-16-16H176c-8.8 0-16 7.2-16 16Z"/></svg>
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
