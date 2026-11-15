@extends('admin.layout')
@section('title', 'Log Aktivitas')
@section('heading', 'Log Aktivitas')
@section('content')
<section class="admin-dashboard-intro">
    <div><p class="admin-eyebrow">System Settings</p><h2>Log Aktivitas <span class="admin-status admin-status--draft">Audit Trail</span></h2><p>Riwayat aktivitas pengguna dan perubahan data CMS yang tercatat oleh sistem.</p></div>
    <span class="admin-dashboard-intro__badge"><span class="admin-dashboard-intro__dot"></span> Data log tersimpan di database</span>
</section>
<section class="admin-dashboard-grid admin-dashboard-grid--four">
    <article class="admin-stat-card"><span>Total Aktivitas Hari Ini</span><strong>{{ number_format($logStats['today']) }} Log</strong><small>Aktivitas sejak tengah malam</small></article>
    <article class="admin-stat-card"><span>Login Gagal</span><strong class="admin-stat-card__danger">{{ number_format($logStats['failed']) }}</strong><small>Catatan kegagalan autentikasi</small></article>
    <article class="admin-stat-card"><span>Perubahan Konten</span><strong>{{ number_format($logStats['changes']) }} Modifikasi</strong><small>Create, update, dan delete</small></article>
    <article class="admin-stat-card"><span>Publish / Unpublish</span><strong class="admin-stat-card__success">{{ number_format($logStats['published']) }} Aksi</strong><small>Status publikasi yang tercatat</small></article>
</section>
<section class="admin-panel">
    <form class="admin-filter admin-log-filter" method="GET">
        <label>Rentang tanggal<input type="date" name="from" value="{{ request('from') }}"></label>
        <label>Sampai<input type="date" name="to" value="{{ request('to') }}"></label>
        <label>Pelaku (User)<select name="user_id"><option value="">Semua User</option>@foreach ($users as $user)<option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
        <label>Jenis aksi<input name="action" value="{{ request('action') }}" placeholder="login_failed, update"></label>
        <label>Modul / entitas<input name="entity_type" value="{{ request('entity_type') }}" placeholder="Produk, User"></label>
        <button class="admin-button admin-button--primary" type="submit">Terapkan Filter</button>
        @if (request()->query())<a class="admin-button" href="{{ route('admin.activity-logs') }}">Reset</a>@endif
    </form>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Waktu (WIB)</th><th>User / Pelaku</th><th>Aksi</th><th>Modul / Entitas</th><th>IP Address</th><th>Detail</th></tr></thead><tbody>
        @forelse ($logs as $log)
            <tr><td>{{ $log->created_at?->format('d/m/Y') }}<small class="admin-table-subline">{{ $log->created_at?->format('H:i:s') }} WIB</small></td><td>{{ $log->user?->name ?? 'Sistem' }}</td><td><span class="admin-status admin-status--{{ str_contains($log->action, 'failed') ? 'locked' : 'draft' }}">{{ strtoupper(str_replace(['_', '.'], ' ', $log->action)) }}</span></td><td>{{ class_basename($log->entity_type ?? '-') }} #{{ $log->entity_id ?? '-' }}</td><td>{{ $log->ip_address ?? '—' }}</td><td><details><summary>Lihat detail</summary><div class="admin-log-detail"><strong>Waktu:</strong> {{ $log->created_at?->format('d M Y H:i:s') }}<strong>User agent</strong><pre>{{ $log->user_agent ?? 'Tidak tersedia' }}</pre><strong>Sebelum</strong><pre>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre><strong>Sesudah</strong><pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div></details></td></tr>
        @empty<tr><td colspan="6">Tidak ada aktivitas pada filter ini.</td></tr>@endforelse
    </tbody></table></div>
    <div class="admin-list-footer"><span>Menampilkan {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} log</span>{{ $logs->links() }}</div>
</section>
@endsection
