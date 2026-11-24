<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <meta name="description" content="Detail artikel IMI Medika.">
    <title>Detail Artikel - Interskala Medika Indonesia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/documentation.css', 'resources/js/app.js'])
</head>
<body class="documentation-page media-list-page">
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

    <main class="article-detail-main">
        <div class="article-detail-container">
            <a class="media-list-back public-back-button" href="{{ route('articles') }}">← Kembali ke Artikel</a>
            <article class="article-detail" id="article-detail" hidden>
                <h1 id="article-detail-title"></h1>
                <div class="article-detail-meta"><time id="article-detail-date"></time><span id="article-detail-author"></span></div>
                <img id="article-detail-cover" class="article-detail-cover" alt="" hidden>
                <div id="article-detail-content" class="article-detail-content"></div>
            </article>
            <p id="article-detail-missing" class="article-detail-missing" hidden>Artikel tidak ditemukan di browser ini.</p>
        </div>
    </main>
    <footer class="site-footer"><p>© 2026 PT Interskala Medika Indonesia | Member of Interskala Group</p></footer>
</body>
</html>
