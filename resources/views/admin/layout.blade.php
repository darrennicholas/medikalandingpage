<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#f4f7fc">
    <title>@yield('title', 'CMS Admin') | Interskala Medika</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin-body" data-admin-theme="light">
    @php
        $currentResource = request()->route('resource');
        $currentSection = request()->query('section');
        $homeBlockSections = ['home_coverage', 'home_cta'];
        $aboutBlockSections = ['about_company'];
        $homeIsExpanded = in_array($currentResource, ['hero-slides', 'brands'], true)
            || ($currentResource === 'content-blocks' && in_array($currentSection, $homeBlockSections, true));
        $aboutIsExpanded = in_array($currentResource, ['company-summary', 'mission-items', 'certificates'], true)
            || ($currentResource === 'content-blocks' && in_array($currentSection, $aboutBlockSections, true));
        $productResources = ['product-categories', 'products'];
        $contactResources = ['contact-info', 'social-links'];
        $footerIsExpanded = in_array($currentResource, $contactResources, true)
            || ($currentResource === 'content-blocks' && $currentSection === 'footer_about');
    @endphp
    <div class="admin-shell" data-admin-shell>
        <aside class="admin-sidebar" aria-label="Navigasi CMS">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}" aria-label="Dashboard CMS">
                <span class="admin-brand__logo"><img src="{{ asset('images/medika-logo.png') }}" alt=""></span>
                <span class="admin-brand__copy"><strong>IMI Medika</strong><small>CMS LANDING PAGE</small></span>
            </a>

            <label class="admin-sidebar-search">
                <span class="material-symbols-rounded" aria-hidden="true">search</span>
                <input type="search" placeholder="Cari menu..." aria-label="Cari menu navigasi" data-admin-nav-search>
                <kbd>/</kbd>
            </label>

            <nav class="admin-nav">
                <div class="admin-nav__category" data-nav-category>
                    <span class="admin-nav__heading">Dashboard</span>
                    <a class="admin-nav__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}" data-nav-link>
                        <span class="material-symbols-rounded" aria-hidden="true">grid_view</span><span class="admin-nav__label">Dashboard</span>
                    </a>
                </div>

                <div class="admin-nav__category" data-nav-category>
                    <span class="admin-nav__heading">Konten Landing Page</span>
                    <div class="admin-nav__section admin-nav__section--home" data-nav-section>
                        <button class="admin-nav__section-toggle" type="button" aria-expanded="{{ $homeIsExpanded ? 'true' : 'false' }}" data-nav-section-toggle>
                            <span class="material-symbols-rounded" aria-hidden="true">home</span><span class="admin-nav__label">Beranda</span><span class="material-symbols-rounded admin-nav__chevron" aria-hidden="true">expand_more</span>
                        </button>
                        <div class="admin-nav__section-content" data-nav-section-content @if (! $homeIsExpanded) hidden @endif>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'hero-slides' ? 'is-active' : '' }}" href="{{ route('admin.content.index', 'hero-slides') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">view_carousel</span><span class="admin-nav__label">Hero / Banner</span></a>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'content-blocks' && $currentSection === 'home_coverage' ? 'is-active' : '' }}" href="{{ route('admin.content.index', ['resource' => 'content-blocks', 'section' => 'home_coverage']) }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">public</span><span class="admin-nav__label">Jangkauan Kami</span></a>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'content-blocks' && $currentSection === 'home_cta' ? 'is-active' : '' }}" href="{{ route('admin.content.index', ['resource' => 'content-blocks', 'section' => 'home_cta']) }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">contact_support</span><span class="admin-nav__label">CTA Hubungi Kami</span></a>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'brands' ? 'is-active' : '' }}" href="{{ route('admin.content.index', 'brands') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">verified</span><span class="admin-nav__label">Brand Kami</span></a>
                        </div>
                    </div>
                    <div class="admin-nav__section" data-nav-section>
                        <button class="admin-nav__section-toggle" type="button" aria-expanded="{{ $aboutIsExpanded ? 'true' : 'false' }}" data-nav-section-toggle>
                            <span class="material-symbols-rounded" aria-hidden="true">domain</span><span class="admin-nav__label">Tentang</span><span class="material-symbols-rounded admin-nav__chevron" aria-hidden="true">expand_more</span>
                        </button>
                        <div class="admin-nav__section-content" data-nav-section-content @if (! $aboutIsExpanded) hidden @endif>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'content-blocks' && $currentSection === 'about_company' ? 'is-active' : '' }}" href="{{ route('admin.content.index', ['resource' => 'content-blocks', 'section' => 'about_company']) }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">domain</span><span class="admin-nav__label">Tentang Perusahaan</span></a>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'company-summary' ? 'is-active' : '' }}" href="{{ route('admin.content.index', 'company-summary') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">summarize</span><span class="admin-nav__label">Summary</span></a>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'mission-items' ? 'is-active' : '' }}" href="{{ route('admin.content.index', 'mission-items') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">flag</span><span class="admin-nav__label">Visi &amp; Misi</span></a>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'certificates' ? 'is-active' : '' }}" href="{{ route('admin.content.index', 'certificates') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">workspace_premium</span><span class="admin-nav__label">Sertifikat</span></a>
                        </div>
                    </div>
                    <div class="admin-nav__section" data-nav-section>
                        <button class="admin-nav__section-toggle" type="button" aria-expanded="{{ in_array($currentResource, $productResources, true) || request()->routeIs('admin.featured.*') ? 'true' : 'false' }}" data-nav-section-toggle>
                            <span class="material-symbols-rounded" aria-hidden="true">inventory_2</span><span class="admin-nav__label">Produk</span><span class="material-symbols-rounded admin-nav__chevron" aria-hidden="true">expand_more</span>
                        </button>
                        <div class="admin-nav__section-content" data-nav-section-content @if (! (in_array($currentResource, $productResources, true) || request()->routeIs('admin.featured.*'))) hidden @endif>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'product-categories' ? 'is-active' : '' }}" href="{{ route('admin.content.index', 'product-categories') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">category</span><span class="admin-nav__label">Kategori Pemeriksaan</span></a>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'products' ? 'is-active' : '' }}" href="{{ route('admin.content.index', 'products') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">inventory_2</span><span class="admin-nav__label">Daftar Produk</span></a>
                            <a class="admin-nav__link admin-nav__link--child {{ request()->routeIs('admin.featured.*') ? 'is-active' : '' }}" href="{{ route('admin.featured.index') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">star</span><span class="admin-nav__label">Produk Unggulan</span></a>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'products' && request()->query('section') === 'specifications-brochures' ? 'is-active' : '' }}" href="{{ route('admin.content.index', ['resource' => 'products', 'section' => 'specifications-brochures']) }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">picture_as_pdf</span><span class="admin-nav__label">Spesifikasi &amp; Brosur</span></a>
                        </div>
                    </div>
                    <a class="admin-nav__link {{ $currentResource === 'documentations' ? 'is-active' : '' }}" href="{{ route('admin.content.index', 'documentations') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">article</span><span class="admin-nav__label">Dokumentasi</span></a>
                    <a class="admin-nav__link {{ $currentResource === 'job-openings' ? 'is-active' : '' }}" href="{{ route('admin.content.index', 'job-openings') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">work</span><span class="admin-nav__label">Karir</span></a>
                    <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'content-blocks' && $currentSection === 'career_intro' ? 'is-active' : '' }}" href="{{ route('admin.content.index', ['resource' => 'content-blocks', 'section' => 'career_intro']) }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">description</span><span class="admin-nav__label">Pengantar Karir</span></a>
                    <div class="admin-nav__section" data-nav-section>
                        <button class="admin-nav__section-toggle" type="button" aria-expanded="{{ $footerIsExpanded ? 'true' : 'false' }}" data-nav-section-toggle>
                            <span class="material-symbols-rounded" aria-hidden="true">call</span><span class="admin-nav__label">Kontak &amp; Footer</span><span class="material-symbols-rounded admin-nav__chevron" aria-hidden="true">expand_more</span>
                        </button>
                        <div class="admin-nav__section-content" data-nav-section-content @if (! $footerIsExpanded) hidden @endif>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'contact-info' ? 'is-active' : '' }}" href="{{ route('admin.content.index', 'contact-info') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">contact_phone</span><span class="admin-nav__label">Informasi Kontak</span></a>
                            <a class="admin-nav__link admin-nav__link--child {{ $currentResource === 'social-links' ? 'is-active' : '' }}" href="{{ route('admin.content.index', 'social-links') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">share</span><span class="admin-nav__label">Sosial Media &amp; Footer</span></a>
                        </div>
                    </div>
                </div>

                <div class="admin-nav__category" data-nav-category>
                    <span class="admin-nav__heading">Media &amp; SEO</span>
                    <a class="admin-nav__link {{ request()->routeIs('admin.media.*') ? 'is-active' : '' }}" href="{{ route('admin.media.index') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">photo_library</span><span class="admin-nav__label">Media Library</span></a>
                    <a class="admin-nav__link {{ $currentResource === 'seo-metas' ? 'is-active' : '' }}" href="{{ route('admin.content.index', 'seo-metas') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">manage_search</span><span class="admin-nav__label">Pengaturan SEO</span></a>
                </div>

                @if (auth()->user()->hasRole('superadmin') || auth()->user()->role?->hasPermission('users', 'view') || auth()->user()->role?->hasPermission('activity_logs', 'view'))
                    <div class="admin-nav__category" data-nav-category>
                        <span class="admin-nav__heading">Pengaturan Sistem</span>
                        @if (auth()->user()->hasRole('superadmin') || auth()->user()->role?->hasPermission('users', 'view'))
                            <a class="admin-nav__link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">group</span><span class="admin-nav__label">Manajemen User</span></a>
                        @endif
                        @if (auth()->user()->hasRole('superadmin'))
                            <a class="admin-nav__link {{ request()->routeIs('admin.permissions.*') ? 'is-active' : '' }}" href="{{ route('admin.permissions.index') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">admin_panel_settings</span><span class="admin-nav__label">Hak Akses (Permission)</span></a>
                        @endif
                        @if (auth()->user()->hasRole('superadmin') || auth()->user()->role?->hasPermission('activity_logs', 'view'))
                            <a class="admin-nav__link {{ request()->routeIs('admin.activity-logs') ? 'is-active' : '' }}" href="{{ route('admin.activity-logs') }}" data-nav-link><span class="material-symbols-rounded" aria-hidden="true">history</span><span class="admin-nav__label">Log Aktivitas</span></a>
                        @endif
                    </div>
                @endif
            </nav>

            <div class="admin-sidebar__footer">
                <a class="admin-sidebar__profile" href="{{ route('admin.password.edit') }}" aria-label="Profil {{ auth()->user()->name }}">
                    <span class="admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="admin-sidebar__user-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span>
                </a>
                <form class="admin-sidebar__logout-form" method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="admin-sidebar__logout" aria-label="Keluar" title="Keluar">
                        <span class="material-symbols-rounded" aria-hidden="true">power_settings_new</span>
                    </button>
                </form>
            </div>
        </aside>

        <button class="admin-sidebar-overlay" type="button" aria-label="Tutup navigasi" data-sidebar-close hidden></button>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar__navigation">
                    <button class="admin-menu-toggle" type="button" aria-label="Buka atau ciutkan navigasi" aria-expanded="true" data-sidebar-toggle>
                        <span class="material-symbols-rounded" aria-hidden="true">menu</span>
                    </button>
                    <label class="admin-topbar-search">
                        <span class="material-symbols-rounded" aria-hidden="true">search</span>
                        <input type="search" placeholder="Cari modul, konten, aset..." aria-label="Cari menu navigasi" data-admin-nav-search>
                    </label>
                </div>
                <div class="admin-topbar__tools">
                    <button class="admin-icon-button" type="button" aria-label="Ubah tema" title="Ubah tema" data-theme-toggle>
                        <span class="material-symbols-rounded" aria-hidden="true" data-theme-icon>dark_mode</span>
                    </button>
                    <div class="admin-notifications">
                        <button class="admin-icon-button" type="button" aria-label="Notifikasi" aria-expanded="false" aria-controls="admin-notification-panel" data-notification-toggle>
                            <span class="material-symbols-rounded" aria-hidden="true">notifications</span>
                        </button>
                        <div class="admin-notifications__panel" id="admin-notification-panel" data-notification-panel hidden>
                            <strong>Notifikasi</strong>
                            <p>Tidak ada notifikasi baru.</p>
                        </div>
                    </div>
                    <a class="admin-topbar__public" href="{{ route('home') }}" target="_blank" rel="noopener">
                        <span class="material-symbols-rounded" aria-hidden="true">open_in_new</span><span>Lihat Website</span>
                    </a>
                    <a class="admin-user-pill" href="{{ route('admin.password.edit') }}" aria-label="Profil {{ auth()->user()->name }}">
                        <span class="admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="admin-user-pill__copy"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->role?->description }}</small></span>
                        <span class="material-symbols-rounded admin-user-pill__chevron" aria-hidden="true">expand_more</span>
                    </a>
                </div>
            </header>

            <div class="admin-content">
                @hasSection('page-heading')
                    <section class="admin-page-heading">
                        <div>
                            <p class="admin-page-breadcrumb">@yield('breadcrumb')</p>
                            <h1>@yield('page-heading')</h1>
                            @if (trim($__env->yieldContent('page-description')) !== '')<p>@yield('page-description')</p>@endif
                        </div>
                        @hasSection('page-actions')<div class="admin-page-heading__actions">@yield('page-actions')</div>@endif
                    </section>
                @endif
                @if (session('status'))<span data-admin-flash-success="{{ session('status') }}" hidden></span>@endif
                @if ($errors->any())<span data-admin-flash-error="{{ $errors->first() }}" hidden></span>
                    <div class="admin-alert admin-alert--error" role="alert"><span class="material-symbols-rounded" aria-hidden="true">error</span><span>{{ $errors->first() }}</span></div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
    @include('admin.partials.feedback-modals')
</body>
</html>
