@extends('layouts.base')

@php
    $settings = \App\Support\SettingsHelper::get();
@endphp

@section('title', 'Marketplace — {{ $settings->brand_name }}')

@section('store-shell')
    @php
        $current = Route::currentRouteName();
        $cart = session('pelanggan_cart', []);
        $promoSession = session('pelanggan_promo');
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
            @include('partials.theme-toggle')

            @auth
                @include('partials.pelanggan-notifikasi')

                <div class="dropdown">
                    <button
                        type="button"
                        class="topbar-action dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Keranjang belanja"
                    >
                        <i class="bi bi-cart3" aria-hidden="true"></i>
                        <span class="topbar-action__badge {{ $cartCount > 0 ? '' : 'd-none' }}" data-cart-badge>{{ $cartCount }}</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end cart-menu">
                        <div class="cart-menu__header">
                            <span class="cart-menu__title"><i class="bi bi-cart3 me-1" aria-hidden="true"></i>Keranjang</span>
                            <span class="cart-menu__count" data-cart-count-label>{{ $cartCount }} item</span>
                        </div>

                        <div data-cart-list-block class="{{ $cartCount > 0 ? '' : 'd-none' }}">
                            <div class="cart-menu__list" data-cart-list>
                                @foreach ($cart as $productId => $item)
                                    <div class="cart-row">
                                        <div class="cart-row__thumb">{{ mb_strtoupper(mb_substr($item['name'], 0, 1)) ?: '?' }}</div>
                                        <div class="cart-row__info">
                                            <span class="cart-row__name">{{ $item['name'] }}</span>
                                            <span class="cart-row__meta">{{ $item['qty'] }} &times; Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                                        </div>
                                        <span class="cart-row__total">Rp {{ number_format($item['qty'] * $item['price'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="cart-menu__footer">
                                <div class="cart-menu__total">
                                    <span>Subtotal</span>
                                    <strong data-cart-total>Rp {{ number_format($cartTotal, 0, ',', '.') }}</strong>
                                </div>
                                <a href="{{ route('pelanggan.cart') }}" class="btn btn-accent btn-sm w-100 d-flex align-items-center justify-content-center gap-1">
                                    <i class="bi bi-cart-check" aria-hidden="true"></i> Lanjut ke Keranjang
                                </a>
                            </div>
                        </div>

                        <div data-cart-empty-block class="cart-menu__empty {{ $cartCount > 0 ? 'd-none' : '' }}">
                            <div class="cart-menu__empty-icon"><i class="bi bi-cart-x" aria-hidden="true"></i></div>
                            <p class="cart-menu__empty-text">Keranjang masih kosong</p>
                            <a href="{{ route('marketplace') }}" class="btn btn-accent btn-sm">
                                <i class="bi bi-grid me-1" aria-hidden="true"></i> Belanja Sekarang
                            </a>
                        </div>
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
                        <span class="avatar avatar--sm" aria-hidden="true">{{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end account-menu">
                        <div class="account-menu__head">
                            <span class="avatar" aria-hidden="true">{{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <div class="account-menu__name text-truncate">{{ Auth::user()->name }}</div>
                                <div class="account-menu__email text-truncate">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('pelanggan.pesanan-saya') }}" class="dropdown-item">
                            <i class="bi bi-receipt me-2" aria-hidden="true"></i> Pesanan Saya
                        </a>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="bi bi-person me-2" aria-hidden="true"></i> Profil Saya
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i> Logout
                            </button>
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
                        <i class="bi bi-cart3" aria-hidden="true"></i>
                        <span class="topbar-action__badge {{ $cartCount > 0 ? '' : 'd-none' }}" data-cart-badge>{{ $cartCount }}</span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end cart-menu">
                        <div class="cart-menu__header">
                            <span class="cart-menu__title"><i class="bi bi-cart3 me-1" aria-hidden="true"></i>Keranjang</span>
                            <span class="cart-menu__count" data-cart-count-label>{{ $cartCount }} item</span>
                        </div>

                        <div class="cart-menu__empty">
                            <div class="cart-menu__empty-icon"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i></div>
                            <p class="cart-menu__empty-text">Masuk untuk mulai belanja</p>
                            <a href="{{ route('login') }}" class="btn btn-accent btn-sm">
                                <i class="bi bi-person me-1" aria-hidden="true"></i> Masuk
                            </a>
                        </div>
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
                        <i class="bi bi-person-circle" aria-hidden="true"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="{{ route('login') }}" class="dropdown-item">
                            <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i> Masuk
                        </a>
                        <a href="{{ route('register') }}" class="dropdown-item">
                            <i class="bi bi-person-plus me-2" aria-hidden="true"></i> Daftar
                        </a>
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
                    <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->brand_name }}" style="height: 26px; width: auto;">
                @else
                    <i class="bi bi-bag-heart" aria-hidden="true"></i>
                @endif
            </span>
            <span class="app-sidebar__brand-text">{{ $settings->brand_name }}</span>
        </div>

        <button
            type="button"
            class="btn-close btn-close-white app-sidebar__close"
            data-bs-dismiss="offcanvas"
            data-bs-target="#storeSidebarOffcanvas"
            aria-label="Tutup menu"
        ></button>

        <button
            type="button"
            id="sidebarCollapseBtn"
            class="app-sidebar__collapse-btn"
            aria-expanded="true"
            aria-label="Perkecil sidebar"
        >
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
            <span>Perkecil</span>
        </button>

        <nav class="app-sidebar__nav nav flex-column" aria-label="Menu utama">
            @auth
                <div class="app-sidebar__profile">
                    <span class="app-sidebar__profile-avatar" aria-hidden="true">
                        {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <div class="app-sidebar__profile-text">
                        <div class="app-sidebar__profile-name">{{ Auth::user()->name }}</div>
                        <div class="app-sidebar__profile-meta">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                <div class="app-sidebar__section-title">Menu</div>

                <a
                    class="nav-link {{ $current === 'pelanggan.cart' ? 'active' : '' }}"
                    href="{{ route('pelanggan.cart') }}"
                    data-tooltip="Keranjang"
                >
                    <i class="bi bi-cart3" aria-hidden="true"></i>
                    <span class="app-sidebar__nav-text">Keranjang</span>
                    <span
                        class="app-sidebar__badge {{ $cartCount > 0 ? '' : 'd-none' }}"
                        data-cart-badge
                        data-count="{{ $cartCount }}"
                    >{{ $cartCount }}</span>
                </a>

                <a
                    class="nav-link {{ $current === 'pusat-bantuan' ? 'active' : '' }}"
                    href="{{ route('pusat-bantuan') }}"
                    data-tooltip="Pusat Informasi"
                >
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    <span class="app-sidebar__nav-text">Pusat Informasi</span>
                </a>

                <a
                    class="nav-link {{ $current === 'profile.edit' ? 'active' : '' }}"
                    href="{{ route('profile.edit') }}"
                    data-tooltip="Profil"
                >
                    <i class="bi bi-person" aria-hidden="true"></i>
                    <span class="app-sidebar__nav-text">Profil</span>
                </a>
            @else
                <div class="app-sidebar__guest-cta">
                    <p class="app-sidebar__hint">Masuk untuk belanja, melihat keranjang, dan mengelola pesananmu.</p>
                    <a href="{{ route('login') }}" class="btn btn-accent w-100 mb-2">
                        <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i> Masuk
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light w-100">
                        <i class="bi bi-person-plus me-1" aria-hidden="true"></i> Daftar
                    </a>
                </div>

                <div class="app-sidebar__section-title">Menu</div>

                <a
                    class="nav-link {{ $current === 'pusat-bantuan' ? 'active' : '' }}"
                    href="{{ route('pusat-bantuan') }}"
                    data-tooltip="Pusat Informasi"
                >
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    <span class="app-sidebar__nav-text">Pusat Informasi</span>
                </a>
            @endauth

            <div class="app-sidebar__section-title">Lainnya</div>

            @include('partials.nav-member')
        </nav>

        @auth
            <div class="app-sidebar__footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="app-sidebar__logout-btn" data-tooltip="Logout">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                        <span>Keluar dari akun</span>
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

@push('scripts-early')
<script>
    window.__CART_SEED__ = {!! json_encode(['cart' => $cart, 'promo' => $promoSession], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    window.__CART_ROUTES__ = {!! json_encode([
        'remove' => route('pelanggan.cart.remove'),
        'promo' => route('pelanggan.cart.promo'),
        'promoRemove' => route('pelanggan.cart.promoRemove'),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
@endpush

@push('scripts')
<script>
    function requireLogin() {
        window.location.href = '{{ route("login") }}';
    }
</script>
@endpush

@push('scripts')
<script>
    (function () {
        const root = document.getElementById('notifDropdown');
        if (!root) {
            return;
        }

        const listUrl = root.dataset.listUrl;
        const readUrl = root.dataset.readUrl;
        const fallbackUrl = root.dataset.fallbackUrl || '#';
        const listEl = root.querySelector('[data-notif-list]');
        const emptyEl = root.querySelector('[data-notif-empty]');
        const badgeEl = root.querySelector('[data-notif-badge]');
        const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        }[char]));

        const resolveUrl = (url) => (typeof url === 'string' && /^(https?:\/\/|\/(?!\/))/.test(url) ? url : fallbackUrl);

        const formatWaktu = (value) => {
            const date = new Date(value);
            return Number.isNaN(date.getTime()) ? '' : date.toLocaleString('id-ID');
        };

        const renderBadge = (unreadCount) => {
            if (!badgeEl) {
                return;
            }
            badgeEl.textContent = String(unreadCount);
            badgeEl.classList.toggle('d-none', unreadCount <= 0);
        };

        const renderList = (notifications) => {
            if (!listEl || !emptyEl) {
                return;
            }

            listEl.innerHTML = notifications.map((item) => {
                const data = item.data || {};
                const unread = item.read_at === null;
                const url = escapeHtml(resolveUrl(data.url));

                return [
                    `<button type="button" class="dropdown-item notif-item${unread ? ' notif-item--unread' : ''}" data-notif-item data-url="${url}">`,
                    '<span class="notif-item__body">',
                    `<span class="notif-item__title">${escapeHtml(data.title || 'Notifikasi')}</span>`,
                    `<span class="notif-item__message">${escapeHtml(data.message || '')}</span>`,
                    `<span class="notif-item__time">${escapeHtml(formatWaktu(item.created_at))}</span>`,
                    '</span>',
                    '</button>',
                ].join('');
            }).join('');

            const kosong = notifications.length === 0;
            listEl.classList.toggle('d-none', kosong);
            emptyEl.classList.toggle('d-none', !kosong);
        };

        const showToast = (item) => {
            const data = item.data || {};
            if (typeof Swal === 'undefined' || Swal.isVisible()) {
                return;
            }
            Swal.fire({
                title: data.title,
                text: data.message,
                icon: data.status === 'rejected' ? 'warning' : 'success',
                timer: 3000,
                timerProgressBar: true,
                position: 'top-end',
                showConfirmButton: false
            });
        };

        let seenIds = null;

        function pollNotifikasi() {
            fetch(listUrl, {
                headers: { 'Accept': 'application/json' },
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then((payload) => {
                    const notifications = Array.isArray(payload.notifications) ? payload.notifications : [];
                    const isFirstPoll = seenIds === null;
                    const knownIds = isFirstPoll ? new Set() : seenIds;
                    const newItems = notifications.filter((item) => !knownIds.has(item.id));

                    notifications.forEach((item) => knownIds.add(item.id));
                    seenIds = knownIds;

                    renderBadge(Number(payload.unread_count) || 0);
                    renderList(notifications);

                    if (isFirstPoll) {
                        return;
                    }
                    newItems.forEach(showToast);
                })
                .catch(() => {});
        }

        root.addEventListener('click', (event) => {
            const item = event.target.closest('[data-notif-item]');
            if (!item) {
                return;
            }

            event.preventDefault();

            fetch(readUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(() => {
                    renderBadge(0);
                    root.querySelectorAll('[data-notif-item]').forEach((el) => el.classList.remove('notif-item--unread'));
                    window.location.href = item.dataset.url || fallbackUrl;
                })
                .catch(() => {});
        });

        setInterval(pollNotifikasi, 10000);
        pollNotifikasi();
    })();
</script>
@endpush