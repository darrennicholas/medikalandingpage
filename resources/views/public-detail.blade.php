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
            @include('partials.language-switcher')
        </nav>
    </div></header>
    <main class="container public-detail-main">
        @if ($kind === 'documentation')
            <article class="public-detail">
                <a class="public-detail__back public-back-button documentation-back-button" href="{{ route($item->type === 'article' ? 'localized.articles' : 'localized.news', ['locale' => $language]) }}">{{ $language === 'en' ? 'Back' : 'Kembali' }}</a>
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
                                <a href="{{ $image['url'] }}" data-public-gallery="documentation" data-gallery-caption="{{ $image['caption'] }}" aria-label="{{ $language === 'en' ? 'Enlarge image' : 'Perbesar gambar' }}"><img src="{{ $image['url'] }}" alt="{{ $image['alt'] ?: $image['caption'] ?: $item->public_title }}" loading="lazy"></a>
                                @if ($image['caption'])<figcaption>{{ $image['caption'] }}</figcaption>@endif
                            </figure>
                        @endforeach
                    </div>
                @endif
            </article>
        @elseif ($kind === 'career')
            <a class="public-back-button public-detail__back" href="{{ route('localized.careers', ['locale' => $language]) }}">← {{ $language === 'en' ? 'Back to careers' : 'Kembali ke Karir' }}</a>
            <article>
                <h1>{{ $translation?->title }}</h1>
                <p>{{ $job->location }} · {{ $job->employment_type }}</p>
                <div>{!! \App\Services\PublicRichText::render($translation?->description) !!}</div>
                <h2>{{ $language === 'en' ? 'Requirements' : 'Persyaratan' }}</h2>
                <div>{!! \App\Services\PublicRichText::render($translation?->requirements) !!}</div>
                <a class="contact-cta__button" href="{{ $job->apply_url }}" target="_blank" rel="noopener noreferrer">{{ $language === 'en' ? 'Apply' : 'Lamar' }}</a>
            </article>
        @elseif ($kind === 'product')
            <a class="public-back-button public-detail__back" href="{{ route('localized.products', ['locale' => $language]) }}">{{ $language === 'en' ? 'Back to products' : 'Kembali ke Produk' }}</a>
            <article class="product-detail">
                <div class="product-detail__overview">
                    @if ($product->public_image_url)
                        <figure class="product-detail__image-wrap">
                            <img class="product-detail__image" src="{{ $product->public_image_url }}" alt="{{ $product->public_image_alt ?: $translation?->name }}">
                        </figure>
                    @endif
                    <div class="product-detail__summary">
                        <span class="public-detail__category">{{ $language === 'en' ? 'Product' : 'Produk' }}</span>
                        <h1>{{ $translation?->name }}</h1>
                        @if ($product->public_brochure_url)
                            <div class="product-detail__actions">
                                <span class="product-detail__actions-label">{{ $language === 'en' ? 'Product document' : 'Dokumen produk' }}</span>
                                <a class="product-detail__brochure" href="{{ $product->public_brochure_url }}" target="_blank" rel="noopener noreferrer"><span class="material-symbols-rounded" aria-hidden="true">picture_as_pdf</span>{{ $language === 'en' ? 'View brochure PDF' : 'Lihat brosur PDF' }}</a>
                            </div>
                        @endif
                        @if ($translation?->description)
                            <section class="product-detail__description" aria-labelledby="product-detail-description-title">
                                <h2 id="product-detail-description-title">{{ $language === 'en' ? 'Product information' : 'Informasi Produk' }}</h2>
                                <div class="product-detail__content">{!! \App\Services\PublicRichText::render($translation->description) !!}</div>
                            </section>
                        @endif
                    </div>
                </div>
            </article>
        @endif
    </main>
    <footer class="site-footer">@include('partials.site-footer', ['footer' => $footer])</footer>
</body>
</html>
