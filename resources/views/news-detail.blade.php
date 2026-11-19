<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Berita Hospital Expo 2026 IMI Medika.">
    <title>Berita Hospital Expo 2026 - Interskala Medika Indonesia</title>
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

    <main class="news-detail-main">
        <div class="news-detail-container">
            <header class="news-detail-heading">
                <h1>Interskala Medika Indonesia dan Interskala Sehat Sejahtera Hadir di Hospital Expo 2026</h1>
                <div class="news-detail-meta">
                    <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2C6 14 2 17 2 21v1h20v-1c0-4-4-7-10-7Z"/></svg> Abdurrahman Panji Virgian</span>
                    <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h2v3h6V2h2v3h3a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h3V2Zm13 8H4v10h16V10Z"/></svg> <time datetime="2026-10-21">21 October 2026</time></span>
                    <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm1 4v5.6l3.5 2.1-1 1.7-4.5-2.7V6h2Z"/></svg> 3 min read</span>
                </div>
            </header>
            <img class="news-detail-hero" src="{{ asset('images/hospital-expo-2026.jpg') }}" alt="Tim Interskala Medika Indonesia dan Interskala Sehat Sejahtera di Hospital Expo 2026">
            <article class="news-detail-article">
                <p><strong>Memperkenalkan solusi alat kesehatan, memperluas koneksi industri, dan memperkuat komitmen sebagai mitra terpercaya dalam layanan kesehatan.</strong></p>
                <p>Interskala Medika Indonesia dan Interskala Sehat Sejahtera berpartisipasi dalam Indonesia International Hospital Expo 2026 yang berlangsung pada 7–10 Oktober 2026 di Indonesia Convention Exhibition (ICE) BSD City, Tangerang.</p>
                <p>Melalui pameran ini, kami memperkenalkan produk unggulan dan solusi alat kesehatan dari kedua perusahaan, sekaligus membuka ruang untuk berinteraksi dengan pengunjung, distributor, profesional kesehatan, dan pelaku industri. Partisipasi ini menjadi kesempatan bagi kami untuk memperkenalkan portofolio produk, bertukar informasi, serta membangun hubungan yang berpotensi mendukung perkembangan industri kesehatan di Indonesia.</p>
                <h2>Memperkenalkan Solusi Alat Kesehatan untuk Berbagai Kebutuhan</h2>
                <p>Sebagai perusahaan yang bergerak di bidang alat kesehatan, kami terus berupaya menghadirkan produk dan solusi yang relevan dengan kebutuhan layanan kesehatan.</p>
                <p>Interskala Medika Indonesia berfokus pada impor, distribusi, dan manufaktur alat kesehatan, khususnya produk diagnostik dan in vitro diagnostics (IVD). Sementara itu, Interskala Sehat Sejahtera bergerak di bidang impor dan distribusi alat kesehatan, termasuk solusi hemodialisis, peralatan medis, serta produk medis habis pakai.</p>
                <p>Melalui kehadiran di Hospital Expo 2026, kami ingin memberikan kesempatan kepada pengunjung dan mitra industri untuk mengenal lebih dekat produk yang kami hadirkan, sekaligus berdiskusi mengenai kebutuhan dan perkembangan di sektor kesehatan.</p>
                <h2>Membangun Koneksi dan Peluang Kolaborasi</h2>
                <p>Bagi kami, Hospital Expo 2026 bukan hanya tentang memperkenalkan produk. Pameran ini juga menjadi ruang untuk membangun komunikasi, bertukar perspektif, dan memahami kebutuhan para profesional kesehatan serta mitra industri.</p>
                <p>Sepanjang empat hari penyelenggaraan, kami berkesempatan berinteraksi dengan pengunjung, distributor, dan berbagai pemangku kepentingan di sektor kesehatan. Setiap percakapan membuka ruang untuk berbagi wawasan, memperluas jaringan, dan menjajaki peluang kolaborasi.</p>
                <p>Kami percaya bahwa perkembangan industri kesehatan tidak hanya didukung oleh ketersediaan produk berkualitas, tetapi juga oleh hubungan profesional yang dibangun melalui kepercayaan, pemahaman, dan komitmen bersama.</p>
                <h2>Melangkah Bersama untuk Masa Depan Kesehatan</h2>
                <p>Partisipasi dalam Hospital Expo 2026 menjadi salah satu langkah kami untuk terus memperkenalkan solusi alat kesehatan, memperkuat hubungan dengan para pemangku kepentingan, dan berkontribusi terhadap perkembangan industri kesehatan di Indonesia.</p>
                <p>Kami mengucapkan terima kasih kepada seluruh pengunjung, distributor, profesional kesehatan, dan mitra industri yang telah mengunjungi booth kami serta meluangkan waktu untuk berdiskusi dan bertukar wawasan.</p>
                <p>Pameran ini telah berakhir, tetapi komitmen kami untuk membangun kemitraan yang kuat dan mendukung kebutuhan layanan kesehatan akan terus berlanjut.</p>
                <p>Bersama, kami membawa masa depan kesehatan lebih dekat.</p>
                <p>-- --</p>
                <h2>Contact Us</h2>
                <p>PT Interskala Medika Indonesia<br>Website: <a href="https://imimedika.com/">https://imimedika.com/</a></p>
                <p>PT Interskala Sehat Sejahtera<br>Website: <a href="https://issmedika.com/">https://issmedika.com/</a></p>
                <p>For business inquiries, product information, or potential partnerships, please contact us through our official communication channels.</p>
                <div class="news-detail-share"><span>Bagikan:</span><a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('news.hospital-expo')) }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke Facebook">f</a><a href="https://twitter.com/intent/tweet?url={{ urlencode(route('news.hospital-expo')) }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke X">𝕏</a><a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('news.hospital-expo')) }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke LinkedIn">in</a><a href="https://wa.me/?text={{ urlencode(route('news.hospital-expo')) }}" target="_blank" rel="noopener noreferrer" aria-label="Bagikan ke WhatsApp">◉</a></div>
                <nav class="news-detail-navigation" aria-label="Navigasi berita"><a class="public-back-button" href="{{ route('news') }}">← Kembali ke Daftar Berita</a><span aria-disabled="true">Berita Selanjutnya →</span></nav>
            </article>
        </div>
    </main>
    <footer class="site-footer"><p>© 2026 PT Interskala Medika Indonesia | Member of Interskala Group</p></footer>
</body>
</html>
