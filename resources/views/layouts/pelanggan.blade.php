@extends('layouts.base')

@php
    $settings = \App\Support\SettingsHelper::get();
@endphp

@section('title', 'Marketplace — {{ $settings->brand_name }}')

@section('store-shell')
    @php
        $current = Route::currentRouteName();
        $cart = session('pelanggan_cart', []);
        $cartCount = collect($cart)->sum('qty');
        $cartTotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['qty']);
        $shopNav = [
            ['label' => 'Katalog', 'icon' => 'bi-grid', 'route' => 'pelanggan.katalog', 'href' => route('marketplace')],
            ['label' => 'Pesanan Saya', 'icon' => 'bi-clock-history', 'route' => 'pelanggan.pesanan-saya', 'href' => route('pelanggan.pesanan-saya')],
        ];
    @endphp

    <header class="store-topbar store-topbar--with-sidebar">
        <button
            type="button"
            class="sidebar-toggle"
            data-bs-toggle="offcanvas"
            data-bs-target="#storeSidebarOffcanvas"
            aria-controls="storeSidebarOffcanvas"
            aria-label="Buka menu"
        >
            <i class="bi bi-list"></i>
        </button>

        <a href="{{ route('marketplace') }}" class="store-topbar__brand" aria-label="{{ $settings->brand_name }} — Katalog">
            <span class="app-sidebar__brand-mark">
                @if ($settings->logo_path)
                    <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->brand_name }}" style="height: 28px; width: auto;">
                @else
                    <i class="bi bi-bag"></i>
                @endif
            </span>
            <span>{{ $settings->brand_name }}</span>
        </a>

        <nav class="store-nav nav d-none d-lg-flex">
            @foreach ($shopNav as $item)
                <a
                    class="nav-link {{ $current === $item['route'] ? 'active' : '' }}"
                    href="{{ $item['href'] }}"
                    {{ $current === $item['route'] ? 'aria-current="page"' : '' }}
                >
                    <i class="bi {{ $item['icon'] }}"></i> {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="d-flex align-items-center gap-1 ms-auto">
            @auth
                <div class="dropdown">
                    <button
                        type="button"
                        class="topbar-action dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Keranjang belanja"
                    >
                        <i class="bi bi-cart3"></i>
                        <span class="topbar-action__badge">{{ $cartCount }}</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end cart-menu">
                        <h6 class="dropdown-header">Keranjang ({{ $cartCount }} item)</h6>
                        @if ($cartCount > 0)
                            @foreach ($cart as $productId => $item)
                                <div class="dropdown-item-text cart-row">
                                    <span class="cart-row__name d-block">{{ $item['name'] }}</span>
                                    <span class="cart-row__meta d-block">{{ $item['qty'] }} &times; Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                                    <span class="cart-row__total">Rp {{ number_format($item['qty'] * $item['price'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                            <div class="dropdown-divider"></div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-2 pb-2 pt-1">
                                <span class="small text-muted-pos">Total: <strong class="text-body">Rp {{ number_format($cartTotal, 0, ',', '.') }}</strong></span>
                                <a href="{{ route('pelanggan.cart') }}" class="btn btn-brand btn-sm">
                                    <i class="bi bi-cart-check me-1"></i> Lihat Keranjang
                                </a>
                            </div>
                        @else
                            <p class="dropdown-item-text text-muted">Keranjang kosong</p>
                            <a href="{{ route('marketplace') }}" class="btn btn-brand btn-sm mt-2">Belanja Sekarang</a>
                        @endif
                    </div>
                </div>

                <div class="dropdown">
                    <button
                        type="button"
                        class="topbar-action dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Akun"
                    >
                        <i class="bi bi-person-circle"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header">{{ Auth::user()->name }} <span class="d-block fw-normal text-capitalize">{{ Auth::user()->role }}</span></div>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('pelanggan.pesanan-saya') }}" class="dropdown-item"><i class="bi bi-receipt me-2"></i> Pesanan Saya</a>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="bi bi-person me-2"></i> Profil Saya</a>
                        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger" style="border:none;background:none;cursor:pointer;width:100%;text-align:left;"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="dropdown">
                    <button
                        type="button"
                        class="topbar-action dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Keranjang belanja"
                    >
                        <i class="bi bi-cart3"></i>
                        <span class="topbar-action__badge">{{ $cartCount }}</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end cart-menu">
                        <h6 class="dropdown-header">Keranjang ({{ $cartCount }} item)</h6>
                        @if ($cartCount > 0)
                            @foreach ($cart as $productId => $item)
                                <div class="dropdown-item-text cart-row">
                                    <span class="cart-row__name d-block">{{ $item['name'] }}</span>
                                    <span class="cart-row__meta d-block">{{ $item['qty'] }} &times; Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                                    <span class="cart-row__total">Rp {{ number_format($item['qty'] * $item['price'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                            <div class="dropdown-divider"></div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-2 pb-2 pt-1">
                                <span class="small text-muted-pos">Total: <strong class="text-body">Rp {{ number_format($cartTotal, 0, ',', '.') }}</strong></span>
                                <a href="{{ route('pelanggan.cart') }}" class="btn btn-brand btn-sm">
                                    <i class="bi bi-cart-check me-1"></i> Lihat Keranjang
                                </a>
                            </div>
                        @else
                            <p class="dropdown-item-text text-muted">Masuk untuk belanja</p>
                            <a href="{{ route('login') }}" class="btn btn-brand btn-sm mt-2">Masuk untuk Belanja</a>
                        @endif
                    </div>
                </div>

                <div class="dropdown">
                    <button
                        type="button"
                        class="topbar-action dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Akun"
                    >
                        <i class="bi bi-person-circle"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="{{ route('login') }}" class="dropdown-item"><i class="bi bi-box-arrow-in-right me-2"></i> Masuk</a>
                        <a href="{{ route('register') }}" class="dropdown-item"><i class="bi bi-person-plus me-2"></i> Daftar</a>
                    </div>
                </div>
            @endauth
        </div>
    </header>

    <aside
        id="storeSidebarOffcanvas"
        class="app-sidebar offcanvas-lg offcanvas-start"
        tabindex="-1"
        aria-labelledby="storeSidebarBrand"
    >
        <div id="storeSidebarBrand" class="app-sidebar__brand">
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
            data-bs-target="#storeSidebarOffcanvas"
            aria-label="Tutup menu"
        ></button>

        <nav class="app-sidebar__nav nav flex-column">
            @auth
                <div class="app-sidebar__profile">
                    <span class="avatar avatar--dark">{{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                    <div class="text-truncate">
                        <div class="app-sidebar__profile-name text-truncate">{{ Auth::user()->name }}</div>
                        <div class="app-sidebar__profile-meta text-truncate">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                <div class="app-sidebar__section-title">Menu</div>

                <a class="nav-link {{ $current === 'pelanggan.cart' ? 'active' : '' }}" href="{{ route('pelanggan.cart') }}">
                    <i class="bi bi-cart3"></i>
                    <span class="flex-grow-1">Keranjang</span>
                    <span class="badge badge-soft badge-soft--warning rounded-pill">{{ $cartCount }}</span>
                </a>

                <a class="nav-link {{ $current === 'pusat-bantuan' ? 'active' : '' }}" href="{{ route('pusat-bantuan') }}">
                    <i class="bi bi-info-circle"></i>
                    <span class="flex-grow-1">Pusat Informasi</span>
                </a>

                <a class="nav-link {{ $current === 'profile.edit' ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person"></i>
                    <span class="flex-grow-1">Profil</span>
                </a>
            @else
                <p class="app-sidebar__hint">Masuk untuk belanja, melihat keranjang, dan mengelola pesananmu.</p>
                <a href="{{ route('login') }}" class="btn btn-brand w-100 mb-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                </a>
                <a href="{{ route('register') }}" class="btn btn-outline-light w-100">
                    <i class="bi bi-person-plus me-1"></i> Daftar
                </a>

                <div class="app-sidebar__section-title">Menu</div>

                <a class="nav-link {{ $current === 'pusat-bantuan' ? 'active' : '' }}" href="{{ route('pusat-bantuan') }}">
                    <i class="bi bi-info-circle"></i>
                    <span class="flex-grow-1">Pusat Informasi</span>
                </a>
            @endauth

            <div class="app-sidebar__section-title">Lainnya</div>

            @include('partials.nav-member')
        </nav>

        @auth
            <div class="app-sidebar__footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link text-danger w-100 border-0 bg-transparent">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="flex-grow-1">Logout</span>
                    </button>
                </form>
            </div>
        @endauth
    </aside>

    <div class="store-main store-main--with-sidebar">
        <main class="store-content">
            @yield('content')
        </main>

        @include('layouts.partials.footer')
    </div>
@endsection

@push('scripts')
<script>
    function requireLogin() {
        window.location.href = '{{ route("login") }}';
    }
</script>
@endpush