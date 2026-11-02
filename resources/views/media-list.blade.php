<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $type === 'articles' ? 'Daftar artikel kesehatan IMI Medika.' : 'Berita terbaru IMI Medika.' }}">
    <title>{{ $type === 'articles' ? 'Artikel' : 'Berita' }} - Interskala Medika Indonesia</title>
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
                <a class="nav-link nav-link--active" href="{{ route('documentation') }}" data-i18n="news">Artikel &amp; Berita</a>

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

    <main class="documentation-main media-list-main">
        <div class="container">
            <div class="documentation-heading">
                <h1 data-i18n="{{ $type === 'articles' ? 'articleListTitle' : 'newsListTitle' }}">{{ $type === 'articles' ? 'Daftar Artikel' : 'Berita Terkini' }}</h1>
                <span aria-hidden="true"></span>
                <p data-i18n="{{ $type === 'articles' ? 'articleListIntro' : 'newsListIntro' }}">{{ $type === 'articles' ? 'Temukan berbagai artikel kesehatan terkini yang informatif dan bermanfaat untuk meningkatkan kualitas hidup Anda' : 'Ikuti perkembangan terbaru seputar kesehatan, layanan, dan kegiatan terbaru dari IMI Medika' }}</p>
            </div>
            <div class="media-empty">
                <h2 data-i18n="{{ $type === 'articles' ? 'noArticles' : 'noNews' }}">{{ $type === 'articles' ? 'Belum Ada Artikel' : 'Belum Ada Berita' }}</h2>
                @if ($type === 'news')
                    <p data-i18n="noNewsDescription">Silakan tambahkan berita pertama Anda untuk dibagikan kepada pengunjung website.</p>
                @endif
                <a href="{{ route('documentation') }}" data-i18n="backToDocumentation">Kembali ke Dokumentasi</a>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <p>© 2026 PT Interskala Medika Indonesia | Member of Interskala Group</p>
    </footer>
</body>
</html>
