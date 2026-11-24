<div class="container site-footer__content">
    @if (!empty($footer['about']['body']))<div class="site-footer__about">{!! $footer['about']['body'] !!}</div>@endif
    @if ((!empty($footer['contact']['phone']) && ($footer['show_phone'] ?? true)) || (!empty($footer['contact']['email']) && ($footer['show_email'] ?? true)))
        <address class="site-footer__contact">
            @if (!empty($footer['contact']['phone']) && ($footer['show_phone'] ?? true))<a href="tel:{{ preg_replace('/[^0-9+]/', '', $footer['contact']['phone']) }}">{{ $footer['contact']['phone'] }}</a>@endif
            @if (!empty($footer['contact']['email']) && ($footer['show_email'] ?? true))<a href="mailto:{{ $footer['contact']['email'] }}">{{ $footer['contact']['email'] }}</a>@endif
        </address>
    @endif
    @if (!empty($footer['social_links']) && $footer['social_links']->isNotEmpty())
        <nav class="site-footer__social" aria-label="{{ app()->getLocale() === 'en' ? 'Social media' : 'Media sosial' }}">
            @foreach ($footer['social_links'] as $socialLink)
                @php($platform = strtolower(trim($socialLink->platform)))
                    <x-social-link-preview :profile-url="$socialLink->url" :platform="$socialLink->platform" :media="$socialLink->previewMedia" link-class="site-footer__social-link">
                    @if ($platform === 'instagram')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                    @elseif ($platform === 'linkedin')
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.5 8.4H3.2V21h3.3V8.4ZM4.85 3a1.95 1.95 0 1 0 0 3.9 1.95 1.95 0 0 0 0-3.9ZM21 13.4c0-3.5-1.9-5.2-4.4-5.2-2 0-2.9 1.1-3.4 1.9V8.4H9.9V21h3.3v-6.2c0-1.6.3-3.2 2.4-3.2 2 0 2 1.9 2 3.3V21H21v-7.6Z"/></svg>
                    @elseif ($platform === 'tiktok')
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.7 2c.2 2.1 1.4 3.5 3.5 3.7v3.2a7.3 7.3 0 0 1-3.5-1v6.5a6.4 6.4 0 1 1-5.5-6.3v3.3a3.2 3.2 0 1 0 2.2 3V2h3.3Z"/></svg>
                    @else
                        <span>{{ $socialLink->platform }}</span>
                    @endif
                    </x-social-link-preview>
            @endforeach
        </nav>
    @endif
    <p>{{ $footer['copyright_text'] ?: '© '.now()->year.' PT Interskala Medika Indonesia | Member of Interskala Group' }}</p>
</div>
