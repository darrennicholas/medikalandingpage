<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Jelajahi katalog alat kesehatan PT Interskala Medika Indonesia.">
    <title>Produk Kami - Interskala Medika Indonesia</title>
    @vite(['resources/css/app.css', 'resources/css/products.css', 'resources/js/app.js', 'resources/js/products.js'])
</head>
<body class="product-page">
    <svg class="product-icons" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
        <symbol id="icon-folder" viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v10H3V7Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></symbol>
        <symbol id="icon-tag" viewBox="0 0 24 24"><path d="m3 12 9-9h8v8l-9 9-8-8Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="16.5" cy="7.5" r="1.5" fill="currentColor"/></symbol>
        <symbol id="icon-reset" viewBox="0 0 24 24"><path d="M4 11a8 8 0 1 1 2 7M4 5v6h6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="icon-cart" viewBox="0 0 24 24"><path d="M2 4h3l2.3 11h11.5L21 7H6M9 20h.01M18 20h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="icon-pdf" viewBox="0 0 24 24"><path d="M6 2h8l5 5v15H6V2Zm8 0v5h5M9 16h7M9 19h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></symbol>
        <symbol id="icon-close" viewBox="0 0 24 24"><path d="M5 5 19 19M19 5 5 19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="icon-download" viewBox="0 0 24 24"><path d="M12 3v12m-5-5 5 5 5-5M4 17v4h16v-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
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
                <a class="nav-link nav-link--active" href="{{ route('products') }}" aria-current="page" data-i18n="products">Produk</a>
                <a class="nav-link" href="https://imimedika.com/karir.php" data-i18n="careers">Karir</a>
                <a class="nav-link" href="{{ route('contact') }}" data-i18n="contact">Kontak</a>
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

    <main>
        <section class="product-hero" aria-labelledby="product-hero-title">
            <div class="container product-hero__content">
                <h1 id="product-hero-title">PT Interskala Medika Indonesia</h1>
                <p data-i18n="productTagline">WE BRING THE FUTURE HEALTH CARE</p>
            </div>
            <svg class="product-hero__wave" viewBox="0 0 1000 100" preserveAspectRatio="none" aria-hidden="true"><path d="M0,0V100H1000V0C849.5,133.5,500,133.5,0,0Z"/></svg>
        </section>

        <section class="product-catalog container" id="products" aria-labelledby="product-catalog-title">
            <div class="product-heading">
                <h2 id="product-catalog-title" data-i18n="productHeading">Temukan Produk Terbaik Kami</h2>
                <p data-i18n="productDescription">Jelajahi berbagai kategori produk berkualitas tinggi untuk kebutuhan medis dan kesehatan Anda</p>
            </div>

            <div class="product-filters" aria-label="Filter produk">
                <div class="product-filters__group">
                    <label for="filter-kategori"><svg aria-hidden="true"><use href="#icon-folder"/></svg><span data-i18n="categoryLabel">Kategori</span></label>
                    <select id="filter-kategori" class="product-filters__select">
                        <option value="all" data-i18n="allCategories">-- Semua Kategori --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="product-filters__group">
                    <label for="filter-subkategori"><svg aria-hidden="true"><use href="#icon-tag"/></svg><span data-i18n="subcategoryLabel">Subkategori</span></label>
                    <select id="filter-subkategori" class="product-filters__select" disabled>
                        <option value="all" data-i18n="allSubcategories">-- Semua Subkategori --</option>
                    </select>
                </div>
                <button class="product-filters__reset" id="reset-filters" type="button"><svg aria-hidden="true"><use href="#icon-reset"/></svg><span data-i18n="resetFilter">Reset Filter</span></button>
            </div>

            <p class="product-results" id="product-results" aria-live="polite"></p>

            <div id="product-categories">
                @foreach ($categories as $category)
                    <section class="product-category-section" data-category="{{ $category['id'] }}" data-category-name="{{ $category['name'] }}" aria-labelledby="category-{{ $category['id'] }}">
                        <h3 class="product-category-section__title" id="category-{{ $category['id'] }}">{{ $category['name'] }}</h3>
                        @if (count($category['products']) === 0)
                            <p class="product-category-section__empty" data-i18n="emptyCategory">Belum ada produk dalam kategori ini.</p>
                        @else
                            <div class="product-grid">
                                @foreach ($category['products'] as $product)
                                    <article class="product-card" data-subcategory="{{ $product['subtitle'] }}">
                                        @if ($product['logo_bottom'])
                                            <img class="product-card__watermark" src="{{ asset($product['logo_bottom']) }}" alt="" loading="lazy">
                                        @endif
                                        <div class="product-card__image-wrap">
                                            <img class="product-card__image" src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}" loading="lazy">
                                            @if ($product['logo_top'])
                                                <span class="product-card__logo"><img src="{{ asset($product['logo_top']) }}" alt="" loading="lazy"></span>
                                            @endif
                                        </div>
                                        <div class="product-card__content">
                                            <h4 class="product-card__title">{{ $product['name'] }}</h4>
                                            @if ($product['parameter'])
                                                <strong class="product-card__parameter">{{ $product['parameter'] }}</strong>
                                            @endif
                                            <p class="product-card__subtitle">{{ $product['subtitle'] }}</p>
                                            <span class="product-card__badge">{{ $category['name'] }}</span>
                                            <div class="product-card__actions">
                                                <a class="product-card__buy" href="{{ $product['buy_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Beli {{ $product['name'] }}"><svg aria-hidden="true"><use href="#icon-cart"/></svg><span data-i18n="buyProduct">Beli</span></a>
                                                <button class="product-card__brochure" type="button" data-brochure-url="{{ $product['brochure_url'] }}" data-product-name="{{ $product['name'] }}" aria-label="Lihat brosur {{ $product['name'] }}"><svg aria-hidden="true"><use href="#icon-pdf"/></svg><span data-i18n="brochure">Brosur</span></button>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>

            <nav class="product-pagination" id="product-pagination" aria-label="Halaman produk"></nav>
        </section>
    </main>

    <div class="product-modal" id="product-modal" hidden>
        <div class="product-modal__backdrop" data-close-modal></div>
        <div class="product-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="brochure-modal-title">
            <div class="product-modal__header">
                <div class="product-modal__heading"><svg aria-hidden="true"><use href="#icon-pdf"/></svg><div><strong id="brochure-modal-title" data-i18n="brochurePreview">Preview Brosur</strong><span id="brochure-product-name"></span></div></div>
                <button class="product-modal__close" type="button" data-close-modal aria-label="Tutup pratinjau brosur"><svg aria-hidden="true"><use href="#icon-close"/></svg></button>
            </div>
            <div class="product-modal__body">
                <iframe id="brochure-frame" title="Preview brosur produk" loading="lazy" hidden></iframe>
                <div class="product-modal__error" id="brochure-error" hidden><svg aria-hidden="true"><use href="#icon-pdf"/></svg><h3 data-i18n="brochureUnavailable">Gagal Memuat Brosur</h3><p data-i18n="brochureUnavailableMessage">File PDF tidak dapat ditampilkan. Silakan download langsung.</p></div>
            </div>
            <div class="product-modal__footer"><span data-i18n="brochureHint">Klik kanan pada PDF untuk zoom atau download</span><a id="brochure-download" href="#" target="_blank" rel="noopener noreferrer"><svg aria-hidden="true"><use href="#icon-download"/></svg><span data-i18n="downloadPdf">Download PDF</span></a></div>
        </div>
    </div>

    <footer class="site-footer">
        <p>© 2026 PT Interskala Medika Indonesia | Member of Interskala Group</p>
    </footer>
</body>
</html>
