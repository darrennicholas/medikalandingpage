@extends('admin.layout')
@section('title', 'Profil Saya')
@section('page-heading', 'Profil Saya')
@section('breadcrumb', 'Pengaturan Sistem  /  Profil Saya')
@section('page-description', 'Kelola informasi akun dan keamanan akses CMS Anda.')
@section('page-actions')
    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="admin-button admin-button--danger" type="submit"><span class="material-symbols-rounded" aria-hidden="true">logout</span>Logout Akun</button></form>
@endsection
@section('content')
<div class="admin-profile-grid">
    <div class="admin-profile-column">
        <section class="admin-panel admin-profile-card">
            <div class="admin-profile-identity">
                <span class="admin-profile-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <div><h2>{{ $user->name }}</h2><span class="admin-profile-role">{{ $user->role?->description ?? ucfirst($user->role?->name ?? 'Pengguna CMS') }}</span></div>
            </div>
            <h3>Ringkasan Informasi Akun</h3>
            <dl class="admin-profile-details">
                <div><dt><span class="material-symbols-rounded" aria-hidden="true">alternate_email</span>Email Akun</dt><dd class="admin-profile-details__email"><span>{{ $user->email }}</span><span class="admin-status admin-status--{{ $user->email_verified_at ? 'active' : 'draft' }}">{{ $user->email_verified_at ? 'Terverifikasi' : 'Belum diverifikasi' }}</span></dd></div>
                <div><dt><span class="material-symbols-rounded" aria-hidden="true">admin_panel_settings</span>Hak Akses</dt><dd>{{ $user->role?->description ?? ucfirst($user->role?->name ?? '—') }}</dd></div>
                <div><dt><span class="material-symbols-rounded" aria-hidden="true">toggle_on</span>Status Akun</dt><dd><span class="admin-status admin-status--{{ $user->status === 'active' ? 'active' : 'inactive' }}">{{ ucfirst($user->status) }}</span></dd></div>
                <div><dt><span class="material-symbols-rounded" aria-hidden="true">calendar_month</span>Terdaftar</dt><dd>{{ $user->created_at?->translatedFormat('d F Y') ?? '—' }}</dd></div>
                <div><dt><span class="material-symbols-rounded" aria-hidden="true">history</span>Login Terakhir</dt><dd>{{ $user->last_login_at?->translatedFormat('d F Y, H:i') ?? 'Belum tercatat' }}</dd></div>
            </dl>
        </section>

    </div>

    <section class="admin-panel admin-password-panel">
        <div class="admin-panel__header"><div><h2>Ubah Password</h2><p>Gunakan password yang kuat dan berbeda dari akun lain.</p></div><span class="material-symbols-rounded admin-password-panel__icon" aria-hidden="true">shield_lock</span></div>
        <div class="admin-password-policy"><span class="material-symbols-rounded" aria-hidden="true">info</span><span>Password minimal {{ config('cms.authentication.password_min_length') }} karakter dan harus memuat huruf besar, huruf kecil, serta angka.</span></div>
        <form method="POST" action="{{ route('admin.password.update') }}" class="admin-form admin-profile-password-form" data-admin-password-form data-redirect-url="{{ route('admin.dashboard') }}">
            @csrf @method('PUT')
            <label class="admin-field">Password Lama<div class="admin-password-field"><span class="material-symbols-rounded admin-password-field__icon" aria-hidden="true">lock</span><input type="password" name="current_password" required autocomplete="current-password" placeholder="Masukkan password saat ini"><button type="button" data-password-toggle aria-label="Tampilkan password"><span class="material-symbols-rounded" aria-hidden="true">visibility</span></button></div><small>Konfirmasi password akun Anda sebelum membuat password baru.</small></label>
            <label class="admin-field">Password Baru<div class="admin-password-field"><span class="material-symbols-rounded admin-password-field__icon" aria-hidden="true">key</span><input type="password" name="password" required autocomplete="new-password" placeholder="Buat password baru" data-new-password data-password-min-length="{{ config('cms.authentication.password_min_length') }}"><button type="button" data-password-toggle aria-label="Tampilkan password"><span class="material-symbols-rounded" aria-hidden="true">visibility</span></button></div></label>
            <ul class="admin-password-rules" data-password-rules>
                <li data-password-rule="length"><span class="material-symbols-rounded" aria-hidden="true">circle</span>Minimal {{ config('cms.authentication.password_min_length') }} karakter</li>
                <li data-password-rule="lowercase"><span class="material-symbols-rounded" aria-hidden="true">circle</span>Memiliki huruf kecil (a-z)</li>
                <li data-password-rule="uppercase"><span class="material-symbols-rounded" aria-hidden="true">circle</span>Memiliki huruf besar (A-Z)</li>
                <li data-password-rule="number"><span class="material-symbols-rounded" aria-hidden="true">circle</span>Memiliki angka (0-9)</li>
            </ul>
            <label class="admin-field">Konfirmasi Password Baru<div class="admin-password-field"><span class="material-symbols-rounded admin-password-field__icon" aria-hidden="true">verified_user</span><input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang password baru" data-password-confirmation><button type="button" data-password-toggle aria-label="Tampilkan password"><span class="material-symbols-rounded" aria-hidden="true">visibility</span></button></div><small data-password-match>Masukkan kembali password baru untuk konfirmasi.</small></label>
            <div class="admin-form__actions admin-password-actions"><button class="admin-button admin-button--primary" type="submit"><span class="material-symbols-rounded" aria-hidden="true">save</span>Simpan Password</button></div>
        </form>
    </section>
</div>
@endsection
