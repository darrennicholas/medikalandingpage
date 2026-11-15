@php
    $paginationLabel = $label ?? 'data';
@endphp
<footer class="admin-pagination">
    <p>Menampilkan <strong>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}</strong> dari <strong>{{ $paginator->total() }}</strong> {{ $paginationLabel }}</p>
    <nav aria-label="Navigasi halaman {{ $paginationLabel }}">
        @if ($paginator->previousPageUrl())
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"><span class="material-symbols-rounded" aria-hidden="true">chevron_left</span>Sebelumnya</a>
        @else
            <span aria-disabled="true"><span class="material-symbols-rounded" aria-hidden="true">chevron_left</span>Sebelumnya</span>
        @endif
        <span class="admin-pagination__page">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
        @if ($paginator->nextPageUrl())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya<span class="material-symbols-rounded" aria-hidden="true">chevron_right</span></a>
        @else
            <span aria-disabled="true">Berikutnya<span class="material-symbols-rounded" aria-hidden="true">chevron_right</span></span>
        @endif
    </nav>
</footer>
