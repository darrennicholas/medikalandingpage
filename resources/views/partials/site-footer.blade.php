<div class="container site-footer__content">
    @if (!empty($footer['about']['body']))<div class="site-footer__about">{!! $footer['about']['body'] !!}</div>@endif
    @if (!empty($footer['contact']))
        <address class="site-footer__contact">
            @if ($footer['contact']['phone'])<a href="tel:{{ preg_replace('/[^0-9+]/', '', $footer['contact']['phone']) }}">{{ $footer['contact']['phone'] }}</a>@endif
            @if ($footer['contact']['email'])<a href="mailto:{{ $footer['contact']['email'] }}">{{ $footer['contact']['email'] }}</a>@endif
        </address>
    @endif
    @if (!empty($footer['social_links']) && $footer['social_links']->isNotEmpty())
        <nav class="site-footer__social" aria-label="Social media">
            @foreach ($footer['social_links'] as $socialLink)<a href="{{ $socialLink->url }}" target="_blank" rel="noopener noreferrer">{{ $socialLink->platform }}</a>@endforeach
        </nav>
    @endif
    <p>{{ $footer['copyright_text'] ?: '© '.now()->year.' PT Interskala Medika Indonesia | Member of Interskala Group' }}</p>
</div>
