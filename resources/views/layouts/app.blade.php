<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $settings = \App\Support\SettingsHelper::get();
        @endphp

        <title>@yield('title', $settings->brand_name)</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <!-- Favicon -->
        <link rel="icon" href="{{ $settings->favicon_path ? asset('storage/'.$settings->favicon_path) : asset('favicon.ico') }}">

        <!-- Dynamic CSS Variables from Settings (override variables.css) -->
        <style>
            :root {
                --color-primary: {{ $settings->color_primary }};
                --color-primary-dark: {{ $settings->color_primary_dark }};
                --color-success: {{ $settings->color_success }};
                --color-warning: {{ $settings->color_warning }};
                --color-danger: {{ $settings->color_danger }};
                /* Derive sidebar colors from primary for consistency */
                --color-sidebar-bg: {{ $settings->color_primary_dark }};
                --color-sidebar-text: #E0E7FF;
                --color-sidebar-muted: rgba(224, 231, 255, 0.6);
            }
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
            <div id="sidebarBrand" class="app-sidebar__brand">
                <span class="app-sidebar__brand-mark">
                    @if ($settings->logo_path)
                        <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->brand_name }}" style="height: 28px; width: auto;">
                    @else
                        <i class="bi bi-bag"></i>
                    @endif
                </span>
                <span>{{ $settings->brand_name }}</span>
            </div>

            <button
                type="button"
                class="btn-close btn-close-white app-sidebar__close"
                data-bs-dismiss="offcanvas"
                data-bs-target="#sidebarOffcanvas"
                aria-label="Tutup menu"
            ></button>

            <nav class="app-sidebar__nav nav flex-column">
                @yield('sidebar-menu')
            </nav>

            <div class="app-sidebar__footer">
                <div class="d-flex align-items-center gap-2 text-truncate">
                    <span class="avatar avatar--dark">{{ strtoupper(mb_substr(Auth::user()->name ?? '', 0, 1)) }}</span>
                    <div class="text-truncate">
                        <div class="text-white fw-semibold text-truncate">{{ Auth::user()->name ?? 'User' }}</div>
                        <div class="text-white-50 small text-capitalize">{{ Auth::user()->role ?? '-' }}</div>
                    </div>
                </div>
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
            <footer class="app-footer app-footer--minimal mt-auto">
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