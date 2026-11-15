<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', ['seo' => $seo])
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
            <a class="site-logo" href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}" aria-label="PT Interskala Medika Indonesia, beranda">
                <img src="{{ asset('images/medika-logo.png') }}" alt="IMI Medika" width="120" height="72">
            </a>

            <button class="menu-toggle" id="menu-toggle" type="button" aria-controls="primary-navigation" aria-expanded="false" aria-label="Buka menu">
                <span></span><span></span><span></span>
            </button>

            <nav class="primary-navigation" id="primary-navigation" aria-label="Navigasi utama">
            @include('partials.public-navigation')
            @include('partials.cms-login-link')
                <div class="language-control">
                    <span class="google-mark" aria-hidden="true">G</span>
                    <label class="sr-only" for="language-picker">Pilih bahasa</label>
                    <select id="language-picker" aria-label="Pilih bahasa">
                        <option value="id" @selected(app()->getLocale() === 'id')>Bahasa Indonesia</option>
                        <option value="en" @selected(app()->getLocale() === 'en')>English</option>
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
                <h2 id="product-catalog-title" data-i18n="productHeading">{{ app()->getLocale() === 'en' ? 'Discover Our Products' : 'Temukan Produk Terbaik Kami' }}</h2>
                <p data-i18n="productDescription">{{ app()->getLocale() === 'en' ? 'Explore our range of quality products for healthcare and medical needs' : 'Jelajahi berbagai kategori produk berkualitas tinggi untuk kebutuhan medis dan kesehatan Anda' }}</p>
            </div>

            @php
                $categoryNamesById = collect($categories)->pluck('name', 'id');
                $idsForCategoryNames = fn (array $names): string => collect($categories)
                    ->filter(fn (array $category): bool => in_array($category['name'], $names, true))
                    ->pluck('id')
                    ->implode(',');
                $sidebarGroups = [
                    [
                        'title' => 'Molecular',
                        'filter' => 'molecular',
                        'category_ids' => $idsForCategoryNames(['Molecular']),
                        'children' => [
                            ['label' => 'PCR', 'filter' => 'PCR'],
                            ['label' => 'Extraction', 'filter' => 'Extraction|Ekstraksi'],
                        ],
                    ],
                    [
                        'title' => 'Rapid Test (Master)',
                        'filter' => 'rapid-test-master',
                        'category_ids' => $idsForCategoryNames(['Rapid Test', 'Rapid Test (Master)', 'Rapid Test COVID-19', 'Drugs Abuse']),
                        'children' => [
                            ['label' => app()->getLocale() === 'en' ? 'Infectious Disease: HIV Series' : 'Infeksius HIV Series', 'filter' => 'HIV|Syphilis'],
                            ['label' => app()->getLocale() === 'en' ? 'Infectious Disease: Hepatitis' : 'Infeksius Hepatitis', 'filter' => 'Hepatitis|HBsAg|Anti HBs|Anti-HCV|HAV|HBV'],
                            ['label' => app()->getLocale() === 'en' ? 'Infectious Disease: Tropical' : 'Infeksius Tropical', 'filter' => 'Malaria|Dengue|Chikungunya|Typhoid'],
                            ['label' => 'Tumor Marker', 'filter' => 'Tumor Marker'],
                            ['label' => 'Covid', 'filter' => 'Covid'],
                            ['label' => 'Drugs of Abuse', 'filter' => 'DOA'],
                        ],
                    ],
                    [
                        'title' => 'Point of Care Testing (POCT)',
                        'filter' => 'point-of-care',
                        'category_ids' => $idsForCategoryNames(['Point of Care Testing (POCT)', 'POCT']),
                        'children' => [
                            ['label' => 'Glucose', 'filter' => 'Glucose'],
                            ['label' => 'Cholesterol', 'filter' => 'Cholesterol'],
                            ['label' => 'Uric Acid', 'filter' => 'Uric Acid'],
                            ['label' => 'Lipid', 'filter' => 'Lipid'],
                        ],
                    ],
                    [
                        'title' => 'Medical Instruments',
                        'filter' => 'medical-instruments',
                        'category_ids' => $idsForCategoryNames(['Medical Instruments', 'Instrument and Consumable']),
                        'children' => [
                            ['label' => 'Urine Analyzer and Strips', 'filter' => 'Urine Analyzer'],
                            ['label' => 'Chemistry Analyzer and Reagent', 'filter' => 'Chemistry Analyzer'],
                            ['label' => 'Electrolyte Analyzer and Reagent', 'filter' => 'Electrolyte Analyzer'],
                            ['label' => 'Hematology Analyzer and Reagent', 'filter' => 'Hematology Analyzer'],
                        ],
                    ],
                    [
                        'title' => 'Medical Consumables',
                        'filter' => 'medical-consumables',
                        'category_ids' => $idsForCategoryNames(['Medical Consumables', 'Medical Consumable']),
                        'children' => [
                            ['label' => app()->getLocale() === 'en' ? 'Filter Tips and More' : 'Filtertips dkk', 'filter' => 'Filtertips'],
                            ['label' => 'Blood Vacuum tube', 'filter' => 'Blood Vacuum tube'],
                            ['label' => 'Swab', 'filter' => 'Swab'],
                        ],
                    ],
                ];
                $sidebarGroups = collect($sidebarGroups)->filter(fn ($group) => $group['category_ids'] !== '')->values()->all();
                $groupedCategoryIds = collect($sidebarGroups)->flatMap(fn ($group) => explode(',', $group['category_ids']))->all();
                foreach ($categories as $category) {
                    if (!in_array((string) $category['id'], $groupedCategoryIds, true)) {
                        $sidebarGroups[] = ['title' => $category['name'], 'filter' => $category['id'], 'category_ids' => $category['id'], 'children' => []];
                    }
                }
                $displayCategories = collect($categories);
            @endphp

            <div class="product-filter-controls" hidden>
                <select id="filter-kategori" aria-label="Filter kategori produk">
                    <option value="all" selected data-i18n="allCategories">{{ app()->getLocale() === 'en' ? '-- All Categories --' : '-- Semua Kategori --' }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                    @endforeach
                    @foreach ($sidebarGroups as $sidebarGroup)
                        <option value="{{ $sidebarGroup['filter'] }}">{{ $sidebarGroup['title'] }}</option>
                    @endforeach
                </select>
                <select id="filter-subkategori" aria-label="Filter subkategori produk" disabled>
                    <option value="all" data-i18n="allSubcategories">{{ app()->getLocale() === 'en' ? '-- All Subcategories --' : '-- Semua Subkategori --' }}</option>
                </select>
                <button id="reset-filters" type="button">Reset Filter</button>
            </div>

            <div class="product-catalog__layout" data-catalog-mode="{{ $catalogMode ? 'all' : 'featured' }}">
                <aside class="product-sidebar" aria-label="Kategori produk">
                    <div class="product-sidebar__heading">
                        <h2 data-i18n="categoryLabel">{{ app()->getLocale() === 'en' ? 'Product Categories' : 'Kategori Produk' }}</h2>
                        <span aria-hidden="true"></span>
                    </div>

                    <button class="product-sidebar__category product-sidebar__category--all" type="button" data-category-filter="all" aria-pressed="true" data-i18n="allCategories">{{ app()->getLocale() === 'en' ? 'All Products' : 'Semua Produk' }}</button>
                    <nav class="product-sidebar__list" aria-label="Pilih kategori produk">
                        @foreach ($sidebarGroups as $groupIndex => $sidebarGroup)
                            @php
                                $sidebarCategoryIds = $sidebarGroup['category_ids'] !== '' ? $sidebarGroup['category_ids'] : $sidebarGroup['filter'];
                                $submenuId = 'product-subcategories-'.$groupIndex;
                            @endphp
                            <div class="product-sidebar__group">
                                <div class="product-sidebar__category-row">
                                    <button class="product-sidebar__category product-sidebar__category--parent" type="button" data-category-filter="{{ $sidebarGroup['filter'] }}" data-category-ids="{{ $sidebarCategoryIds }}" data-category-title="{{ $sidebarGroup['title'] }}" aria-pressed="false">{{ $sidebarGroup['title'] }}</button>
                                    <button class="product-sidebar__toggle" type="button" data-category-toggle aria-expanded="false" aria-controls="{{ $submenuId }}" aria-label="Tampilkan subkategori {{ $sidebarGroup['title'] }}"><svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                </div>
                                <div class="product-sidebar__children" id="{{ $submenuId }}" hidden>
                                    @foreach ($sidebarGroup['children'] as $childCategory)
                                        <button class="product-sidebar__category product-sidebar__category--child" type="button" data-category-filter="{{ $sidebarGroup['filter'] }}" data-category-ids="{{ $sidebarCategoryIds }}" data-category-title="{{ $sidebarGroup['title'] }}" data-product-filter="{{ $childCategory['filter'] }}" aria-pressed="false">{{ $childCategory['label'] }} <svg aria-hidden="true"><use href="#icon-chevron"/></svg></button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                </aside>
                <div class="product-results-area">
                    @if ($catalogMode)<form method="GET" class="product-catalog-search"><input type="search" name="q" value="{{ request('q') }}" placeholder="{{ app()->getLocale() === 'en' ? 'Search products...' : 'Cari produk...' }}">@if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif @if(request('subcategory'))<input type="hidden" name="subcategory" value="{{ request('subcategory') }}">@endif<button type="submit">{{ app()->getLocale() === 'en' ? 'Search' : 'Cari' }}</button></form>@endif
                    <p class="product-results sr-only" id="product-results" aria-live="polite"></p>
                    <h2 class="product-active-heading" id="product-active-heading"></h2>
                    <p class="product-no-matches" id="product-no-matches" data-i18n="emptyCategory" hidden>{{ app()->getLocale() === 'en' ? 'No products in this category yet.' : 'Belum ada produk dalam kategori ini.' }}</p>
                    <div id="product-categories">
                        @foreach ($displayCategories as $categoryIndex => $category)
                            <section class="product-category-section" data-category-filter="{{ $category['id'] }}" data-category-ids="{{ $category['id'] }}" aria-labelledby="category-group-{{ $categoryIndex }}">
                                <h3 class="product-category-section__title" id="category-group-{{ $categoryIndex }}">{{ $category['name'] }}</h3>
                                @if (count($category['products']) > 0)
                                <div class="product-grid">
                                    @foreach ($category['products'] as $product)
                                        @php
                                            $isDrugAbuse = strcasecmp($category['name'], 'Drugs Abuse') === 0;
                                            $productImageName = basename($product['image']);
                                            $isDoaSevenProductImage = $productImageName === 'doa-7-new-cutout.png';
                                            $isCompactProductImage = in_array($productImageName, ['doa-10-cup.png', 'doa-3-amp-thc-meth-new.png', 'doa-3-amp-thc-mop-new.png', 'doa-3-revamp-1.png', 'doa-5-new.png', 'doa-6-new.png', 'doa-7-new.png'], true);
                                            $isAlignedDoaProductImage = in_array($productImageName, ['doa-10-cup.png', 'doa-3-amp-thc-meth-new.png', 'doa-3-amp-thc-mop-new.png', 'doa-3-revamp-1.png', 'doa-5-new.png', 'doa-6-new.png', 'doa-7-new.png'], true);
                                            $isCompactProductImageLarger = $productImageName === 'doa-3-amp-thc-mop-new.png';
                                            $compactProductImageSizeClass = match ($productImageName) {
                                                'doa-5-new.png' => ' product-card__image--compact-size-small',
                                                'doa-6-new.png' => ' product-card__image--compact-size-medium',
                                                'doa-7-new.png' => ' product-card__image--compact-size-large',
                                                default => '',
                                            };
                                            $parameterLabels = collect($product['parameters'] ?? []);
                                            $categoryLabel = $category['name'];
                                        @endphp
                                        <article class="product-card" data-category="{{ $category['id'] }}" data-subcategory="{{ $product['subcategory'] }}">
                                            @if ($product['logo_bottom'])
                                                <img class="product-card__watermark" src="{{ $product['logo_bottom'] }}" alt="" loading="lazy">
                                            @endif
                                            <div class="product-card__image-wrap">
                                                @if ($product['image'])
                                                    <img class="product-card__image{{ $isDoaSevenProductImage ? ' product-card__image--doa-seven-right' : '' }}{{ $isAlignedDoaProductImage ? ' product-card__image--doa-aligned' : '' }}{{ $isCompactProductImage ? ' product-card__image--compact' : '' }}{{ $isCompactProductImageLarger ? ' product-card__image--compact-larger' : '' }}{{ $compactProductImageSizeClass }}" src="{{ $product['image'] }}" alt="{{ $product['image_alt'] ?: $product['name'] }}" loading="lazy">
                                                @else
                                                    <span class="product-card__image-empty">{{ app()->getLocale() === 'en' ? 'Product image unavailable' : 'Gambar produk belum tersedia' }}</span>
                                                @endif
                                                @if ($product['logo_top'])
                                                    <span class="product-card__logo"><img src="{{ $product['logo_top'] }}" alt="" loading="lazy"></span>
                                                @endif
                                            </div>
                                            <div class="product-card__content">
                                                <h4 class="product-card__title" title="{{ $product['name'] }}">{{ $product['name'] }}</h4>
                                                @if ($parameterLabels->isNotEmpty())
                                                    <div class="product-card__parameters" aria-label="Parameter produk">
                                                        @foreach ($parameterLabels as $parameterLabel)
                                                            <span class="product-card__parameter-chip">{{ trim($parameterLabel) }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                <p class="product-card__subtitle">{{ $isDrugAbuse ? 'Rapid Test (Master)' : $product['subtitle'] }}</p>
                                                <span class="product-card__badge">{{ $categoryLabel }}</span>
                                                <div class="product-card__actions">
                                                    <a class="product-card__buy" href="{{ $product['buy_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Beli {{ $product['name'] }}"><svg aria-hidden="true"><use href="#icon-cart"/></svg><span data-i18n="buyProduct">{{ app()->getLocale() === 'en' ? 'Buy' : 'Beli' }}</span></a>
                                                    @if (!empty($product['brochure_url']))
                                                        <button class="product-card__brochure" type="button" data-brochure-url="{{ $product['brochure_url'] }}" data-product-name="{{ $product['name'] }}" aria-label="Preview brosur {{ $product['name'] }}"><svg aria-hidden="true"><use href="#icon-pdf"/></svg><span data-i18n="brochure">{{ app()->getLocale() === 'en' ? 'Brochure' : 'Brosur' }}</span></button>
                                                    @else
                                                        <button class="product-card__brochure" type="button" disabled aria-label="Brosur {{ $product['name'] }} belum tersedia"><svg aria-hidden="true"><use href="#icon-pdf"/></svg><span data-i18n="brochure">{{ app()->getLocale() === 'en' ? 'Brochure' : 'Brosur' }}</span></button>
                                                    @endif
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                                @else
                                    <p class="product-category-section__empty">{{ app()->getLocale() === 'en' ? 'No products in this category yet.' : 'Belum ada produk dalam kategori ini.' }}</p>
                                @endif
                            </section>
                        @endforeach
                    </div>

                    <nav class="product-pagination" id="product-pagination" aria-label="Halaman produk">@if ($catalogMode)<span>{{ app()->getLocale() === 'en' ? 'Showing' : 'Menampilkan' }} {{ $catalogPage->firstItem() ?? 0 }}–{{ $catalogPage->lastItem() ?? 0 }} {{ app()->getLocale() === 'en' ? 'of' : 'dari' }} {{ $catalogPage->total() }}</span> @if ($catalogPage->previousPageUrl())<a href="{{ $catalogPage->previousPageUrl() }}">{{ app()->getLocale() === 'en' ? '‹ Previous' : '‹ Sebelumnya' }}</a>@endif @if ($catalogPage->nextPageUrl())<a href="{{ $catalogPage->nextPageUrl() }}">{{ app()->getLocale() === 'en' ? 'Next ›' : 'Berikutnya ›' }}</a>@endif @endif</nav>
                    @if ($catalogMode)
                        <a class="product-catalog__all-link" href="{{ route('localized.products', ['locale' => app()->getLocale()]) }}">{{ app()->getLocale() === 'en' ? 'Back to Featured Products' : 'Kembali ke Produk Unggulan' }}</a>
                    @else
                        <a class="product-catalog__all-link" href="{{ route('localized.products.all', ['locale' => app()->getLocale()]) }}" data-catalog-link>{{ app()->getLocale() === 'en' ? 'View All Products' : 'Lihat Semua Produk' }}</a>
                    @endif
                </div>
            </div>
        </section>
    </main>

    <div class="product-modal" id="product-modal" hidden>
        <div class="product-modal__backdrop" data-close-modal></div>
        <div class="product-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="brochure-modal-title">
            <div class="product-modal__header">
                <div class="product-modal__heading"><svg aria-hidden="true"><use href="#icon-pdf"/></svg><div><strong id="brochure-modal-title" data-i18n="brochurePreview">{{ app()->getLocale() === 'en' ? 'Brochure Preview' : 'Preview Brosur' }}</strong><span id="brochure-product-name"></span></div></div>
                <button class="product-modal__close" type="button" data-close-modal aria-label="Tutup pratinjau brosur"><svg aria-hidden="true"><use href="#icon-close"/></svg></button>
            </div>
            <div class="product-modal__body">
                <iframe id="brochure-frame" title="Preview brosur produk" loading="lazy" hidden></iframe>
                <div class="product-modal__error" id="brochure-error" hidden><svg aria-hidden="true"><use href="#icon-pdf"/></svg><h3 data-i18n="brochureUnavailable">{{ app()->getLocale() === 'en' ? 'Unable to Load Brochure' : 'Gagal Memuat Brosur' }}</h3><p data-i18n="brochureUnavailableMessage">{{ app()->getLocale() === 'en' ? 'The PDF file could not be displayed.' : 'File PDF tidak dapat ditampilkan.' }}</p></div>
            </div>
            <div class="product-modal__footer"><span>{{ app()->getLocale() === 'en' ? 'Open the full PDF in a new tab to enlarge the brochure.' : 'Perbesar brosur dengan membuka PDF lengkap di tab baru.' }}</span><a id="brochure-open-pdf" href="#" target="_blank" rel="noopener noreferrer" hidden aria-label="Buka PDF di tab baru"><svg aria-hidden="true"><use href="#icon-pdf"/></svg><span>{{ app()->getLocale() === 'en' ? 'Open PDF in a new tab' : 'Buka PDF di tab baru' }}</span></a></div>
        </div>
    </div>

    <footer class="site-footer">
        @include('partials.site-footer', ['footer' => $footer])
    </footer>
</body>
</html>
