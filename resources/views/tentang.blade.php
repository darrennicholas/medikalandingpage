<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', ['seo' => $seo])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="about-page">
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
        <section class="about-hero" aria-labelledby="about-hero-title">
            <div class="container about-hero__content">
                <h1 id="about-hero-title">PT Interskala Medika Indonesia</h1>
                <span class="about-hero__rule" aria-hidden="true"></span>
                <p data-i18n="aboutTagline">IMPORTIR | DISTRIBUTOR | MANUFAKTUR ALAT KESEHATAN</p>
            </div>
        </section>

        @php
            $aboutBlock = $blocks->get('about_company');
            $visionBlock = $blocks->get('about_vision');
            $visionText = $visionBlock['body'] ?? null;
        @endphp

        @if ($aboutBlock && (!empty($aboutBlock['title']) || !empty($aboutBlock['body']) || !empty($aboutBlock['image_url'])))
            <section class="about-intro" aria-labelledby="about-intro-title">
                <div class="container">
                    <div class="section-title">
                        <h2 id="about-intro-title">{{ $aboutBlock['title'] ?: (app()->getLocale() === 'en' ? 'About Us' : 'Tentang Kami') }}</h2>
                        @if (!empty($aboutBlock['subtitle']))<p>{{ $aboutBlock['subtitle'] }}</p>@endif
                    </div>
                    <div class="about-intro__content">
                        @if (!empty($aboutBlock['image_url']))
                            <img class="about-intro__image" src="{{ $aboutBlock['image_url'] }}" alt="{{ $aboutBlock['image_alt'] ?: $aboutBlock['title'] }}" loading="lazy">
                        @endif
                        @if (!empty($aboutBlock['body']))
                            <div class="about-intro__copy">{!! $aboutBlock['body'] !!}</div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        @if (!empty($visionText) || $missions->isNotEmpty())
            <section class="vision-mission" aria-labelledby="vision-mission-title">
                <div class="container">
                    <div class="section-title">
                        <h2 id="vision-mission-title">{{ $language === 'en' ? 'Vision & Mission' : 'Visi & Misi' }}</h2>
                    </div>
                    <div class="vision-mission__cards">
                        @if (!empty($visionText))
                            <article class="vision-card">
                                <span class="vision-card__icon" aria-hidden="true">
                                    <svg viewBox="0 0 64 64" fill="none"><path d="M5 32s10-16 27-16 27 16 27 16S49 48 32 48 5 32 5 32Z" stroke="currentColor" stroke-width="5"/><circle cx="32" cy="32" r="10" stroke="currentColor" stroke-width="5"/><circle cx="32" cy="32" r="3" fill="currentColor"/></svg>
                                </span>
                                <h3>{{ $visionBlock['title'] ?: ($language === 'en' ? 'Vision' : 'Visi') }}</h3>
                                <div>{!! $visionText !!}</div>
                            </article>
                        @endif
                        @if ($missions->isNotEmpty())
                            <article class="vision-card vision-card--mission">
                                <span class="vision-card__icon" aria-hidden="true">
                                    <svg viewBox="0 0 64 64" fill="none"><circle cx="32" cy="32" r="25" stroke="currentColor" stroke-width="5"/><circle cx="32" cy="32" r="15" stroke="currentColor" stroke-width="5"/><circle cx="32" cy="32" r="5" fill="currentColor"/></svg>
                                </span>
                                <h3>{{ $language === 'en' ? 'Mission' : 'Misi' }}</h3>
                                <div>
                                    @foreach ($missions as $mission)
                                        @php $missionTranslation = $mission->public_translation ?? $mission->translations->firstWhere('lang', $language) ?? $mission->translations->firstWhere('lang', 'id'); @endphp
                                        @if ($missionTranslation?->text)<p>{{ $missionTranslation->text }}</p>@endif
                                    @endforeach
                                </div>
                            </article>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        @if ($summary)
            <section class="about-stats" aria-label="{{ $language === 'en' ? 'Company statistics' : 'Statistik perusahaan' }}">
                <div class="container about-stats__grid">
                    @if ($yearsEstablished !== null)<div class="about-stats__card"><strong>{{ $yearsEstablished }}</strong><span>{{ $language === 'en' ? 'Years in business' : 'Tahun Pengalaman' }}</span></div>@endif
                    <div class="about-stats__card"><strong>{{ number_format($summary->partner_count) }}</strong><span>{{ $language === 'en' ? 'Partners' : 'Mitra' }}</span></div>
                    <div class="about-stats__card"><strong>{{ $summary->province_count }}</strong><span>{{ $language === 'en' ? 'Provinces covered' : 'Jangkauan Provinsi' }}</span></div>
                </div>
            </section>
        @endif

        @if ($certificates->isNotEmpty())
            <section class="certificates" aria-labelledby="certificates-title">
                <div class="container">
                    <div class="section-title">
                        <h2 id="certificates-title">{{ $language === 'en' ? 'Certificates & Awards' : 'Sertifikat & Penghargaan' }}</h2>
                    </div>
                    <div class="certificate-carousel" aria-label="{{ $language === 'en' ? 'Company certificates' : 'Sertifikat perusahaan' }}">
                        <div class="certificate-carousel__viewport">
                            <div class="certificate-carousel__track" id="certificate-track">
                                @foreach ($certificates as $certificate)
                                    @php $certificateTranslation = $certificate->public_translation ?? $certificate->translations->firstWhere('lang', $language) ?? $certificate->translations->firstWhere('lang', 'id'); @endphp
                                    @if ($certificate->public_image_url)
                                        <article class="certificate-card">
                                            <h3>{{ $certificateTranslation?->title }}</h3>
                                            @if ($certificate->issued_year)<span>{{ $certificate->issued_year }}</span>@endif
                                            <img src="{{ $certificate->public_image_url }}" alt="{{ $certificate->public_image_alt ?: $certificateTranslation?->title }}" loading="lazy">
                                            @if ($certificateTranslation?->description)<p>{{ $certificateTranslation->description }}</p>@endif
                                        </article>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="certificate-carousel__dots" id="certificate-dots" aria-label="{{ $language === 'en' ? 'Select certificate' : 'Pilih sertifikat' }}"></div>
                    </div>
                </div>
            </section>
        @endif    </main>

    <footer class="site-footer">
        @include('partials.site-footer', ['footer' => $footer])
    </footer>
</body>
</html>
