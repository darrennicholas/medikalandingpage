<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', ['seo' => $seo])
    @vite(['resources/css/app.css', 'resources/css/documentation.css', 'resources/js/app.js'])
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
    <main class="container public-detail-main">
        @if ($kind === 'documentation')
            <article class="public-detail">
                <a class="public-detail__back" href="{{ route($item->type === 'article' ? 'localized.articles' : 'localized.news', ['locale' => $language]) }}">← {{ $language === 'en' ? ($item->type === 'article' ? 'Back to articles' : 'Back to news') : ($item->type === 'article' ? 'Kembali ke artikel' : 'Kembali ke berita') }}</a>
                <header class="public-detail__heading">
                    <span class="public-detail__category">{{ $item->type === 'article' ? ($language === 'en' ? 'Article' : 'Artikel') : ($language === 'en' ? 'News' : 'Berita') }}</span>
                    <h1>{{ $item->public_title }}</h1>
                    @if ($item->event_date)
                        <time datetime="{{ $item->event_date->toDateString() }}">{{ $item->event_date->translatedFormat('d F Y') }}</time>
                    @endif
                    @if ($item->public_excerpt)<p>{{ $item->public_excerpt }}</p>@endif
                </header>
                @if ($item->public_cover_url)
                    <img class="public-detail__cover" src="{{ $item->public_cover_url }}" alt="{{ $item->public_cover_alt ?: $item->public_title }}">
                @endif
                <div class="public-detail__content">{!! $item->public_body !!}</div>
                @if (!empty($item->public_gallery))
                    <div class="public-detail__gallery" aria-label="{{ $language === 'en' ? 'Photo gallery' : 'Galeri foto' }}">
                        @foreach ($item->public_gallery as $image)
                            <figure>
                                <img src="{{ $image['url'] }}" alt="{{ $image['alt'] ?: $image['caption'] ?: $item->public_title }}" loading="lazy">
                                @if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif
                            </figure>
                        @endforeach
                    </div>
                @endif
            </article>
        @elseif ($kind === 'career')
            <article>
                <h1>{{ $translation?->title }}</h1>
                <p>{{ $job->location }} · {{ $job->employment_type }}</p>
                <div>{!! nl2br(e($translation?->description)) !!}</div>
                <h2>{{ $language === 'en' ? 'Requirements' : 'Persyaratan' }}</h2>
                <div>{!! nl2br(e($translation?->requirements)) !!}</div>
                <a class="contact-cta__button" href="{{ $job->apply_url }}" target="_blank" rel="noopener noreferrer">{{ $language === 'en' ? 'Apply' : 'Lamar' }}</a>
            </article>
        @elseif ($kind === 'product')
            <article>
                @if ($product->public_image_url)<img src="{{ $product->public_image_url }}" alt="{{ $product->public_image_alt }}" style="max-width:100%;height:auto">@endif
                <h1>{{ $translation?->name }}</h1>
                <div>{!! nl2br(e($translation?->description)) !!}</div>
                @if ($product->public_brochure_url)<a href="{{ $product->public_brochure_url }}" target="_blank" rel="noopener">{{ $language === 'en' ? 'View brochure PDF' : 'Lihat brosur PDF' }}</a>@endif
            </article>
        @endif
    </main>
    <footer class="site-footer">@include('partials.site-footer', ['footer' => $footer])</footer>
</body>
</html>
