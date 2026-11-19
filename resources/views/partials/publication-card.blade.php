<a href="{{ $itemUrl }}" class="public-documentation__activity-link" target="_blank" rel="noopener noreferrer">
    @if ($item->public_cover_url)
        <img class="public-documentation__cover" src="{{ $item->public_cover_url }}" alt="{{ $item->public_cover_alt ?: $item->public_title }}" loading="lazy">
    @else
        <div class="public-documentation__cover-placeholder" aria-hidden="true">{{ $kindLabel }}</div>
    @endif
    <div class="public-documentation__body">
        <h3>{{ $item->public_title }}</h3>
        @if ($item->public_excerpt)<p>{{ $item->public_excerpt }}</p>@endif
        <div class="public-documentation__meta">
            @if ($item->event_date)<time datetime="{{ $item->event_date->toDateString() }}"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 3h2v2h6V3h2v2h2a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2V3ZM5 10v9h14v-9H5Z"/></svg>{{ $item->event_date->format('d M Y') }}</time>@endif
        </div>
        <span>{{ $language === 'en' ? 'Read more' : 'Baca Selengkapnya' }} →</span>
    </div>
</a>
