<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', ['seo' => $seo])
    @vite(['resources/css/app.css', 'resources/css/careers.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header"><div class="site-header__inner">
        <a class="site-logo" href="{{ route('localized.home', ['locale' => $language]) }}"><img src="{{ asset('images/medika-logo.png') }}" alt="IMI Medika" width="120" height="72"></a>
        <button class="menu-toggle" id="menu-toggle" type="button" aria-controls="primary-navigation" aria-expanded="false" aria-label="Buka menu"><span></span><span></span><span></span></button>
        <nav class="primary-navigation" id="primary-navigation" aria-label="Navigasi utama">
            @include('partials.public-navigation')
            @include('partials.cms-login-link')
            <div class="language-control"><label class="sr-only" for="language-picker">Pilih bahasa</label><select id="language-picker" aria-label="Pilih bahasa"><option value="id" @selected($language === 'id')>Bahasa Indonesia</option><option value="en" @selected($language === 'en')>English</option></select></div>
        </nav>
    </div></header>
    <main class="careers-page">
        <section class="careers-hero" aria-labelledby="careers-title">
            <div class="container careers-hero__inner">
                <div class="careers-hero__copy">
                    <h1 id="careers-title">{{ $careerIntro['title'] ?? ($language === 'en' ? 'Grow with us' : 'Berkembang bersama kami') }}</h1>
                    <p>{{ $careerIntro['body'] ?? ($language === 'en' ? 'Join our team and help support better healthcare in Indonesia.' : 'Bergabunglah bersama kami dan ikut menghadirkan layanan kesehatan yang lebih baik untuk Indonesia.') }}</p>
                    <a class="careers-hero__button" href="#lowongan">{{ $language === 'en' ? 'Explore opportunities' : 'Lihat lowongan' }}</a>
                </div>
                <div class="careers-hero__visual"><img src="{{ $careerIntro['image_url'] ?? asset('images/gambartentangmedika.jpeg') }}" alt="{{ ($careerIntro['image_alt'] ?? null) ?: ($language === 'en' ? 'IMI Medika team' : 'Tim IMI Medika') }}"></div>
            </div>
        </section>
        <section class="careers-openings" id="lowongan" aria-labelledby="careers-openings-title">
            <div class="container">
                <div class="careers-openings__heading"><div><h2 id="careers-openings-title">{{ $language === 'en' ? 'Explore open positions' : 'Temukan peluang karir' }}</h2><p>{{ $language === 'en' ? 'Discover opportunities to make an impact with our team.' : 'Temukan kesempatan untuk berkarya dan bertumbuh bersama tim kami.' }}</p></div></div>
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
