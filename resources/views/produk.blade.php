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
        <symbol id="icon-chevron" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
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
        <section class="product-hero" aria-labelledby="product-hero-title">
            <div class="container product-hero__content">
                <h1 id="product-hero-title">PT. Interskala Medika Indonesia</h1>
                <span class="product-hero__rule" aria-hidden="true"></span>
                <p data-i18n="productTagline">WE BRING THE FUTURE HEALTH CARE</p>
            </div>
        </section>

        <section class="product-catalog container" id="products" aria-labelledby="product-catalog-title">
            <div class="product-heading">
                <h2 id="product-catalog-title" data-i18n="productHeading">Temukan Produk Terbaik Kami</h2>
                <p data-i18n="productDescription">Jelajahi berbagai kategori produk berkualitas tinggi untuk kebutuhan medis dan kesehatan Anda</p>
            </div>

            @php
                $categoryIdsByName = collect($categories)->pluck('id', 'name');
                $molecularCategoryIds = collect([$categoryIdsByName['AKL'] ?? null, $categoryIdsByName['Molecular'] ?? null])->filter()->implode(',');
                $rapidTestCategoryIds = collect([
                    $categoryIdsByName['Rapid Test (Master)'] ?? null,
                    $categoryIdsByName['Drugs Abuse'] ?? null,
                    $categoryIdsByName['Rapid Test'] ?? null,
                    $categoryIdsByName['Rapid Test COVID-19'] ?? null,
                ])->filter()->implode(',');
                $pointOfCareCategoryIds = collect([
                    $categoryIdsByName['POCT'] ?? null,
                    $categoryIdsByName['Glucose Meter'] ?? null,
                    $categoryIdsByName['Point Of Care Testing'] ?? null,
                ])->filter()->implode(',');
                $productGroups = [
                    ['title' => 'Molecular', 'categoryNames' => ['AKL', 'Molecular']],
                    ['title' => 'Rapid Test (Master)', 'categoryNames' => ['Rapid Test (Master)', 'Drugs Abuse', 'Rapid Test', 'Rapid Test COVID-19']],
                    ['title' => 'Point of Care Testing (POCT)', 'categoryNames' => ['POCT', 'Glucose Meter', 'Point Of Care Testing']],
                    ['title' => 'Medical Instruments', 'categoryNames' => ['Instrument and Consumable']],
                    ['title' => 'Medical Consumables', 'categoryNames' => ['Medical Consumable']],
                ];
            @endphp

            <div class="product-filter-controls" hidden>
                <select id="filter-kategori" aria-label="Filter kategori produk">
                    <option value="molecular">Molecular</option>
                    <option value="rapid-test-master" selected>Rapid Test (Master)</option>
                    <option value="point-of-care">Point of Care Testing (POCT)</option>
                    <option value="medical-instruments">Medical Instruments</option>
                    <option value="medical-consumables">Medical Consumables</option>
                    <option value="all" data-i18n="allCategories">-- Semua Kategori --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                    @endforeach
                </select>
                <select id="filter-subkategori" aria-label="Filter subkategori produk" disabled>
                    <option value="all" data-i18n="allSubcategories">-- Semua Subkategori --</option>
                </select>
                <button id="reset-filters" type="button">Reset Filter</button>
            </div>

            <div class="product-catalog__layout">
                <aside class="product-sidebar" aria-label="Kategori produk">
                    <div class="product-sidebar__heading">
                        <h2 data-i18n="categoryLabel">Kategori Produk</h2>
                        <span aria-hidden="true"></span>
                    </div>

                    <button class="product-sidebar__category product-sidebar__category--all" type="button" data-category-filter="all" aria-pressed="false" data-i18n="allCategories">Semua Produk</button>

                    <nav class="product-sidebar__list" aria-label="Pilih kategori produk">
                        <div class="product-sidebar__group">
                            <div class="product-sidebar__category-row">
                                <button class="product-sidebar__category product-sidebar__category--parent" type="button" data-category-filter="molecular" data-category-ids="{{ $molecularCategoryIds }}" data-category-title="Molecular" aria-pressed="false">Molecular</button>
                                <button class="product-sidebar__toggle" type="button" data-category-toggle aria-expanded="false" aria-controls="molecular-subcategories" aria-label="Tampilkan subkategori Molecular"><svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                            </div>
                            <div class="product-sidebar__children" id="molecular-subcategories" hidden>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="molecular" data-category-ids="{{ $molecularCategoryIds }}" data-product-filter="PCR" data-category-title="Molecular" aria-pressed="false">PCR <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="molecular" data-category-ids="{{ $molecularCategoryIds }}" data-product-filter="Extraction|Ekstraksi" data-category-title="Molecular" aria-pressed="false">Extraction <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                            </div>
                        </div>

                        <div class="product-sidebar__group">
                            <div class="product-sidebar__category-row">
                                <button class="product-sidebar__category product-sidebar__category--parent" type="button" data-category-filter="rapid-test-master" data-category-ids="{{ $rapidTestCategoryIds }}" data-category-title="Rapid Test (Master)" aria-pressed="true">Rapid Test (Master)</button>
                                <button class="product-sidebar__toggle" type="button" data-category-toggle aria-expanded="true" aria-controls="rapid-test-subcategories" aria-label="Tampilkan subkategori Rapid Test (Master)"><svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                            </div>
                            <div class="product-sidebar__children" id="rapid-test-subcategories">
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Rapid Test'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Rapid Test'] ?? '' }}" data-product-filter="HIV|Syphilis" data-category-title="Rapid Test (Master)" aria-pressed="false">Infeksius HIV Series <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Rapid Test'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Rapid Test'] ?? '' }}" data-product-filter="Hepatitis|HBsAg|Anti HBs|Anti-HCV|HAV|HBV" data-category-title="Rapid Test (Master)" aria-pressed="false">Infeksius Hepatitis <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Rapid Test'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Rapid Test'] ?? '' }}" data-product-filter="Malaria|Dengue|Chikungunya|Typhoid" data-category-title="Rapid Test (Master)" aria-pressed="false">Infeksius Tropical <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Rapid Test'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Rapid Test'] ?? '' }}" data-product-filter="Tumor Marker" data-category-title="Rapid Test (Master)" aria-pressed="false">Tumor Marker <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Rapid Test COVID-19'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Rapid Test COVID-19'] ?? '' }}" data-category-title="Rapid Test (Master)" aria-pressed="false">Covid <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Drugs Abuse'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Drugs Abuse'] ?? '' }}" data-category-title="Rapid Test (Master)" aria-pressed="false">Drugs of Abuse <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                            </div>
                        </div>

                        <div class="product-sidebar__group">
                            <div class="product-sidebar__category-row">
                                <button class="product-sidebar__category product-sidebar__category--parent" type="button" data-category-filter="point-of-care" data-category-ids="{{ $pointOfCareCategoryIds }}" data-category-title="Point of Care Testing (POCT)" aria-pressed="false">Point of Care Testing (POCT)</button>
                                <button class="product-sidebar__toggle" type="button" data-category-toggle aria-expanded="false" aria-controls="poct-subcategories" aria-label="Tampilkan subkategori Point of Care Testing"><svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                            </div>
                            <div class="product-sidebar__children" id="poct-subcategories" hidden>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['POCT'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['POCT'] ?? '' }}" data-product-filter="Glucose" data-category-title="Point of Care Testing (POCT)" aria-pressed="false">Glucose <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['POCT'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['POCT'] ?? '' }}" data-product-filter="Cholesterol" data-category-title="Point of Care Testing (POCT)" aria-pressed="false">Cholesterol <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['POCT'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['POCT'] ?? '' }}" data-product-filter="Uric Acid" data-category-title="Point of Care Testing (POCT)" aria-pressed="false">Uric Acid <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['POCT'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['POCT'] ?? '' }}" data-product-filter="Lipid" data-category-title="Point of Care Testing (POCT)" aria-pressed="false">Lipid <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                            </div>
                        </div>

                        <div class="product-sidebar__group">
                            <div class="product-sidebar__category-row">
                                <button class="product-sidebar__category product-sidebar__category--parent" type="button" data-category-filter="medical-instruments" data-category-ids="{{ $categoryIdsByName['Instrument and Consumable'] ?? '' }}" data-category-title="Medical Instruments" aria-pressed="false">Medical Instruments</button>
                                <button class="product-sidebar__toggle" type="button" data-category-toggle aria-expanded="false" aria-controls="instrument-subcategories" aria-label="Tampilkan subkategori Medical Instruments"><svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                            </div>
                            <div class="product-sidebar__children" id="instrument-subcategories" hidden>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Instrument and Consumable'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Instrument and Consumable'] ?? '' }}" data-category-title="Medical Instruments" data-product-filter="Urine Analyzer" aria-pressed="false">Urine Analyzer and Strips <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Instrument and Consumable'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Instrument and Consumable'] ?? '' }}" data-category-title="Medical Instruments" data-product-filter="Chemistry Analyzer" aria-pressed="false">Chemistry Analyzer and Reagent <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Instrument and Consumable'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Instrument and Consumable'] ?? '' }}" data-category-title="Medical Instruments" data-product-filter="Electrolyte Analyzer" aria-pressed="false">Electrolyte Analyzer and Reagent <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Instrument and Consumable'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Instrument and Consumable'] ?? '' }}" data-category-title="Medical Instruments" data-product-filter="Hematology Analyzer" aria-pressed="false">Hematology Analyzer and Reagent <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                            </div>
                        </div>

                        <div class="product-sidebar__group">
                            <div class="product-sidebar__category-row">
                                <button class="product-sidebar__category product-sidebar__category--parent" type="button" data-category-filter="medical-consumables" data-category-ids="{{ $categoryIdsByName['Medical Consumable'] ?? '' }}" data-category-title="Medical Consumables" aria-pressed="false">Medical Consumables</button>
                                <button class="product-sidebar__toggle" type="button" data-category-toggle aria-expanded="false" aria-controls="consumable-subcategories" aria-label="Tampilkan subkategori Medical Consumables"><svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                            </div>
                            <div class="product-sidebar__children" id="consumable-subcategories" hidden>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Medical Consumable'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Medical Consumable'] ?? '' }}" data-category-title="Medical Consumables" data-product-filter="Filtertips" aria-pressed="false">Filtertips dkk <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Medical Consumable'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Medical Consumable'] ?? '' }}" data-category-title="Medical Consumables" data-product-filter="Blood Vacuum tube" aria-pressed="false">Blood Vacuum tube <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $categoryIdsByName['Medical Consumable'] ?? 'all' }}" data-category-ids="{{ $categoryIdsByName['Medical Consumable'] ?? '' }}" data-category-title="Medical Consumables" data-product-filter="Swab" aria-pressed="false">Swab <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                            </div>
                        </div>
                    </nav>
                </aside>

                <div class="product-results-area">
                    <p class="product-results sr-only" id="product-results" aria-live="polite"></p>
                    <h2 class="product-active-heading" id="product-active-heading"></h2>
                    <p class="product-no-matches" id="product-no-matches" data-i18n="emptyCategory" hidden>Belum ada produk dalam kategori ini.</p>
                    <div id="product-categories">
                        @foreach ($productGroups as $groupIndex => $productGroup)
                            @php
                                $groupCategories = collect($categories)
                                    ->filter(fn ($category) => in_array($category['name'], $productGroup['categoryNames'], true))
                                    ->values();
                                $groupCategoryIds = $groupCategories->pluck('id')->implode(',');
                                $groupProducts = $groupCategories
                                    ->flatMap(fn ($category) => collect($category['products'])->map(fn ($product) => [
                                        'product' => $product,
                                        'category_id' => $category['id'],
                                    ]))
                                    ->values();
                            @endphp
                            <section class="product-category-section" data-category-ids="{{ $groupCategoryIds }}" aria-labelledby="category-group-{{ $groupIndex }}">
                                <h3 class="product-category-section__title" id="category-group-{{ $groupIndex }}">{{ $productGroup['title'] }}</h3>
                                @if ($groupProducts->isEmpty())
                                    <p class="product-category-section__empty" data-i18n="emptyCategory">Belum ada produk dalam kategori ini.</p>
                                @else
                                    <div class="product-grid">
                                        @foreach ($groupProducts as $productEntry)
                                            @php
                                                $product = $productEntry['product'];
                                            @endphp
                                            <article class="product-card" data-category="{{ $productEntry['category_id'] }}" data-subcategory="{{ $product['subtitle'] }}">
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
                                                    <span class="product-card__badge">{{ $productGroup['title'] }}</span>
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
                </div>
            </div>
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
