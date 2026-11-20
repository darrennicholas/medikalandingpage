<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', ['seo' => $seo])
    @vite(['resources/css/app.css', 'resources/css/documentation.css', 'resources/js/app.js'])
</head>
<body class="documentation-page {{ $selectedType ? 'documentation-page--listing' : '' }}">
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
                @include('partials.language-switcher')
            </nav>
        </div>
    </header>

    <main class="documentation-main {{ $selectedType ? 'documentation-main--listing' : '' }}">
        @if (!$selectedType)
            <section class="documentation-hero" aria-labelledby="documentation-title">
                <div class="container documentation-hero__content">
                    <h1 id="documentation-title" data-i18n="documentationTitle">{{ app()->getLocale() === 'en' ? 'DOCUMENTATION' : 'DOKUMENTASI' }}</h1>
                    <p data-i18n="documentationIntro">{{ app()->getLocale() === 'en' ? 'Discover the latest healthcare articles and news' : 'Temukan artikel dan berita terkini seputar dunia kesehatan' }}</p>
                    <a href="#content" data-i18n="exploreNow">{{ app()->getLocale() === 'en' ? 'EXPLORE NOW' : 'JELAJAHI SEKARANG' }}</a>
                </div>
            </section>
        @endif

        <section class="documentation-content {{ $selectedType ? 'documentation-content--listing' : '' }}" id="content" aria-labelledby="media-heading">
            <div class="container">
                @if ($selectedType)
                    <div class="publication-listing__intro">
                        <a class="publication-listing__back public-back-button" href="{{ route('localized.documentation', ['locale' => $language]) }}">{{ $language === 'en' ? 'Back' : 'Kembali' }}</a>
                        <p class="publication-listing__eyebrow">IMI Medika Media</p>
                        <h1 id="media-heading">{{ $selectedType === 'article' ? ($language === 'en' ? 'Articles' : 'Daftar Artikel') : ($language === 'en' ? 'Latest News' : 'Berita Terkini') }}</h1>
                        <p>{{ $selectedType === 'article'
                            ? ($language === 'en' ? 'Explore health articles from IMI Medika.' : 'Temukan artikel kesehatan terkini yang informatif dan bermanfaat.')
                            : ($language === 'en' ? 'Follow the latest updates and activities from IMI Medika.' : 'Ikuti perkembangan terbaru seputar kesehatan, layanan, dan kegiatan IMI Medika.') }}</p>
                    </div>

                    <form class="publication-listing__toolbar" method="GET" action="{{ route($selectedType === 'article' ? 'localized.articles' : 'localized.news', ['locale' => $language]) }}">
                        <label class="publication-listing__search">
                            <span class="sr-only">{{ $language === 'en' ? 'Search articles and news' : 'Cari artikel atau berita' }}</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg>
                            <input type="search" name="q" value="{{ $search }}" placeholder="{{ $language === 'en' ? ($selectedType === 'article' ? 'Search articles...' : 'Search news...') : ($selectedType === 'article' ? 'Cari artikel...' : 'Cari berita...') }}">
                        </label>
                        <div class="publication-listing__actions">
                            <details class="publication-listing__dropdown">
                                <summary><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 5h18l-7 8v6l-4 2v-8z"/></svg> Filter</summary>
                                <div class="publication-listing__panel">
                                    <label for="publication-year">{{ $language === 'en' ? 'Year' : 'Tahun' }}</label>
                                    <select id="publication-year" name="year"><option value="">{{ $language === 'en' ? 'All years' : 'Semua tahun' }}</option>@foreach ($years as $availableYear)<option value="{{ $availableYear }}" @selected($year === $availableYear)>{{ $availableYear }}</option>@endforeach</select>
                                    <button type="submit">{{ $language === 'en' ? 'Apply' : 'Terapkan' }}</button>
                                </div>
                            </details>
                            <details class="publication-listing__dropdown">
                                <summary><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 3 5 6H7l5-6Zm0 18-5-6h10l-5 6Z"/></svg> {{ $language === 'en' ? 'Sort' : 'Urutkan' }}</summary>
                                <div class="publication-listing__panel">
                                    <label for="publication-sort">{{ $language === 'en' ? 'Order' : 'Urutan' }}</label>
                                    <select id="publication-sort" name="sort"><option value="newest" @selected($sort === 'newest')>{{ $language === 'en' ? 'Newest' : 'Terbaru' }}</option><option value="oldest" @selected($sort === 'oldest')>{{ $language === 'en' ? 'Oldest' : 'Terlama' }}</option></select>
                                    <button type="submit">{{ $language === 'en' ? 'Apply' : 'Terapkan' }}</button>
                                </div>
                            </details>
                        </div>
                    </form>
                @else
                    <div class="documentation-heading">
                        <h2 id="media-heading" data-i18n="mediaHeading">IMI MEDIKA MEDIA</h2>
                        <span aria-hidden="true"></span>
                    </div>

                    <!-- Font Awesome Free icons by Fonticons, Inc. (CC BY 4.0): https://fontawesome.com/license/free -->
                    <div class="media-grid">
                        <a class="media-card" href="{{ route('localized.articles', ['locale' => $language]) }}" target="_blank" rel="noopener noreferrer" aria-label="Lihat artikel selengkapnya di tab baru">
                            <svg viewBox="0 0 384 512" aria-hidden="true"><path fill="currentColor" d="M64 0C28.7 0 0 28.7 0 64v384c0 35.3 28.7 64 64 64h256c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64Zm192 0v128h128L256 0ZM112 256h160c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16Zm0 64h160c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16Zm0 64h160c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16Z"/></svg>
                            <h3 data-i18n="articleCardTitle">{{ app()->getLocale() === 'en' ? 'Articles' : 'Artikel' }}</h3>
                            <span data-i18n="readMore">{{ app()->getLocale() === 'en' ? 'Read more' : 'Selengkapnya' }}</span>
                        </a>
                        <a class="media-card" href="{{ route('localized.news', ['locale' => $language]) }}" target="_blank" rel="noopener noreferrer" aria-label="Lihat berita selengkapnya di tab baru">
                            <svg viewBox="0 0 512 512" aria-hidden="true"><path fill="currentColor" d="M96 96c0-35.3 28.7-64 64-64h288c35.3 0 64 28.7 64 64v320c0 35.3-28.7 64-64 64H80c-44.2 0-80-35.8-80-80V128c0-17.7 14.3-32 32-32s32 14.3 32 32v272c0 8.8 7.2 16 16 16s16-7.2 16-16V96Zm64 24v80c0 13.3 10.7 24 24 24h112c13.3 0 24-10.7 24-24v-80c0-13.3-10.7-24-24-24H184c-13.3 0-24 10.7-24 24Zm208-8c0 8.8 7.2 16 16 16h48c8.8 0 16-7.2 16-16s-7.2-16-16-16h-48c-8.8 0-16 7.2-16 16Zm0 96c0 8.8 7.2 16 16 16h48c8.8 0 16-7.2 16-16s-7.2-16-16-16h-48c-8.8 0-16 7.2-16 16ZM160 304c0 8.8 7.2 16 16 16h256c8.8 0 16-7.2 16-16s-7.2-16-16-16H176c-8.8 0-16 7.2-16 16Zm0 96c0 8.8 7.2 16 16 16h256c8.8 0 16-7.2 16-16s-7.2-16-16-16H176c-8.8 0-16 7.2-16 16Z"/></svg>
                            <h3 data-i18n="newsCardTitle">{{ app()->getLocale() === 'en' ? 'News' : 'Berita' }}</h3>
                            <span data-i18n="readMore">{{ app()->getLocale() === 'en' ? 'Read more' : 'Selengkapnya' }}</span>
                        </a>
                    </div>

                @endif

                @if ($selectedType === 'article')
                    <section class="public-documentation" @if ($selectedType) aria-label="{{ $language === 'en' ? 'Articles' : 'Daftar artikel' }}" @else aria-labelledby="public-articles-title" @endif>
                        @if (!$selectedType)
                            <div class="documentation-heading"><h2 id="public-articles-title">{{ $language === 'en' ? 'Articles' : 'Artikel' }}</h2><span aria-hidden="true"></span></div>
                        @endif
                        <div class="public-documentation__grid">
                            @foreach ($articles as $article)
                                <article class="public-documentation__card">
                                    @include('partials.publication-card', ['item' => $article, 'itemUrl' => route('localized.documentation.show', ['locale' => $language, 'slug' => $article->public_slug]), 'kindLabel' => $language === 'en' ? 'Article' : 'Artikel'])
                                </article>
                            @endforeach
                        </div>
                        @if ($articles->isNotEmpty())
                            {{ $articles->links() }}
                        @else
                            <p class="public-documentation__empty">{{ $language === 'en' ? 'No published articles yet.' : 'Belum ada artikel yang dipublikasikan.' }}</p>
                        @endif
                    </section>
                @endif

                @if ($selectedType === 'activity')
                    <section class="public-documentation" @if ($selectedType) aria-label="{{ $language === 'en' ? 'Latest news' : 'Daftar berita' }}" @else aria-labelledby="public-activities-title" @endif>
                        @if (!$selectedType)
                            <div class="documentation-heading"><h2 id="public-activities-title">{{ $language === 'en' ? 'Latest News' : 'Berita Terkini' }}</h2><span aria-hidden="true"></span></div>
                        @endif
                        <div class="public-documentation__grid">
                            @foreach ($activities as $activity)
                                <article class="public-documentation__card">
                                    @include('partials.publication-card', ['item' => $activity, 'itemUrl' => ($activity->public_slug ? route('localized.news.show', ['locale' => $language, 'slug' => $activity->public_slug]) : route('localized.documentation.activity', ['locale' => $language, 'id' => $activity->id])), 'kindLabel' => $language === 'en' ? 'News' : 'Berita'])
                                    @if (!empty($activity->public_gallery))
                                        <div class="public-documentation__gallery" aria-label="{{ $language === 'en' ? 'Activity gallery' : 'Galeri kegiatan' }}">
                                            @foreach (array_slice($activity->public_gallery, 0, 4) as $image)
                                                <a href="{{ ($activity->public_slug ? route('localized.news.show', ['locale' => $language, 'slug' => $activity->public_slug]) : route('localized.documentation.activity', ['locale' => $language, 'id' => $activity->id])) }}" target="_blank" rel="noopener noreferrer"><x-public-image :src="$image['url']" :alt="$image['alt'] ?: $image['caption'] ?: $activity->public_title" sizes="(max-width: 640px) 100vw, 33vw"/></a>
                                            @endforeach
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                        @if ($activities->isNotEmpty())
                            {{ $activities->links() }}
                        @else
                            <p class="public-documentation__empty">{{ $language === 'en' ? 'No published news yet.' : 'Belum ada berita yang dipublikasikan.' }}</p>
                        @endif
                    </section>
                @endif
            </div>
        </section>
    </main>

    <footer class="site-footer">
        @include('partials.site-footer', ['footer' => $footer])
    </footer>
</body>
</html>
