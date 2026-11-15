<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#10213f">
    <title>Login CMS | Interskala Medika</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin-login-body">
    <div class="admin-login-shell">
        <section class="admin-login-aside" aria-label="CMS Landing">
            <a class="admin-login-brand" href="{{ route('home') }}">
                <span class="admin-login-brand__logo"><img src="{{ asset('images/medika-logo.png') }}" alt="IMI Medika"></span>
                <span><strong>CMS Landing</strong><small>PT Interskala Medika</small></span>
            </a>
            <div class="admin-login-aside__content">
                <h2>Kelola konten<br><span>website perusahaan.</span></h2>
            </div>
        </section>

        <main class="admin-login-card">
            <a class="admin-login-back" href="{{ route('home') }}"><span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>Kembali ke website</a>
            <div class="admin-login-heading">
                <h1>Masuk ke Admin Panel</h1>
                <p>Kelola konten landing page company profile.</p>
            </div>
            @if ($errors->any())<span data-admin-flash-error="{{ $errors->first() }}" hidden></span>
                <div class="admin-alert admin-alert--error" role="alert"><span class="material-symbols-rounded" aria-hidden="true">error</span><span>{{ $errors->first() }}</span></div>
            @endif
            <form method="POST" action="{{ route('admin.login.store') }}" class="admin-form admin-login-form" data-admin-login-form>
                @csrf
                <div class="admin-form__error" data-login-errors hidden role="alert"></div>
                <label class="admin-field" for="login-email"><span>Alamat Email <b>*</b></span><span class="admin-input-wrap"><span class="material-symbols-rounded" aria-hidden="true">mail</span><input id="login-email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="nama@perusahaan.co.id" required autofocus></span></label>
                <label class="admin-field" for="login-password"><span>Kata Sandi <b>*</b></span><span class="admin-password-field"><span class="material-symbols-rounded admin-password-field__icon" aria-hidden="true">lock</span><input id="login-password" type="password" name="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required><button type="button" data-password-toggle aria-label="Tampilkan atau sembunyikan kata sandi"><span class="material-symbols-rounded" aria-hidden="true">visibility</span></button></span></label>
                <button class="admin-button admin-button--primary admin-login-submit" type="submit"><span>Masuk</span><span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span></button>
            </form>
        </main>
    </div>
    @include('admin.partials.feedback-modals')
</body>
</html>
