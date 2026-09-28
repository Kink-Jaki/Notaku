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

        {{-- Global SweetAlert2 Theme Configuration --}}
        @stack('scripts-early')

        <!-- Favicon -->
        <link rel="icon" href="{{ $settings->favicon_path ? asset('storage/'.$settings->favicon_path) : asset('favicon.ico') }}">

        <!-- Dynamic CSS Variables from Settings (override variables.css) -->
        <style>
            :root {
                --color-primary: {{ $settings->color_primary }};
                --color-primary-dark: {{ $settings->color_primary_dark }};
                --color-secondary: {{ $settings->color_secondary }};
                --color-secondary-dark: {{ $settings->color_secondary_dark }};
                --color-success: {{ $settings->color_success }};
                --color-warning: {{ $settings->color_warning }};
                --color-danger: {{ $settings->color_danger }};
                /* Derive sidebar colors from primary for consistency */
                --color-sidebar-bg: {{ $settings->color_primary_dark }};
                --color-sidebar-text: #E0E7FF;
                --color-sidebar-muted: rgba(224, 231, 255, 0.6);
                /* Footer uses primary dark */
                --color-footer-bg: {{ $settings->color_primary_dark }};
                --color-footer-text: #FFFFFF;
            }

            /* Theme Variant Styles - Applied based on selected preset */
            @php
                $variantStyles = [
                    'default' => '
                        --color-bg: #F8FAFC;
                        --color-surface: #FFFFFF;
                        --color-border: #E2E8F0;
                        --color-text: #1E293B;
                        --color-text-muted: #94A3B8;
                        --color-primary-rgb: 79, 70, 229;
                        --color-success-rgb: 16, 185, 129;
                        --color-warning-rgb: 245, 158, 11;
                        --color-danger-rgb: 239, 68, 68;
                        --color-primary-soft: #EEF2FF;
                        --pos-radius: 0.5rem;
                        --pos-shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 1px rgba(15, 23, 42, 0.06);
                        --pos-shadow-md: 0 4px 6px -1px rgba(15, 23, 42, 0.07), 0 2px 4px -2px rgba(15, 23, 42, 0.05), 0 12px 24px -8px rgba(79, 70, 229, 0.12);
                    ',
                    'ocean' => '
                        --color-bg: #F0FDFA;
                        --color-surface: #FFFFFF;
                        --color-border: #CCFBF1;
                        --color-text: #134E4A;
                        --color-text-muted: #4B9B8E;
                        --color-primary-rgb: 13, 148, 136;
                        --color-success-rgb: 16, 185, 129;
                        --color-warning-rgb: 245, 158, 11;
                        --color-danger-rgb: 239, 68, 68;
                        --color-primary-soft: #CCFBF1;
                        --pos-radius: 0.5rem;
                        --pos-shadow-sm: 0 1px 2px rgba(13, 148, 136, 0.05);
                        --pos-shadow-md: 0 4px 6px -1px rgba(13, 148, 136, 0.07), 0 2px 4px -2px rgba(13, 148, 136, 0.05), 0 12px 24px -8px rgba(13, 148, 136, 0.12);
                    ',
                    'forest' => '
                        --color-bg: #F0FDF4;
                        --color-surface: #FFFFFF;
                        --color-border: #DCFCE7;
                        --color-text: #14532D;
                        --color-text-muted: #4ADE80;
                        --color-primary-rgb: 5, 150, 105;
                        --color-success-rgb: 34, 197, 94;
                        --color-warning-rgb: 245, 158, 11;
                        --color-danger-rgb: 239, 68, 68;
                        --color-primary-soft: #DCFCE7;
                        --pos-radius: 0.5rem;
                        --pos-shadow-sm: 0 1px 2px rgba(5, 150, 105, 0.05);
                        --pos-shadow-md: 0 4px 6px -1px rgba(5, 150, 105, 0.07), 0 2px 4px -2px rgba(5, 150, 105, 0.05), 0 12px 24px -8px rgba(5, 150, 105, 0.12);
                    ',
                    'sunset' => '
                        --color-bg: #FFF7ED;
                        --color-surface: #FFFFFF;
                        --color-border: #FFEDD5;
                        --color-text: #7C2D12;
                        --color-text-muted: #FB923C;
                        --color-primary-rgb: 234, 88, 12;
                        --color-success-rgb: 16, 185, 129;
                        --color-warning-rgb: 251, 191, 36;
                        --color-danger-rgb: 239, 68, 68;
                        --color-primary-soft: #FFEDD5;
                        --pos-radius: 0.5rem;
                        --pos-shadow-sm: 0 1px 2px rgba(234, 88, 12, 0.05);
                        --pos-shadow-md: 0 4px 6px -1px rgba(234, 88, 12, 0.07), 0 2px 4px -2px rgba(234, 88, 12, 0.05), 0 12px 24px -8px rgba(234, 88, 12, 0.12);
                    ',
                    'midnight' => '
                        --color-bg: #0F172A;
                        --color-surface: #1E293B;
                        --color-border: #334155;
                        --color-text: #F1F5F9;
                        --color-text-muted: #94A3B8;
                        --color-primary-rgb: 30, 58, 138;
                        --color-success-rgb: 16, 185, 129;
                        --color-warning-rgb: 245, 158, 11;
                        --color-danger-rgb: 239, 68, 68;
                        --color-primary-soft: #1E3A8A;
                        --pos-radius: 0.5rem;
                        --pos-shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.2);
                        --pos-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -2px rgba(0, 0, 0, 0.2), 0 12px 24px -8px rgba(30, 58, 138, 0.3);
                    ',
                    'rose' => '
                        --color-bg: #FFF1F2;
                        --color-surface: #FFFFFF;
                        --color-border: #FFE4E6;
                        --color-text: #881337;
                        --color-text-muted: #FB7185;
                        --color-primary-rgb: 225, 29, 72;
                        --color-success-rgb: 16, 185, 129;
                        --color-warning-rgb: 245, 158, 11;
                        --color-danger-rgb: 239, 68, 68;
                        --color-primary-soft: #FFE4E6;
                        --pos-radius: 0.5rem;
                        --pos-shadow-sm: 0 1px 2px rgba(225, 29, 72, 0.05);
                        --pos-shadow-md: 0 4px 6px -1px rgba(225, 29, 72, 0.07), 0 2px 4px -2px rgba(225, 29, 72, 0.05), 0 12px 24px -8px rgba(225, 29, 72, 0.12);
                    ',
                    'violet' => '
                        --color-bg: #FAF5FF;
                        --color-surface: #FFFFFF;
                        --color-border: #F3E8FF;
                        --color-text: #4C1D95;
                        --color-text-muted: #C084FC;
                        --color-primary-rgb: 124, 58, 237;
                        --color-success-rgb: 16, 185, 129;
                        --color-warning-rgb: 245, 158, 11;
                        --color-danger-rgb: 239, 68, 68;
                        --color-primary-soft: #F3E8FF;
                        --pos-radius: 0.5rem;
                        --pos-shadow-sm: 0 1px 2px rgba(124, 58, 237, 0.05);
                        --pos-shadow-md: 0 4px 6px -1px rgba(124, 58, 237, 0.07), 0 2px 4px -2px rgba(124, 58, 237, 0.05), 0 12px 24px -8px rgba(124, 58, 237, 0.12);
                    ',
                    'amber' => '
                        --color-bg: #FFFDF6;
                        --color-surface: #FFFFFF;
                        --color-border: #FEF3C7;
                        --color-text: #78350F;
                        --color-text-muted: #FBBF24;
                        --color-primary-rgb: 217, 119, 6;
                        --color-success-rgb: 16, 185, 129;
                        --color-warning-rgb: 251, 191, 36;
                        --color-danger-rgb: 239, 68, 68;
                        --color-primary-soft: #FEF3C7;
                        --pos-radius: 0.5rem;
                        --pos-shadow-sm: 0 1px 2px rgba(217, 119, 6, 0.05);
                        --pos-shadow-md: 0 4px 6px -1px rgba(217, 119, 6, 0.07), 0 2px 4px -2px rgba(217, 119, 6, 0.05), 0 12px 24px -8px rgba(217, 119, 6, 0.12);
                    ',
                ];
                $selectedVariant = $variantStyles[$settings->theme_variant] ?? $variantStyles['default'];
            @endphp
            {{ $selectedVariant }}

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
                <div class="p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-10">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="avatar avatar--dark avatar--lg flex-shrink-0">{{ strtoupper(mb_substr(Auth::user()->name ?? '', 0, 1)) }}</span>
                        <div class="text-truncate flex-grow-1">
                            <div class="text-white fw-semibold text-truncate">{{ Auth::user()->name ?? 'User' }}</div>
                            <div class="text-white-50 small text-capitalize">{{ Auth::user()->role ?? '-' }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-light w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
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