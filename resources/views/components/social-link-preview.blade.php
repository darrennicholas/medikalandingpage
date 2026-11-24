@props(['profileUrl', 'platform' => 'Instagram', 'media' => null, 'linkClass' => ''])

@php
    $language = app()->getLocale();
    $platformName = match (strtolower(trim($platform))) {
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'facebook' => 'Facebook',
        default => $platform,
    };
    $altText = match ($language) {
        'en' => $media?->alt_text_en ?: $media?->alt_text_id,
        'zh' => $media?->alt_text_zh ?: ($media?->alt_text_en ?: $media?->alt_text_id),
        default => $media?->alt_text_id,
    } ?: $media?->original_name;
    $profileHandle = in_array(strtolower(trim($platform)), ['instagram', 'tiktok'], true) ? ltrim(trim(parse_url($profileUrl, PHP_URL_PATH) ?: '', '/'), '@') : '';
    $profileDomain = parse_url($profileUrl, PHP_URL_HOST) ?: '';
    $profileDomain = $profileDomain !== '' && ! str_starts_with($profileDomain, 'www.') ? 'www.'.$profileDomain : $profileDomain;
    $profileTitle = 'INTERSKALA MEDIKA INDONESIA'.($profileHandle !== '' ? ' (@'.$profileHandle.')' : '').' · Profil '.$platformName;
    $profileLabel = $language === 'en' ? 'Open '.$platformName.' profile' : ($language === 'zh' ? '打开 '.$platformName.' 个人主页' : 'Buka profil '.$platformName);
@endphp

<div class="social-preview" @if ($media) data-social-preview @endif>
    @if ($media)
        <div class="social-preview__popover" data-social-popover role="group" aria-label="{{ $profileLabel }}" hidden>
            <a class="social-preview__card" href="{{ $profileUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $profileLabel }}">
                <img src="{{ route('media.stream', ['media' => $media->id, 'variant' => 'thumbnail']) }}" alt="{{ $altText }}" loading="lazy" decoding="async">
                <span class="social-preview__details"><strong>{{ $profileTitle }}</strong><small>{{ $profileDomain }}</small></span>
            </a>
        </div>
    @endif
    <a class="{{ trim($linkClass) }} social-preview__trigger" href="{{ $profileUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $platform }}" title="{{ $platform }}" @if ($media) data-social-trigger aria-expanded="false" @endif>
        {{ $slot }}
    </a>
</div>
