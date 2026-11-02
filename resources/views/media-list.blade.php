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
            @if ($type === 'news')
                <div class="news-toolbar">
                    <label class="news-search" for="news-search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg>
                        <input id="news-search" type="search" placeholder="Cari berita..." autocomplete="off" aria-label="Cari berita">
                    </label>
                    <div class="news-toolbar__actions">
                        <label class="news-select" for="news-filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 5h18l-7 8v5l-4 2v-7L3 5Z"/></svg>
                            <span>Filter</span>
                            <select id="news-filter" aria-label="Filter berita berdasarkan tahun">
                                <option value="all">Semua Tahun</option>
                                <option value="2026">2026</option>
                            </select>
                        </label>
                        <label class="news-select" for="news-sort">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M7 12h13M10 17h10"/></svg>
                            <span>Urutkan</span>
                            <select id="news-sort" aria-label="Urutkan berita">
                                <option value="newest">Terbaru</option>
                                <option value="oldest">Terlama</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="news-grid" id="news-grid">
                    <article class="news-card" data-title="Interskala Medika Indonesia dan Interskala Sehat Sejahtera Hadir di Hospital Expo 2026" data-year="2026" data-date="2026-10-21">
                        <a class="news-card__image" href="https://imimedika.com/detail_berita.php?id=6" aria-label="Baca berita Hospital Expo 2026">
                            <img src="{{ asset('images/hospital-expo-2026.jpg') }}" alt="Tim Interskala Medika Indonesia dan Interskala Sehat Sejahtera di Hospital Expo 2026" loading="lazy" width="6240" height="4160">
                        </a>
                        <div class="news-card__body">
                            <div class="news-card__meta"><span>Abdurrahman Panji Virgian</span><time datetime="2026-10-21">21 Oct 2026</time></div>
                            <h2><a href="https://imimedika.com/detail_berita.php?id=6">Interskala Medika Indonesia dan Interskala Sehat Sejahtera Hadir di Hospital Expo 2026</a></h2>
                            <p>Interskala Medika Indonesia dan Interskala Sehat Sejahtera memperkenalkan solusi alat kesehatan di Hospital Expo 2026.</p>
                            <a class="news-card__read-more" href="https://imimedika.com/detail_berita.php?id=6">Baca Selengkapnya <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                </div>
                <p class="news-no-results" id="news-no-results" hidden>Tidak ada berita yang cocok dengan pencarian Anda.</p>
                <nav class="news-pagination" aria-label="Halaman berita"><span class="news-pagination__active" aria-current="page">1</span></nav>
            @else
                <div class="media-empty">
                    <h2 data-i18n="noArticles">Belum Ada Artikel</h2>
                    <a href="{{ route('documentation') }}" data-i18n="backToDocumentation">Kembali ke Dokumentasi</a>
                </div>
            @endif
        </div>
    </main>

    <footer class="site-footer">
        <p>© 2026 PT Interskala Medika Indonesia | Member of Interskala Group</p>
    </footer>
</body>
</html>
