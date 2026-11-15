@php($navigationLanguage = app()->getLocale())
@foreach (['home' => ['Beranda', 'Home'], 'about' => ['Tentang', 'About'], 'products' => ['Produk', 'Products'], 'documentation' => ['Dokumentasi', 'Documentation'], 'careers' => ['Karir', 'Careers'], 'contact' => ['Kontak', 'Contact']] as $navigationRoute => $navigationLabels)
    <a class="nav-link {{ request()->routeIs('localized.'.$navigationRoute.'*') ? 'nav-link--active' : '' }}" href="{{ route('localized.'.$navigationRoute, ['locale' => $navigationLanguage]) }}">{{ $navigationLabels[$navigationLanguage === 'en' ? 1 : 0] }}</a>
@endforeach
