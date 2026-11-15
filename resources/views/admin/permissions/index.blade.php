@extends('admin.layout')
@section('title', 'Hak Akses')
@section('heading', 'Hak Akses (Permission)')
@section('content')
<div class="permission-page">
    <div class="permission-intro"><p class="admin-eyebrow">CMS Landing / Pengaturan Sistem / Hak Akses</p><h2>Hak Akses</h2><p>Kelola peran pengguna dan izin setiap modul CMS.</p></div>
    <div class="permission-stats">
        <article><div><span>Total Role</span><strong>{{ $roles->count() }} Role <small>Sistem</small></strong><p>Superadmin &amp; Admin Konten</p></div><span class="material-symbols-rounded">shield_person</span></article>
        <article><div><span>Role Dapat Dikonfigurasi</span><strong>1 Role</strong><p>Izin Admin dapat disesuaikan</p></div><span class="material-symbols-rounded is-green">verified</span></article>
        <article><div><span>Modul Otorisasi</span><strong>{{ count($modules) }} Modul</strong><p>Kontrol akses per tindakan</p></div><span class="material-symbols-rounded">lock</span></article>
    </div>
    <section class="admin-panel permission-panel">
        <div class="permission-heading"><div><h3><span class="material-symbols-rounded">shield_person</span> Daftar Peran Pengguna (System Roles)</h3><p>Struktur peran dan akses pengguna pengelola CMS.</p></div></div>
        <div class="permission-scroll"><table class="permission-role-table"><thead><tr><th>Role</th><th>Deskripsi Otoritas</th><th>Jumlah User</th><th>Status Hak Akses</th></tr></thead><tbody>
            @foreach ($roles as $role)
                <tr><td><strong>{{ $role->name === 'superadmin' ? 'Superadmin (Utama)' : 'Admin (Pengelola Konten)' }}</strong><small>{{ $role->name === 'superadmin' ? 'Root Access' : 'Custom Configurable' }}</small></td><td>{{ $role->description }}</td><td><span class="permission-badge">{{ $role->users_count }} User</span></td><td><span class="permission-badge {{ $role->name === 'admin' ? 'is-green' : '' }}">{{ $role->name === 'superadmin' ? 'Terkunci Penuh' : 'Dapat Dikonfigurasi' }}</span></td></tr>
            @endforeach
        </tbody></table></div>
    </section>
    <section class="admin-panel permission-panel">
        <div class="permission-heading"><div><h3><span class="material-symbols-rounded">checklist</span> Matriks Otorisasi Hak Akses Modul</h3><p>Tentukan izin untuk melihat, menambah, mengubah, menghapus, dan mempublikasikan konten.</p></div><span class="permission-badge permission-badge--role">Role: Admin Konten</span></div>
        <div class="permission-notices"><div><span class="material-symbols-rounded">info</span><p><strong>Penerapan Hak Akses</strong>Perubahan izin berlaku setelah disimpan dan diterapkan pada akses CMS berikutnya.</p></div><div><span class="material-symbols-rounded">verified_user</span><p><strong>Perlindungan Akses Sistem</strong>Superadmin memiliki akses penuh. Pengaturan hak akses hanya dapat diubah Superadmin.</p></div></div>
        <form method="POST" action="{{ route('admin.permissions.update') }}" data-permission-form data-permission-defaults="{{ json_encode($defaultPermissions) }}">
            @csrf @method('PUT')
            <div class="permission-toolbar"><span>Terapkan opsi cepat:</span><button type="button" data-permission-check="all">Pilih Semua</button><button type="button" data-permission-check="none">Kosongkan Semua</button><button type="button" data-permission-default>Batalkan Perubahan</button><span class="permission-legend">✓ Diberikan <span>— Tidak tersedia</span></span></div>
            <div class="permission-scroll"><table class="permission-matrix">
                <colgroup><col class="permission-module-col">@foreach (range(1, 5) as $column)<col class="permission-action-col">@endforeach</colgroup>
                <thead><tr><th>Modul CMS &amp; Komponen</th>@foreach (['view' => 'Lihat', 'create' => 'Tambah', 'update' => 'Ubah', 'delete' => 'Hapus', 'publish' => 'Publish'] as $action => $label)<th>{{ $label }}<small>{{ ucfirst($action) }}</small></th>@endforeach</tr></thead>
                <tbody>
                    @foreach (collect($modules)->groupBy('group', preserveKeys: true) as $group => $groupModules)
                        <tr class="permission-category"><th colspan="6">{{ $group }}</th></tr>
                        @foreach ($groupModules as $key => $module)
                            <tr><td>{{ $module['label'] }}@if ($key === 'users')<small>Hanya baca untuk Admin</small>@elseif ($key === 'permissions')<small>Khusus Superadmin</small>@endif</td>
                                @foreach (['view', 'create', 'update', 'delete', 'publish'] as $action)
                                    <td>@if (in_array($action, $module['actions'], true))<input class="permission-checkbox" type="checkbox" name="permissions[{{ $key }}][]" value="{{ $action }}" aria-label="{{ $action }} {{ $module['label'] }}" @checked(in_array($action, $adminRole->permissions[$key] ?? [], true))>@else<span class="permission-unavailable" aria-label="Tidak tersedia">—</span>@endif</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table></div>
            <div class="permission-footer"><span><i></i> Perubahan disimpan untuk role Admin</span><div><button class="admin-button" type="button" data-permission-default>Batal</button><button class="admin-button admin-button--primary" type="submit"><span class="material-symbols-rounded" aria-hidden="true">save</span> Simpan Perubahan</button></div></div>
        </form>
    </section>
</div>
@endsection
