<title>{{ $seo['title'] ?? 'PT Interskala Medika Indonesia' }}</title>
<meta name="description" content="{{ $seo['description'] ?? '' }}">
<meta name="robots" content="{{ ($seo['robots_index'] ?? true) ? 'index,follow' : 'noindex,nofollow' }}">
<link rel="canonical" href="{{ $seo['canonical'] ?? request()->url() }}">
@php
    $currentLanguage = app()->getLocale();
    $otherLanguage = $currentLanguage === 'id' ? 'en' : 'id';
    $alternateUrl = $seo['alternate'] ?? preg_replace('#/(id|en)(?=/|$)#', '/'.$otherLanguage, request()->url());
    $defaultUrl = $currentLanguage === 'en' && !empty($seo['alternate']) ? $seo['alternate'] : preg_replace('#/(id|en)(?=/|$)#', '/id', request()->url());
@endphp
<link rel="alternate" hreflang="{{ $currentLanguage }}" href="{{ $seo['canonical'] ?? request()->url() }}">
<link rel="alternate" hreflang="{{ $otherLanguage }}" href="{{ $alternateUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $defaultUrl }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $seo['title'] ?? 'PT Interskala Medika Indonesia' }}">
<meta property="og:description" content="{{ $seo['description'] ?? '' }}">
<meta property="og:url" content="{{ $seo['canonical'] ?? request()->url() }}">
@if (!empty($seo['og_image']))<meta property="og:image" content="{{ $seo['og_image'] }}">@endif
