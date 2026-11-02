<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hubungi PT Interskala Medika Indonesia di Factory 1 Jakarta atau Factory 2 Banten.">
    <title>Kontak - Interskala Medika Indonesia</title>
    @vite(['resources/css/app.css', 'resources/css/contact.css', 'resources/js/app.js'])
</head>
<body class="contact-page">
    <svg class="contact-icons" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
        <symbol id="icon-factory" viewBox="0 0 24 24"><path d="M3 21V8l6 3V8l6 3V4h6v17H3Z" fill="currentColor"/><path d="M6 16h2m3 0h2m3 0h2" stroke="#fff" stroke-width="1.4" stroke-linecap="round"/></symbol>
        <symbol id="icon-pin" viewBox="0 0 24 24"><path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12Z" fill="currentColor"/><circle cx="12" cy="10" r="2.5" fill="#fff"/></symbol>
        <symbol id="icon-phone" viewBox="0 0 24 24"><path d="M6.8 3 4 5.8c-.8.8-.9 2-.5 3.1 2.2 5.5 6.1 9.4 11.6 11.6 1.1.4 2.3.3 3.1-.5l2.8-2.8-5-4-2.7 2.1a15.5 15.5 0 0 1-4.6-4.6L10.8 8l-4-5Z" fill="currentColor"/></symbol>
        <symbol id="icon-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 12h18M12 3c2.7 2.5 4 5.5 4 9s-1.3 6.5-4 9c-2.7-2.5-4-5.5-4-9s1.3-6.5 4-9Z" fill="none" stroke="currentColor" stroke-width="1.6"/></symbol>
        <symbol id="icon-mail" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="m4 7 8 6 8-6" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="icon-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 6v6l4 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="icon-instagram" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.3" cy="6.8" r="1.1" fill="currentColor"/></symbol>
        <symbol id="icon-linkedin" viewBox="0 0 24 24"><path d="M5 9v10M5 5v.2M10 19v-6.2c0-2.2 4-2.6 4 0V19m-4-9v9m4-5.5c0-2.1 4-2.6 4 0V19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="icon-tiktok" viewBox="0 0 24 24"><path d="M14 3v11.5a4.5 4.5 0 1 1-4.5-4.5m4.5-7c.6 3.5 2.4 5.3 6 5.6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    </svg>

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
                <a class="nav-link nav-link--active" href="{{ route('contact') }}" aria-current="page" data-i18n="contact">Kontak</a>
                <a class="nav-link" href="{{ route('documentation') }}" data-i18n="news">Artikel &amp; Berita</a>

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

    <main class="contact-main">
        <div class="container">
            <div class="contact-heading">
                <h1 data-i18n="contactPageTitle">Hubungi Kami</h1>
                <span class="contact-heading__rule" aria-hidden="true"></span>
                <p data-i18n="contactPageIntro">Kami siap membantu Anda dengan segala pertanyaan dan kebutuhan mengenai produk dan layanan IMI Medika. Tim kami yang profesional siap memberikan solusi terbaik untuk Anda.</p>
            </div>

            <div class="factory-grid">
                <article class="factory-card" aria-labelledby="factory-one-title">
                    <h2 id="factory-one-title"><svg aria-hidden="true"><use href="#icon-factory"/></svg>Factory 1</h2>
                    <div class="factory-card__body">
                        <div class="factory-card__address"><svg aria-hidden="true"><use href="#icon-pin"/></svg><p>Green Sedayu Bizpark Blok DM09 No.62 Kalideres, Jakarta Barat, DKI Jakarta 11840</p></div>
                        <div class="factory-card__details">
                            <a href="tel:+622122527790"><span class="factory-card__round-icon"><svg aria-hidden="true"><use href="#icon-phone"/></svg></span>(021) 22527790</a>
                            <a href="https://imimedika.com" target="_blank" rel="noopener noreferrer"><span class="factory-card__round-icon"><svg aria-hidden="true"><use href="#icon-globe"/></svg></span>www.imimedika.com</a>
                            <a href="mailto:sales@imimedika.com"><span class="factory-card__round-icon"><svg aria-hidden="true"><use href="#icon-mail"/></svg></span>sales@imimedika.com</a>
                        </div>
                        <div class="factory-card__hours">
                            <h3><svg aria-hidden="true"><use href="#icon-clock"/></svg><span data-i18n="operatingHours">Jam Operasional</span></h3>
                            <p><span data-i18n="weekdays">Senin - Jumat:</span><span>08.00 - 17.00 WIB</span></p>
                            <p><span data-i18n="weekend">Sabtu &amp; Minggu:</span><span data-i18n="closed">Libur</span></p>
                        </div>
                        <iframe title="Peta Factory 1 di Kalideres, Jakarta Barat" src="https://www.google.com/maps?q=PT+Interskala+Medika+Indonesia+Green+Sedayu+Bizpark+Kalideres&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </article>

                <article class="factory-card" aria-labelledby="factory-two-title">
                    <h2 id="factory-two-title"><svg aria-hidden="true"><use href="#icon-factory"/></svg>Factory 2</h2>
                    <div class="factory-card__body">
                        <div class="factory-card__address"><svg aria-hidden="true"><use href="#icon-pin"/></svg><p>Modern Industri XVI Blok AH No. 5B-1, Cikande Industrial Estate Kab. Serang, Banten 42186</p></div>
                        <div class="factory-card__details">
                            <a href="tel:+622122527790"><span class="factory-card__round-icon"><svg aria-hidden="true"><use href="#icon-phone"/></svg></span>(021) 22527790</a>
                            <a href="https://imimedika.com" target="_blank" rel="noopener noreferrer"><span class="factory-card__round-icon"><svg aria-hidden="true"><use href="#icon-globe"/></svg></span>www.imimedika.com</a>
                            <a href="mailto:sales@imimedika.com"><span class="factory-card__round-icon"><svg aria-hidden="true"><use href="#icon-mail"/></svg></span>sales@imimedika.com</a>
                        </div>
                        <div class="factory-card__hours">
                            <h3><svg aria-hidden="true"><use href="#icon-clock"/></svg><span data-i18n="operatingHours">Jam Operasional</span></h3>
                            <p><span data-i18n="weekdays">Senin - Jumat:</span><span>08.00 - 17.00 WIB</span></p>
                            <p><span data-i18n="weekend">Sabtu &amp; Minggu:</span><span data-i18n="closed">Libur</span></p>
                        </div>
                        <iframe title="Peta Factory 2 di Cikande, Banten" src="https://www.google.com/maps?q=PT+Interskala+Medika+Indonesia+Modern+Industri+XVI+Cikande&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </article>
            </div>

            <section class="contact-social" aria-labelledby="social-title">
                <h2 id="social-title" data-i18n="socialTitle">Ikuti Kami di Media Sosial</h2>
                <span class="contact-social__rule" aria-hidden="true"></span>
                <p data-i18n="socialDescription">Tetap terhubung dengan kami melalui media sosial untuk mendapatkan informasi terbaru tentang produk, layanan, dan promo menarik dari IMI Medika.</p>
                <div class="contact-social__links">
                    <a href="https://www.instagram.com/medikainterskala/" target="_blank" rel="noopener noreferrer" aria-label="Instagram IMI Medika"><svg aria-hidden="true"><use href="#icon-instagram"/></svg></a>
                    <a href="https://www.linkedin.com/company/96334258/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn IMI Medika"><svg aria-hidden="true"><use href="#icon-linkedin"/></svg></a>
                    <a href="https://www.tiktok.com/@medikainterskala" target="_blank" rel="noopener noreferrer" aria-label="TikTok IMI Medika"><svg aria-hidden="true"><use href="#icon-tiktok"/></svg></a>
                </div>
            </section>
        </div>
    </main>

    <footer class="site-footer">
        <p>© 2026 PT Interskala Medika Indonesia | Member of Interskala Group</p>
    </footer>
</body>
</html>
