@extends('admin.layout')
@section('title', 'Dashboard')
@section('page-heading', 'Dashboard')
@section('breadcrumb', 'CMS  /  Dashboard')
@section('page-actions')
    <form class="admin-dashboard-range" method="GET" action="{{ route('admin.dashboard') }}">
        <div class="admin-dashboard-date">
            <input id="dashboard-from-date" type="date" name="from" value="{{ $fromDate }}" aria-label="Tanggal mulai">
            <button type="button" data-date-picker-target="dashboard-from-date" aria-label="Pilih tanggal mulai">
                <span class="material-symbols-rounded" aria-hidden="true">calendar_month</span>
            </button>
        </div>
        <span class="admin-dashboard-range__separator" aria-hidden="true">–</span>
        <div class="admin-dashboard-date">
            <input id="dashboard-to-date" type="date" name="to" value="{{ $toDate }}" aria-label="Tanggal akhir">
            <button type="button" data-date-picker-target="dashboard-to-date" aria-label="Pilih tanggal akhir">
                <span class="material-symbols-rounded" aria-hidden="true">calendar_month</span>
            </button>
        </div>
        <button class="admin-button admin-button--primary admin-dashboard-range__apply" type="submit">Terapkan Periode</button>
    </form>
@endsection
@section('content')
<section class="admin-dashboard-stats" aria-label="Ringkasan konten">
    @foreach ($dashboardStats as $stat)
        <a class="admin-dashboard-stat admin-summary-card" href="{{ $stat['url'] }}">
            <div class="admin-dashboard-stat__top"><span>{{ $stat['label'] }}</span><span class="admin-dashboard-stat__icon material-symbols-rounded" aria-hidden="true">{{ $stat['icon'] }}</span></div>
            <strong>{{ number_format($stat['total']) }}</strong>
            <div class="admin-dashboard-stat__counts">
                @foreach (['published' => 'Tayang', 'draft' => 'Draft'] as $status => $label)
                    @if (($stat['statuses'][$status] ?? 0) > 0)
                        <span><b>{{ $stat['statuses'][$status] }}</b> {{ $label }}</span>
                    @endif
                @endforeach
            </div>
        </a>
    @endforeach
</section>

<section class="admin-dashboard-grid" aria-label="Ringkasan lowongan">
    <article class="admin-stat-card admin-summary-card admin-summary-card--success"><span>Lowongan Open</span><strong>{{ $jobStats['open'] }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">work_history</span></article>
    <article class="admin-stat-card admin-summary-card admin-summary-card--warning"><span>Lowongan Closed</span><strong>{{ $jobStats['closed'] }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">work_off</span></article>
    <article class="admin-stat-card admin-summary-card"><span>Ditutup dalam 7 hari</span><strong>{{ $jobStats['closingSoon'] }}</strong><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">schedule</span></article>
</section>

<section class="admin-dashboard-overview">
    <article class="admin-panel admin-language-card">
        <div class="admin-panel__header">
            <div><h2>Kelengkapan Bahasa</h2></div>
            <span class="admin-language-card__percent">{{ $translationPercent }}% ID &amp; EN</span>
        </div>
        <div class="admin-language-card__summary"><strong>{{ number_format($translationCompleted) }}</strong><span>dari {{ number_format($totalIndonesianContent) }} konten memiliki terjemahan Inggris</span></div>
        <div class="admin-progress" role="progressbar" aria-label="Kelengkapan terjemahan Inggris" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $translationPercent }}"><span style="width: {{ $translationPercent }}%"></span></div>
        @if ($missingEnglishItems->isNotEmpty())
            <div class="admin-translation-gaps">
                @foreach ($missingEnglishItems as $item)
                    <a href="{{ $item['url'] }}"><span>{{ strtoupper($item['category']) }}</span><strong>{{ $item['title'] }}</strong><small>Terjemahkan <span aria-hidden="true">→</span></small></a>
                @endforeach
            </div>
        @endif
    </article>

    <aside class="admin-panel admin-dashboard-shortcuts">
        <div class="admin-panel__header"><div><h2>Pintasan</h2></div></div>
        <a href="{{ route('admin.content.create', 'products') }}"><span class="material-symbols-rounded" aria-hidden="true">inventory_2</span><span><strong>Tambah Produk</strong></span><span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span></a>
        <a href="{{ route('admin.content.create', ['resource' => 'documentations', 'type' => 'article']) }}"><span class="material-symbols-rounded" aria-hidden="true">article</span><span><strong>Tambah Artikel</strong></span><span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span></a>
        <a href="{{ route('admin.content.create', 'job-openings') }}"><span class="material-symbols-rounded" aria-hidden="true">work</span><span><strong>Tambah Lowongan</strong></span><span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span></a>
        <a href="{{ route('admin.media.index') }}"><span class="material-symbols-rounded" aria-hidden="true">photo_library</span><span><strong>Media Library</strong></span><span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span></a>
    </aside>
</section>

<section class="admin-panel admin-activity-panel">
    <div class="admin-panel__header"><div><h2>Aktivitas Terbaru</h2></div>@if ($isSuperadmin)<a href="{{ route('admin.activity-logs') }}">Lihat semua log <span aria-hidden="true">→</span></a>@endif</div>
    @if ($isSuperadmin)
        <div class="admin-table-wrap"><table class="admin-table admin-activity-table"><thead><tr><th>Pengguna</th><th>Aktivitas / Konten</th><th>Waktu</th></tr></thead><tbody>
            @forelse ($recentActivities as $activity)
                @php $entityLabel = $activity->entity_type ? class_basename($activity->entity_type) : 'Sistem'; @endphp
                <tr><td><span class="admin-activity-user"><span class="admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($activity->user?->name ?? 'S', 0, 1)) }}</span><span><strong>{{ $activity->user?->name ?? 'Sistem' }}</strong></span></span></td><td><strong>{{ str($activity->action)->replace('_', ' ')->headline() }}</strong></td><td>{{ $activity->created_at?->diffForHumans() ?? '—' }}</td></tr>
            @empty
                <tr><td colspan="3" class="admin-empty">Belum ada aktivitas pada periode ini.</td></tr>
            @endforelse
        </tbody></table></div>
    @else
        <p class="admin-activity-restricted">Log aktivitas tersedia untuk Superadmin.</p>
    @endif
</section>
@endsection
