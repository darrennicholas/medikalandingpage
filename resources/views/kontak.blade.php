<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', ['seo' => $seo])
    @vite(['resources/css/app.css', 'resources/css/contact.css', 'resources/js/app.js', 'resources/js/contact-map.js'])
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
        <symbol id="icon-linkedin" viewBox="0 0 24 24"><path fill="currentColor" d="M6.5 8.4H3.2V21h3.3V8.4ZM4.85 3a1.95 1.95 0 1 0 0 3.9 1.95 1.95 0 0 0 0-3.9ZM21 13.4c0-3.5-1.9-5.2-4.4-5.2-2 0-2.9 1.1-3.4 1.9V8.4H9.9V21h3.3v-6.2c0-1.6.3-3.2 2.4-3.2 2 0 2 1.9 2 3.3V21H21v-7.6Z"/></symbol>
        <symbol id="icon-tiktok" viewBox="0 0 24 24"><path d="M14 3v11.5a4.5 4.5 0 1 1-4.5-4.5m4.5-7c.6 3.5 2.4 5.3 6 5.6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></symbol>
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
                @include('partials.language-switcher')
            </nav>
        </div>
    </header>

    <main class="contact-main">
        <div class="container">
            <div class="contact-heading">
                <h1 data-i18n="contactPageTitle">{{ app()->getLocale() === 'en' ? 'Contact Us' : 'Hubungi Kami' }}</h1>
                <span class="contact-heading__rule" aria-hidden="true"></span>
                <p data-i18n="contactPageIntro">{{ app()->getLocale() === 'en' ? 'Our team is ready to help with questions about IMI Medika products and services.' : 'Kami siap membantu Anda dengan segala pertanyaan dan kebutuhan mengenai produk dan layanan IMI Medika. Tim kami yang profesional siap memberikan solusi terbaik untuk Anda.' }}</p>
            </div>

            @php
                $language = app()->getLocale();
                $contactTranslation = $contactInfo?->public_translation ?? $contactInfo?->translations->firstWhere('lang', $language) ?? $contactInfo?->translations->firstWhere('lang', 'id');
                $mapEmbedUrl = null;
                if ($contactInfo?->latitude !== null && $contactInfo?->longitude !== null) {
                    $mapEmbedUrl = 'https://www.google.com/maps?q='.urlencode($contactInfo->latitude.','.$contactInfo->longitude).'&output=embed';
                } elseif ($contactInfo?->maps_url) {
                    $mapEmbedUrl = str_contains($contactInfo->maps_url, 'output=embed') || str_contains($contactInfo->maps_url, '/maps/embed')
                        ? $contactInfo->maps_url
                        : 'https://www.google.com/maps?q='.urlencode($contactInfo->maps_url).'&output=embed';
                }
            @endphp

            @if ($contactInfo)
                <div class="factory-grid factory-grid--single">
                    <article class="factory-card" aria-labelledby="contact-details-title">
                        <h2 id="contact-details-title"><svg aria-hidden="true"><use href="#icon-factory"/></svg>{{ $language === 'en' ? 'Contact Details' : 'Informasi Kontak' }}</h2>
                        <div class="factory-card__body">
                            @if ($contactTranslation?->address)
                                <div class="factory-card__address"><svg aria-hidden="true"><use href="#icon-pin"/></svg><p>{{ $contactTranslation->address }}</p></div>
                            @endif
                            <div class="factory-card__details">
                                @if ($contactInfo->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactInfo->phone) }}"><span class="factory-card__round-icon"><svg aria-hidden="true"><use href="#icon-phone"/></svg></span>{{ $contactInfo->phone }}</a>@endif
                                @if ($contactInfo->email)<a href="mailto:{{ $contactInfo->email }}"><span class="factory-card__round-icon"><svg aria-hidden="true"><use href="#icon-mail"/></svg></span>{{ $contactInfo->email }}</a>@endif
                                @if ($contactInfo->maps_url)<a href="{{ $contactInfo->maps_url }}" target="_blank" rel="noopener noreferrer"><span class="factory-card__round-icon"><svg aria-hidden="true"><use href="#icon-globe"/></svg></span>{{ $language === 'en' ? 'Open location map' : 'Buka lokasi di peta' }}</a>@endif
                            </div>
                            @if ($contactTranslation?->office_hours)
                                <div class="factory-card__hours">
                                    <h3><svg aria-hidden="true"><use href="#icon-clock"/></svg><span>{{ $language === 'en' ? 'Operating Hours' : 'Jam Operasional' }}</span></h3>
                                    <p>{{ $contactTranslation->office_hours }}</p>
                                </div>
                            @endif
                            @if ($contactInfo->latitude !== null && $contactInfo->longitude !== null)
                                <section class="contact-map" aria-label="{{ $language === 'en' ? 'Company location map' : 'Peta lokasi perusahaan' }}">
                                    <h3>{{ $language === 'en' ? 'Our location' : 'Lokasi Kami' }}</h3>
                                    <div class="contact-map__canvas" data-contact-map data-latitude="{{ $contactInfo->latitude }}" data-longitude="{{ $contactInfo->longitude }}" data-map-label="{{ $contactTranslation?->address ?: 'PT Interskala Medika Indonesia' }}" aria-label="{{ $language === 'en' ? 'Interactive map of the company location' : 'Peta interaktif lokasi perusahaan' }}"></div>
                                    @if ($contactInfo->maps_url)<a class="contact-map__link" href="{{ $contactInfo->maps_url }}" target="_blank" rel="noopener noreferrer">{{ $language === 'en' ? 'Open directions' : 'Buka petunjuk arah' }} ↗</a>@endif
                                </section>
                            @elseif ($mapEmbedUrl)
                                <section class="contact-map" aria-label="{{ $language === 'en' ? 'Company location map' : 'Peta lokasi perusahaan' }}">
                                    <h3>{{ $language === 'en' ? 'Our location' : 'Lokasi Kami' }}</h3>
                                    <iframe title="{{ $language === 'en' ? 'Company location map' : 'Peta lokasi perusahaan' }}" src="{{ $mapEmbedUrl }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </section>
                            @endif
                        </div>
                    </article>
                </div>
            @endif

            @if ($socialLinks->isNotEmpty())
                <section class="contact-social" aria-labelledby="social-title">
                    <h2 id="social-title">{{ $language === 'en' ? 'Follow Us' : 'Ikuti Kami di Media Sosial' }}</h2>
                    <span class="contact-social__rule" aria-hidden="true"></span>
                    <div class="contact-social__links">
                        @foreach ($socialLinks as $socialLink)
                            @php $icon = strtolower($socialLink->platform); @endphp
                            <x-social-link-preview :profile-url="$socialLink->url" :platform="$socialLink->platform" :media="$socialLink->previewMedia" link-class="contact-social__trigger">
                                <svg aria-hidden="true"><use href="#icon-{{ in_array($icon, ['instagram', 'linkedin', 'tiktok'], true) ? $icon : 'globe' }}"/></svg>
                            </x-social-link-preview>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </main>

    <footer class="site-footer">
        @include('partials.site-footer', ['footer' => $footer])
    </footer>
</body>
</html>
