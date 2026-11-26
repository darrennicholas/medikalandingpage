<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', ['seo' => $seo])
    @vite(['resources/css/app.css', 'resources/css/careers.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header" id="site-header"><div class="site-header__inner">
        <a class="site-logo" href="{{ route('localized.home', ['locale' => $language]) }}"><img src="{{ asset('images/medika-logo.png') }}" alt="IMI Medika" width="120" height="72"></a>
        <button class="menu-toggle" id="menu-toggle" type="button" aria-controls="primary-navigation" aria-expanded="false" aria-label="Buka menu"><span></span><span></span><span></span></button>
        <nav class="primary-navigation" id="primary-navigation" aria-label="Navigasi utama">
            @include('partials.public-navigation')
            @include('partials.language-switcher')
        </nav>
    </div></header>
    <main class="careers-page">
        <section id="careers-hero" class="careers-hero" aria-labelledby="careers-title">
            <div class="container careers-hero__inner">
                <div class="careers-hero__copy">
                    @php
                        $careerButton = collect($careerIntro['buttons'] ?? [])->first(fn ($button) => filled($button['label'] ?? null) && filled($button['url'] ?? null));
                        $careerButtonUrl = $careerButton['url'] ?? ($careerIntro['cta_url'] ?? null);
                        $careerButtonLabel = $careerButton['label'] ?? ($careerIntro['cta_label'] ?? null);
                    @endphp
                    <h1 id="careers-title">{{ $careerIntro['title'] ?? ($language === 'en' ? 'Grow with us' : 'Berkembang bersama kami') }}</h1>
                    <span class="careers-hero__rule" aria-hidden="true"></span>
                    <p>{{ $careerIntro['body'] ?? ($language === 'en' ? 'Join our team and help support better healthcare in Indonesia.' : 'Bergabunglah bersama kami dan ikut menghadirkan layanan kesehatan yang lebih baik untuk Indonesia.') }}</p>
                    <a class="careers-hero__button" href="{{ $careerButtonUrl ? preg_replace('#^/(id|en|zh)(?=/|$)#', '/'.$language, $careerButtonUrl) : '#lowongan' }}">{{ $careerButtonLabel ?: ($language === 'en' ? 'Explore opportunities' : 'Lihat lowongan') }}</a>
                </div>
            </div>
        </section>
        <section class="careers-openings" id="lowongan" aria-label="{{ $language === 'en' ? 'Open positions' : 'Daftar lowongan' }}">
            <div class="container">
                <div class="careers-grid">
                    @forelse ($jobs as $job)
                        @php($jobTranslation = $job->public_translation ?? $job->translations->firstWhere('lang', $language) ?? $job->translations->firstWhere('lang', 'id'))
                        <article class="careers-card"><div class="careers-card__top"><span class="careers-card__icon" aria-hidden="true">✦</span><span class="careers-card__status">{{ $language === 'en' ? 'Open' : 'Terbuka' }}</span></div><h3>{{ $jobTranslation?->title }}</h3><div class="careers-card__meta"><span>⌖ {{ $job->location }}</span><span>◷ {{ $job->employment_type }}</span></div><p>{{ \Illuminate\Support\Str::limit(trim(strip_tags($jobTranslation?->description ?? '')), 150) }}</p>@if ($job->closing_date)<small>{{ $language === 'en' ? 'Closes' : 'Ditutup' }}: {{ $job->closing_date->format('d M Y') }}</small>@endif<a href="{{ route('localized.careers.show', ['locale' => $language, 'id' => $job->id]) }}">{{ $language === 'en' ? 'View details' : 'Lihat detail' }} <span aria-hidden="true">→</span></a></article>
                    @empty
                        <div class="careers-empty"><span class="careers-empty__icon" aria-hidden="true">✦</span><h3>{{ $language === 'en' ? 'No open positions yet' : 'Belum ada lowongan yang dibuka' }}</h3><p>{{ $language === 'en' ? 'Please check back later for new opportunities with our team.' : 'Kunjungi halaman ini kembali untuk melihat kesempatan terbaru bersama tim kami.' }}</p></div>
                    @endforelse
                </div>
                @if ($jobs->hasPages())<div class="careers-pagination">{{ $jobs->links() }}</div>@endif
            </div>
        </section>
    </main>
    <footer class="site-footer">@include('partials.site-footer', ['footer' => $footer])</footer>
</body>
</html>
