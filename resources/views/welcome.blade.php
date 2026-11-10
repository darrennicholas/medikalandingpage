<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="PT Interskala Medika Indonesia melayani kebutuhan alat kesehatan di seluruh Indonesia.">
    <title>PT Interskala Medika Indonesia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header" id="site-header">
        <div class="site-header__inner">
            <a class="site-logo" href="{{ route('home') }}" aria-label="PT Interskala Medika Indonesia, beranda">
                <img src="{{ asset('images/medika-logo.png') }}" alt="IMI Medika" width="120" height="72">
            </a>

            <button class="menu-toggle" id="menu-toggle" type="button" aria-controls="primary-navigation" aria-expanded="false" aria-label="Buka menu">
                <span></span><span></span><span></span>
            </button>

            <nav class="primary-navigation" id="primary-navigation" aria-label="Navigasi utama">
                <a class="nav-link nav-link--active" href="{{ route('home') }}" aria-current="page" data-i18n="home">Beranda</a>
                <a class="nav-link" href="{{ route('about') }}" data-i18n="about">Tentang</a>
                <a class="nav-link" href="{{ route('products') }}" data-i18n="products">Produk</a>
                <a class="nav-link" href="{{ route('documentation') }}" data-i18n="news">Dokumentasi</a>
                <a class="nav-link" href="https://imimedika.com/karir.php" data-i18n="careers">Karir</a>
                <a class="nav-link" href="{{ route('contact') }}" data-i18n="contact">Kontak</a>
                <a class="nav-link" href="{{ route('documentation') }}" data-i18n="articlesNews">Artikel &amp; Berita</a>

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

    <main>
        <section class="hero" id="beranda" aria-labelledby="hero-title">
            <div class="container hero__content">
                <h1 id="hero-title">PT INTERSKALA MEDIKA INDONESIA</h1>
                <p>WE BRING THE FUTURE HEALTHCARE</p>
            </div>
        </section>

        <section class="coverage" id="jangkauan" aria-labelledby="coverage-title">
            <div class="container">
                <div class="section-title">
                    <h2 id="coverage-title" data-i18n="coverageTitle">Jangkauan Kami</h2>
                    <p data-i18n="coverageSubtitle">Melayani seluruh Indonesia dengan jaringan distribusi yang luas</p>
                </div>

                <div class="coverage__content">
                    <div class="coverage__info">
                        <h3 data-i18n="salesNetwork">Jaringan Penjualan</h3>
                        <p data-i18n="coverageDescription">PT Interskala Medika Indonesia memiliki jaringan distribusi yang mencakup seluruh wilayah Indonesia, dari Sabang sampai Merauke. Kami berkomitmen untuk menyediakan alat kesehatan berkualitas ke setiap pelosok negeri.</p>

                        <div class="coverage__stats" aria-label="Statistik jangkauan">
                            <div class="stat-card"><strong>34</strong><span data-i18n="provinces">Provinsi</span></div>
                            <div class="stat-card"><strong>500+</strong><span data-i18n="cities">Kota/Kabupaten</span></div>
                            <div class="stat-card"><strong>5000+</strong><span data-i18n="partners">Mitra</span></div>
                        </div>
                    </div>

                    <figure class="coverage__map">
                        <img src="{{ asset('images/coverage-map.jpg') }}" alt="Peta jangkauan PT Interskala Medika Indonesia" loading="lazy" width="1300" height="591">
                        <figcaption>PT Interskala Medika Indonesia</figcaption>
                    </figure>
                </div>
            </div>
        </section>

        <section class="contact-cta" id="kontak" aria-labelledby="contact-title">
            <div class="container">
                <h2 id="contact-title" data-i18n="contactTitle">Siap Membantu Kebutuhan Alat Kesehatan Anda</h2>
                <p data-i18n="contactSubtitle">Jangan ragu untuk menghubungi kami untuk informasi lebih lanjut</p>
                <a class="contact-cta__button" href="{{ route('contact') }}" data-i18n="contactButton">Hubungi Kami!</a>
            </div>
        </section>

        <section class="brands" id="brand" aria-labelledby="brand-title">
            <div class="container">
                <div class="section-title">
                    <h2 id="brand-title" data-i18n="brandTitle">Brand Kami</h2>
                    <p data-i18n="brandSubtitle">Bekerja sama dengan merek-merek terpercaya</p>
                </div>

                <div class="brand-carousel" aria-label="Brand mitra">
                    <div class="brand-carousel__viewport">
                        <div class="brand-carousel__track" id="brand-track">
                            <div class="brand-carousel__slide"><img src="{{ asset('images/brands/indocheck.png') }}" alt="Indocheck" loading="lazy"></div>
                            <div class="brand-carousel__slide"><img src="{{ asset('images/brands/g-scientific.png') }}" alt="G Scientific" loading="lazy"></div>
                            <div class="brand-carousel__slide"><img src="{{ asset('images/brands/diagnose.png') }}" alt="Diagnose" loading="lazy"></div>
                            <div class="brand-carousel__slide"><img src="{{ asset('images/brands/vitrogen.png') }}" alt="Vitrogen" loading="lazy"></div>
                            <div class="brand-carousel__slide"><img src="{{ asset('images/brands/genomic.png') }}" alt="Genomic" loading="lazy"></div>
                            <div class="brand-carousel__slide"><img src="{{ asset('images/brands/mkcells.png') }}" alt="MKCells" loading="lazy"></div>
                            <div class="brand-carousel__slide"><img src="{{ asset('images/brands/dr-plus.png') }}" alt="DR Plus" loading="lazy"></div>
                            <div class="brand-carousel__slide"><img src="{{ asset('images/brands/is.png') }}" alt="IS" loading="lazy"></div>
                            <div class="brand-carousel__slide"><img src="{{ asset('images/brands/logix.png') }}" alt="Logix" loading="lazy"></div>
                            <div class="brand-carousel__slide"><img src="{{ asset('images/brands/tamatech.png') }}" alt="Tamatech" loading="lazy"></div>
                            <div class="brand-carousel__slide"><img src="{{ asset('images/brands/medicheck.png') }}" alt="Medicheck" loading="lazy"></div>
                        </div>
                    </div>
                    <div class="brand-carousel__dots" id="brand-dots" aria-label="Pilih brand"></div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <p>© 2026 PT Interskala Medika Indonesia | Member of Interskala Group</p>
    </footer>
</body>
</html>
