@extends('admin.layout')
@section('title', 'Manajemen User')
@section('heading', 'Manajemen User')
@section('content')
<section class="admin-dashboard-intro">
    <div><p class="admin-eyebrow">Pengaturan Sistem</p><h2>Manajemen User</h2><p>Kelola akun pengguna CMS, role, status, dan kredensial dengan aman.</p></div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">@if (auth()->user()->hasRole('superadmin'))<a class="admin-button" href="{{ route('admin.users.export', request()->query()) }}"><span class="material-symbols-rounded" aria-hidden="true">download</span>Export CSV</a><a class="admin-button admin-button--primary" href="{{ route('admin.users.create') }}"><span class="material-symbols-rounded" aria-hidden="true">person_add</span> Tambah User</a>@endif</div>
</section>
@if (session('temporary_password'))<div class="admin-alert admin-alert--success">Password sementara (ditampilkan sekali): <strong>{{ session('temporary_password') }}</strong></div>@endif
<section class="admin-stats-grid admin-stats-grid--three">
    <article class="admin-stat-card admin-summary-card"><span>Total User Terdaftar</span><strong>{{ $userStats['total'] }} Akun</strong><small>Akun CMS di database</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">group</span></article>
    <article class="admin-stat-card admin-summary-card admin-summary-card--success"><span>User Status Aktif</span><strong class="admin-stat-card__success">{{ $userStats['active'] }} Akun</strong><small>Memiliki akses login aktif</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">person_check</span></article>
    <article class="admin-stat-card admin-summary-card admin-summary-card--danger"><span>Nonaktif / Terkunci</span><strong class="admin-stat-card__danger">{{ $userStats['restricted'] }} Akun</strong><small>Akun dibatasi atau dikunci</small><span class="material-symbols-rounded admin-summary-card__icon" aria-hidden="true">lock</span></article>
</section>
<section class="admin-panel">
    <form class="admin-filter admin-user-filter" method="GET">
        <strong>Filter Akun:</strong>
        <select name="role"><option value="">Semua Role</option>@foreach ($roles as $role)<option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ ucfirst($role->name) }}</option>@endforeach</select>
        <select name="status"><option value="">Semua Status</option>@foreach (['active' => 'Active', 'inactive' => 'Inactive', 'locked' => 'Locked'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email...">
        <label>Baris <select name="per_page">@foreach ([10, 25, 50] as $size)<option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}</option>@endforeach</select></label>
        <div class="admin-filter-actions"><button class="admin-button admin-button--primary" type="submit">Cari</button><a class="admin-button" href="{{ route('admin.users.index') }}">Reset</a></div>
    </form>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr>@if (auth()->user()->hasRole('superadmin'))<th>Aksi</th>@endif<th>Nama Pengguna</th><th>Email</th><th>Peran (Role)</th><th>Status</th><th>Login Terakhir</th></tr></thead><tbody>
        @forelse ($users as $user)
            <tr>@if (auth()->user()->hasRole('superadmin'))<td class="admin-actions"><a href="{{ route('admin.users.edit', $user) }}" title="Ubah" aria-label="Ubah"><span class="material-symbols-rounded" aria-hidden="true">edit</span></a><form method="POST" action="{{ route('admin.users.reset-password', $user) }}" data-confirm="Reset password user ini?">@csrf<button type="submit" title="Reset password" aria-label="Reset password"><span class="material-symbols-rounded" aria-hidden="true">lock_reset</span></button></form>@if ($user->status === 'locked')<form method="POST" action="{{ route('admin.users.unlock', $user) }}">@csrf<button type="submit" title="Buka kunci" aria-label="Buka kunci"><span class="material-symbols-rounded" aria-hidden="true">lock_open</span></button></form>@endif<form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Apakah Anda yakin ingin menghapus data ini?"><input type="hidden" name="_method" value="DELETE">@csrf<button type="submit" class="is-danger" title="Hapus" aria-label="Hapus"><span class="material-symbols-rounded" aria-hidden="true">delete</span></button></form></td>@endif
            <td><strong>{{ $user->name }}</strong>@if (auth()->id() === $user->id)<span class="admin-status admin-status--published">Anda</span>@endif</td><td>{{ $user->email }}</td><td><span class="admin-status admin-status--draft">{{ ucfirst($user->role?->name ?? 'Tanpa role') }}</span></td><td><span class="admin-status admin-status--{{ $user->status }}">{{ ucfirst($user->status) }}</span></td><td>{{ $user->last_login_at?->format('d M Y, H:i') ?? 'Belum pernah login' }}</td></tr>
        @empty<tr><td colspan="{{ auth()->user()->hasRole('superadmin') ? 6 : 5 }}">Tidak ada akun yang cocok dengan filter ini.</td></tr>@endforelse
    </tbody></table></div>
    @include('admin.partials.pagination', ['paginator' => $users, 'label' => 'user'])
</section>
@endsection
        
