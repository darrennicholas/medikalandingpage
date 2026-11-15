@extends('admin.layout')
@section('title', $user->exists ? 'Ubah User' : 'Tambah User')
@section('heading', $user->exists ? 'Ubah User' : 'Tambah User')
@section('content')
<section class="admin-panel admin-panel--narrow">
    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="admin-form">
        @csrf @if ($user->exists) @method('PUT') @endif
        <label>Nama<input name="name" maxlength="100" value="{{ old('name', $user->name) }}" required></label>
        <label>Email<input type="email" name="email" maxlength="150" value="{{ old('email', $user->email) }}" required></label>
        <label>Role<select name="role_id" required>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ match ($role->name) { 'superadmin' => 'Superadmin', 'admin' => 'Admin (Digital Marketing)', 'user' => 'User (akses publik saja)', default => $role->name } }}</option>@endforeach</select></label>
        <label>Status<select name="status" required>@foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $user->status ?: 'active') === $value)>{{ $label }}</option>@endforeach</select></label>
        @unless ($user->exists)<label>Password awal<input type="password" name="password" minlength="8" required autocomplete="new-password"></label><label>Konfirmasi password<input type="password" name="password_confirmation" minlength="8" required autocomplete="new-password"></label>@endunless
        <div class="admin-form__actions"><a class="admin-button" href="{{ route('admin.users.index') }}">Batal</a><button class="admin-button admin-button--primary" type="submit">Simpan</button></div>
    </form>
</section>
@endsection
