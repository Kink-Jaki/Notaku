<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $settings = \App\Support\SettingsHelper::get();
        @endphp

        <title>@yield('title', $settings->brand_name)</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        {{-- Global SweetAlert2 Theme Configuration --}}
        @stack('scripts-early')

        <!-- Favicon -->
        <link rel="icon" href="{{ $settings->favicon_path ? asset('storage/'.$settings->favicon_path) : asset('favicon.ico') }}">

        <!-- Dynamic CSS Variables from Settings (override variables.css) -->
        <style>
{!! \App\Support\SettingsHelper::sidebarCss($settings) !!}

            /* Warna netral per varian tema (latar, border, teks).
               Dibatasi ke mode terang: blok [data-theme="dark"] di
               variables.css harus tetap menang, dan spesifisitasnya sama. */
            :root:not([data-theme="dark"]) {
{!! \App\Support\SettingsHelper::surfaceCss($settings->theme_variant) !!}
            }

            /* Developer Mode - Advanced styles when dev_mode is enabled */
            @if ($settings->dev_mode)
                .dev-mode-badge { display: inline-block !important; }
                .app-page { padding: 1rem; }
                .card { border-width: 1px; }
                .btn { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
                .form-control, .form-select { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
                .app-sidebar__nav { padding: 0.5rem; gap: 0; }
                .app-sidebar .nav-link { padding: 0.375rem 0.5rem; font-size: 0.8125rem; }
            @endif
        </style>
    </head>
    <body>
        @php
            $sidebarMode = $sidebarMode ?? 'static';
        @endphp

        @hasSection('store-shell')
            @yield('store-shell')
        @else
        @if(Auth::check() && (! isset($hideSidebar) || ! $hideSidebar))
        <nav class="app-topbar">
            <button
                type="button"
                class="sidebar-toggle{{ $sidebarMode === 'drawer' ? ' sidebar-toggle--always' : '' }}"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebarOffcanvas"
                aria-controls="sidebarOffcanvas"
                aria-label="Buka menu"
            >
                <i class="bi bi-list"></i>
            </button>

            <a href="{{ url('/') }}" class="app-topbar__brand">
                <span class="app-sidebar__brand-mark">
                    @if ($settings->logo_path)
                        <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->brand_name }}" style="height: 28px; width: auto;">
                    @else
                        <i class="bi bi-bag"></i>
                    @endif
                </span>
                <span class="fw-semibold">{{ $settings->brand_name }}</span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-2">
                <div class="dropdown">
                    <button
                        type="button"
                        class="btn btn-light dropdown-toggle d-flex align-items-center gap-2"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <span class="avatar avatar--sm">{{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                        <span class="d-none d-md-inline small fw-semibold">{{ Auth::user()->name }}</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header">{{ Auth::user()->name }} <span class="d-block fw-normal text-capitalize">{{ Auth::user()->role }}</span></div>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="bi bi-person me-2"></i> Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <aside
            id="sidebarOffcanvas"
            class="app-sidebar {{ $sidebarMode === 'drawer' ? 'app-sidebar--drawer offcanvas offcanvas-start' : 'offcanvas-lg offcanvas-start' }}"
            tabindex="-1"
            aria-labelledby="sidebarBrand"
        >
            @yield('sidebar-menu')

            <div class="app-sidebar__footer">
                <div class="app-sidebar__footer-content">
                    <span class="app-sidebar__user-avatar">{{ strtoupper(mb_strimwidth(Auth::user()->name ?? '', 0, 1, '')) }}</span>
                    <div class="app-sidebar__user-info">
                        <span class="app-sidebar__user-name text-truncate">{{ Auth::user()->name ?? 'User' }}</span>
                        <span class="app-sidebar__user-role text-truncate">{{ Auth::user()->role ?? '-' }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="app-sidebar__logout-btn">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>
        @else
        @if(! Auth::check())
        <nav class="app-topbar">
            <a href="{{ url('/') }}" class="app-topbar__brand">
                <span class="app-sidebar__brand-mark">
                    @if ($settings->logo_path)
                        <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->brand_name }}" style="height: 28px; width: auto;">
                    @else
                        <i class="bi bi-bag"></i>
                    @endif
                </span>
                <span class="fw-semibold">{{ $settings->brand_name }}</span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="{{ route('login') }}" class="btn btn-brand btn-sm">Login</a>
            </div>
        </nav>
        @endif
        @hasSection('topbar')
        <nav class="store-topbar">
            @yield('topbar')
        </nav>
        @endif
        @endif

        <div class="app-main @if((isset($hideSidebar) && $hideSidebar) || Auth::guest() || $sidebarMode === 'drawer') app-main--no-sidebar @endif">
            <main class="app-page">
                @yield('content')
            </main>

            @if(!isset($hideFullFooter) || !$hideFullFooter)
            @include('layouts.partials.footer')
@else
            <footer class="app-footer app-footer--minimal mt-auto" style="color: var(--color-footer-text);">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <span>&copy; {{ date('Y') }} {{ $settings->brand_name ?? 'Notaku' }}</span>
                    <span>@if(Auth::check()) Panel {{ Auth::user()->role ?? 'User' }} @else {{ $settings->brand_name ?? 'Notaku' }} @endif</span>
                </div>
            </footer>
@endif
        </div>
        @endif

        {{-- ================= TOMBOL HELPER (PUAT BANTUAN) ================= --}}
        <button
            type="button"
            class="helper-fab"
            data-bs-toggle="modal"
            data-bs-target="#helperModal .modal"
            aria-label="Buka pusat bantuan"
            title="Pusat Bantuan"
        >
            <i class="bi bi-question-circle-fill" aria-hidden="true"></i>
        </button>

        <div id="helperModal">
            <x-modal size="modal-sm" title="Pusat Bantuan">
                <p class="small text-muted-pos mb-3">Ada yang bisa dibantu? Pilih topik di bawah ini.</p>

                <div class="list-group list-group-flush">
                    <a href="{{ route('pusat-bantuan') }}#cara-memesan" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-bag-check text-primary"></i>
                        <span>Bagaimana cara memesan produk?</span>
                    </a>
                    <a href="{{ route('pusat-bantuan') }}#barang-tidak-sesuai" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle text-warning"></i>
                        <span>Barang tidak sesuai?</span>
                    </a>
                    <a href="{{ route('pusat-bantuan') }}#lupa-password" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-key text-danger"></i>
                        <span>Lupa password?</span>
                    </a>
                    <a href="{{ route('pusat-bantuan') }}#hubungi-admin" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-headset text-success"></i>
                        <span>Hubungi admin</span>
                    </a>
                </div>
            </x-modal>
        </div>

        @include('partials.swal-flash')

        @stack('scripts')
        @yield('scripts')
    </body>
</html>
