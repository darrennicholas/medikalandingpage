<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', ['seo' => $seo])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
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
        @if ($slides->isNotEmpty())
            <section class="hero{{ $slides->count() > 1 ? ' hero--slider' : '' }}" id="beranda" aria-labelledby="hero-title" data-hero-slider>
                @foreach ($slides as $slideIndex => $slide)
                    <div class="hero__slide" data-hero-slide @if ($slideIndex !== 0) hidden @endif aria-hidden="{{ $slideIndex === 0 ? 'false' : 'true' }}">
                        <div class="container hero__content">
                            @if ($slide->public_title)<h1 id="hero-title">{{ $slide->public_title }}</h1>@endif
                            @if ($slide->public_subtitle)<p>{{ $slide->public_subtitle }}</p>@endif
                            @if ($slide->public_cta_label && $slide->cta_url)<a class="contact-cta__button" href="{{ preg_replace('#^/(id|en)(?=/|$)#', '/'.app()->getLocale(), $slide->cta_url) }}">{{ $slide->public_cta_label }}</a>@endif
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        @if ($blocks->has('home_coverage'))
            @php($coverage = $blocks->get('home_coverage'))
            <section class="coverage" id="jangkauan" aria-labelledby="coverage-title">
                <div class="container">
                    <div class="section-title">
                        <h2 id="coverage-title">{{ $coverage['title'] }}</h2>
                        @if ($coverage['subtitle'])<p>{{ $coverage['subtitle'] }}</p>@endif
                    </div>
                    <div class="coverage__content">
                        <div class="coverage__info">
                            @if ($coverage['subtitle'])<h3>{{ $coverage['subtitle'] }}</h3>@endif
                            @if ($coverage['body'])<div>{!! $coverage['body'] !!}</div>@endif
                            @if (! empty($coverage['metrics']))
                                <div class="coverage__stats" aria-label="{{ app()->getLocale() === 'en' ? 'Coverage statistics' : 'Statistik jangkauan' }}">
                                    @foreach ($coverage['metrics'] as $metric)
                                        @if (filled($metric['value'] ?? null) && filled($metric['label'] ?? null))
                                            <div class="stat-card"><strong>{{ $metric['value'] }}</strong><span>{{ $metric['label'] }}</span></div>
                                        @endif
                                    @endforeach
                                </div>
                            @elseif ($summary)
                                <div class="coverage__stats" aria-label="Statistik jangkauan">
                                    <div class="stat-card"><strong>{{ $summary->province_count }}</strong><span>{{ app()->getLocale() === 'en' ? 'Provinces' : 'Provinsi' }}</span></div>
                                    <div class="stat-card"><strong>{{ number_format($summary->partner_count) }}</strong><span>{{ app()->getLocale() === 'en' ? 'Partners' : 'Mitra' }}</span></div>
                                </div>
                            @endif
                        </div>
                        @if ($coverage['image_url'])<figure class="coverage__map"><img src="{{ $coverage['image_url'] }}" alt="{{ $coverage['image_alt'] ?? '' }}" loading="lazy" width="1300" height="591"></figure>@endif
                    </div>
                </div>
            </section>
        @endif

        @if ($blocks->has('home_cta'))
            @php($homeCta = $blocks->get('home_cta'))
            <section class="contact-cta" id="kontak" aria-labelledby="contact-title">
                <div class="container">
                    @if ($homeCta['title'])<h2 id="contact-title">{{ $homeCta['title'] }}</h2>@endif
                    @if ($homeCta['body'])<div>{{ $homeCta['body'] }}</div>@endif
                    @if (! empty($homeCta['buttons']))
                        <div class="contact-cta__actions">
                            @foreach ($homeCta['buttons'] as $button)
                                @if (filled($button['label'] ?? null) && filled($button['url'] ?? null))
                                    @php($buttonUrl = preg_replace('#^/(id|en)(?=/|$)#', '/'.app()->getLocale(), $button['url']))
                                    @php($isExternalButton = Illuminate\Support\Str::startsWith($buttonUrl, ['https://', 'http://']))
                                    <a class="contact-cta__button contact-cta__button--{{ $button['style'] ?? 'primary' }}" href="{{ $buttonUrl }}" @if ($isExternalButton) target="_blank" rel="noopener noreferrer" @endif>
                                        @if (filled($button['icon'] ?? null))<span class="material-symbols-rounded" aria-hidden="true">{{ $button['icon'] }}</span>@endif
                                        {{ $button['label'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @elseif ($homeCta['cta_label'] && $homeCta['cta_url'])<a class="contact-cta__button" href="{{ preg_replace('#^/(id|en)(?=/|$)#', '/'.app()->getLocale(), $homeCta['cta_url']) }}">{{ $homeCta['cta_label'] }}</a>@endif
                </div>
            </section>
        @endif

        @if ($brands->isNotEmpty())
            <section class="brands" id="brand" aria-labelledby="brand-title">
                <div class="container">
                    <div class="section-title"><h2 id="brand-title">{{ app()->getLocale() === 'en' ? 'Our Brands' : 'Brand Kami' }}</h2></div>
                    <div class="brand-carousel" aria-label="Brand mitra">
                        <div class="brand-carousel__viewport">
                            <div class="brand-carousel__track" id="brand-track">
                                @foreach ($brands as $brand)
                                    @if ($brand->public_logo_url)<div class="brand-carousel__slide brand-carousel__slide--{{ \Illuminate\Support\Str::slug($brand->name) }}"><img src="{{ $brand->public_logo_url }}" alt="{{ $brand->public_logo_alt ?: $brand->name }}" loading="lazy"></div>@endif
                                @endforeach
                            </div>
                        </div>
                        <div class="brand-carousel__dots" id="brand-dots" aria-label="Pilih brand"></div>
                    </div>
                </div>
            </section>
        @endif
    </main>

    <footer class="site-footer">
        @include('partials.site-footer', ['footer' => $footer])
    </footer>
</body>
</html>
