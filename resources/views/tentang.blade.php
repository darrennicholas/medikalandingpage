<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Tentang PT Interskala Medika Indonesia, visi, misi, dan sertifikasi kami.">
    <title>Tentang Kami - Interskala Medika Indonesia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="about-page">
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
                <a class="nav-link nav-link--active" href="{{ route('about') }}" aria-current="page" data-i18n="about">Tentang</a>
                <a class="nav-link" href="{{ route('products') }}" data-i18n="products">Produk</a>
                <a class="nav-link" href="{{ route('documentation') }}" data-i18n="news">Dokumentasi</a>
                <a class="nav-link" href="https://imimedika.com/karir.php" data-i18n="careers">Karir</a>
                <a class="nav-link" href="{{ route('contact') }}" data-i18n="contact">Kontak</a>

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
        <section class="about-hero" aria-labelledby="about-hero-title">
            <div class="container about-hero__content">
                <h1 id="about-hero-title">PT Interskala Medika Indonesia</h1>
                <span class="about-hero__rule" aria-hidden="true"></span>
                <p data-i18n="aboutTagline">IMPORTIR | DISTRIBUTOR | MANUFAKTUR ALAT KESEHATAN</p>
            </div>
        </section>

        <section class="about-intro" aria-labelledby="about-intro-title">
            <div class="container">
                <div class="section-title">
                    <h2 id="about-intro-title" data-i18n="aboutTitle">Tentang Kami</h2>
                </div>

                <div class="about-intro__content">
                    <img class="about-intro__image" src="{{ asset('images/gambartentangmedika.jpeg') }}" alt="Tim PT Interskala Medika Indonesia" loading="lazy">
                    <div class="about-intro__copy">
                        <h3>PT Interskala Medika Indonesia</h3>
                        <p data-i18n="aboutDescriptionOne">PT Interskala Medika Indonesia adalah perusahaan impor, distribusi, dan manufaktur alat kesehatan, khususnya produk diagnostik dan in vitro (IVD). Dengan sertifikasi CE dan ISO, kami berkomitmen menghadirkan produk aman, berkualitas, dan sesuai standar internasional untuk mendukung pelayanan kesehatan yang lebih baik.</p>
                        <p data-i18n="aboutDescriptionTwo">Kami terus berinovasi dalam teknologi, efisiensi, dan aksesibilitas, serta didukung oleh pengalaman, jaringan distribusi luas, dan tim profesional. PT Interskala Medika Indonesia siap menjadi mitra strategis bagi institusi kesehatan, laboratorium, dan penyedia layanan medis di seluruh Indonesia.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="vision-mission" aria-labelledby="vision-mission-title">
            <div class="container">
                <div class="section-title">
                    <h2 id="vision-mission-title" data-i18n="visionMissionTitle">Visi &amp; Misi</h2>
                </div>
                <div class="vision-mission__cards">
                    <article class="vision-card">
                        <span class="vision-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="none"><path d="M5 32s10-16 27-16 27 16 27 16S49 48 32 48 5 32 5 32Z" stroke="currentColor" stroke-width="5"/><circle cx="32" cy="32" r="10" stroke="currentColor" stroke-width="5"/><circle cx="32" cy="32" r="3" fill="currentColor"/></svg>
                        </span>
                        <h3 data-i18n="visionTitle">Visi</h3>
                        <p data-i18n="visionDescription">Menjadi mitra strategis terdepan dalam menghadirkan solusi alat kesehatan berstandar global bagi masa depan kesehatan Indonesia yang lebih andal dan berkelanjutan</p>
                    </article>
                    <article class="vision-card vision-card--mission">
                        <span class="vision-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 64 64" fill="none"><circle cx="32" cy="32" r="25" stroke="currentColor" stroke-width="5"/><circle cx="32" cy="32" r="15" stroke="currentColor" stroke-width="5"/><circle cx="32" cy="32" r="5" fill="currentColor"/></svg>
                        </span>
                        <h3 data-i18n="missionTitle">Misi</h3>
                        <p><span data-i18n="missionDescriptionOne">Menghadirkan instrumen medis berkualitas tinggi yang memenuhi standar internasional demi pelayanan kesehatan yang presisi.</span><span data-i18n="missionDescriptionTwo">Mengembangkan solusi diagnostik yang inovatif dan kompetitif untuk memenuhi kebutuhan fasilitas kesehatan secara efektif.</span><span data-i18n="missionDescriptionThree">Membangun relasi jangka panjang dengan pelanggan melalui integritas, profesionalisme, dan layanan yang berkelanjutan.</span></p>
                    </article>
                </div>
            </div>
        </section>

        <section class="about-stats" aria-label="Statistik perusahaan">
            <div class="container about-stats__grid">
                <div class="about-stats__card"><strong>5</strong><span data-i18n="yearsExperience">Tahun Pengalaman</span></div>
                <div class="about-stats__card"><strong>5000</strong><span data-i18n="aboutPartners">Mitra</span></div>
                <div class="about-stats__card"><strong>34</strong><span data-i18n="provinceReach">Jangkauan Provinsi</span></div>
            </div>
        </section>

        <section class="certificates" aria-labelledby="certificates-title">
            <div class="container">
                <div class="section-title">
                    <h2 id="certificates-title" data-i18n="certificatesTitle">Sertifikat &amp; Penghargaan</h2>
                    <p data-i18n="certificatesSubtitle">Pengakuan atas komitmen kami terhadap kualitas dan standar internasional</p>
                </div>

                <div class="certificate-carousel" aria-label="Sertifikat perusahaan">
                    <div class="certificate-carousel__viewport">
                        <div class="certificate-carousel__track" id="certificate-track">
                            <article class="certificate-card"><h3>Cara Distribusi Alat Kesehatan yang Baik</h3><span>KEMENKES</span><img src="{{ asset('images/cdakb.png') }}" alt="Sertifikat CDAKB Kemenkes" loading="lazy"></article>
                            <article class="certificate-card"><h3>ISO 9001:2015</h3><span>TERAKREDITASI</span><img src="{{ asset('images/iso-9001.png') }}" alt="Sertifikat ISO 9001:2015 terakreditasi" loading="lazy"></article>
                            <article class="certificate-card"><h3>CDAKB</h3><span>KEMENTERIAN KESEHATAN</span><img src="{{ asset('images/cdakb.png') }}" alt="Sertifikat CDAKB Kementerian Kesehatan" loading="lazy"></article>
                            <article class="certificate-card"><h3>ISO 9001:2015</h3><span>TUV NORD</span><img src="{{ asset('images/iso-9001.png') }}" alt="Sertifikat ISO 9001:2015 TUV Nord" loading="lazy"></article>
                            <article class="certificate-card"><h3>Tingkat Komponen Dalam Negeri</h3><span>KEMENPERIN</span><img src="{{ asset('images/tkdn.png') }}" alt="Sertifikat TKDN Kemenperin" loading="lazy"></article>
                            <article class="certificate-card"><h3>CDAKB</h3><span>KEMENKES RI</span><img src="{{ asset('images/cdakb.png') }}" alt="Sertifikat CDAKB Kemenkes RI" loading="lazy"></article>
                        </div>
                    </div>
                    <div class="certificate-carousel__dots" id="certificate-dots" aria-label="Pilih sertifikat"></div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <p>© 2026 PT Interskala Medika Indonesia | Member of Interskala Group</p>
    </footer>
</body>
</html>
