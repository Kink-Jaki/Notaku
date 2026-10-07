@extends('layouts.base')

@php
    $settings = \App\Support\SettingsHelper::get();
    $currentRoute = Route::currentRouteName();
    $cart = session('pelanggan_cart', []);
    $cartCount = collect($cart)->sum('qty');

    $bottomNav = [
        ['label' => 'Beranda', 'icon' => 'bi-house', 'route' => 'marketplace', 'href' => route('marketplace')],
        ['label' => 'Katalog', 'icon' => 'bi-grid', 'route' => 'pelanggan.katalog', 'href' => route('pelanggan.katalog.index')],
        ['label' => 'Keranjang', 'icon' => 'bi-cart3', 'route' => 'pelanggan.cart', 'href' => route('pelanggan.cart'), 'badge' => $cartCount],
        ['label' => 'Pesanan', 'icon' => 'bi-clock-history', 'route' => 'pelanggan.pesanan-saya', 'href' => route('pelanggan.pesanan-saya')],
        ['label' => 'Akun', 'icon' => 'bi-person', 'route' => 'profile.edit', 'href' => route('profile.edit')],
    ];
@endphp

@section('title', $settings->brand_name)

@section('store-shell')
    {{-- Mobile Top Bar --}}
    <header class="mobile-topbar">
        <a href="{{ route('marketplace') }}" class="mobile-topbar__brand" aria-label="{{ $settings->brand_name }}">
            <span class="mobile-topbar__brand-mark">
                @if ($settings->logo_path)
                    <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->brand_name }}" style="height: 28px; width: auto;">
                @else
                    <i class="bi bi-bag"></i>
                @endif
            </span>
            <span>{{ $settings->brand_name }}</span>
        </a>

        @auth
            <div class="mobile-topbar__actions">
                {{-- Cart Button --}}
                <a href="{{ route('pelanggan.cart') }}" class="mobile-topbar__action" aria-label="Keranjang ({{ $cartCount }} item)">
                    <i class="bi bi-cart3"></i>
                    @if ($cartCount > 0)
                        <span class="mobile-topbar__badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                    @endif
                </a>

                {{-- User Menu Dropdown --}}
                <div class="dropdown">
                    <button type="button" class="mobile-topbar__action dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu pengguna">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end mobile-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar--dark">{{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                                <div>
                                    <div class="fw-semibold">{{ Auth::user()->name }}</div>
                                    <small class="text-muted-pos">{{ Auth::user()->email }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('pelanggan.pesanan-saya') }}" class="dropdown-item"><i class="bi bi-receipt me-2"></i> Pesanan Saya</a>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="bi bi-person me-2"></i> Profil</a>
                        <a href="{{ route('pusat-bantuan') }}" class="dropdown-item"><i class="bi bi-info-circle me-2"></i> Bantuan</div>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger w-100 text-start" style="border:none;background:none;cursor:pointer;padding:0.5rem 1rem;"><i class="bi bi-box-arrow-right me-2"></i> Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <a href="{{ route('login') }}" class="mobile-topbar__action mobile-topbar__action--login" aria-label="Masuk">
                <i class="bi bi-box-arrow-in-right"></i>
            </a>
        @endauth
    </header>

    {{-- Main Content --}}
    <main class="mobile-main">
        @yield('content')
    </main>

    {{-- Bottom Navigation --}}
    @auth
        <nav class="mobile-bottombar" aria-label="Navigasi utama">
            @foreach ($bottomNav as $item)
                <a href="{{ $item['href'] }}"
                   class="mobile-bottombar__item {{ $currentRoute === $item['route'] ? 'active' : '' }}"
                   {{ $currentRoute === $item['route'] ? 'aria-current="page"' : '' }}>
                    <i class="bi {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                    @if (isset($item['badge']) && $item['badge'] > 0)
                        <span class="mobile-bottombar__badge">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
    @endauth

    {{-- Floating Action Button for Guest --}}
    @guest
        <a href="{{ route('marketplace') }}" class="mobile-fab" aria-label="Mulai belanja">
            <i class="bi bi-bag-plus"></i>
        </a>
    @endguest

    {{-- Toast Container --}}
    <div id="mobileToastContainer" class="mobile-toast-container" role="region" aria-live="polite" aria-label="Notifikasi"></div>
@endsection

@push('scripts')
<script>
    // Global mobile toast function
    window.showMobileToast = function(message, type = 'info', duration = 3000) {
        const container = document.getElementById('mobileToastContainer');
        if (!container) return;

        const bgColor = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : (type === 'warning' ? '#f59e0b' : '#0ea5e9'));
        const icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'x-circle' : (type === 'warning' ? 'exclamation-triangle' : 'info-circle'));

        const toast = document.createElement('div');
        toast.className = 'mobile-toast';
        toast.role = 'alert';
        toast.innerHTML = `
            <div style="background: ${bgColor}; color: white; border-radius: var(--pos-radius); padding: 0.875rem 1rem; box-shadow: var(--pos-shadow-md); display: flex; align-items: center; gap: 0.5rem; animation: slideInUp 0.3s ease;">
                <i class="bi bi-${icon}"></i>
                <span>${message}</span>
            </div>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOutDown 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };

    // Add toast animations
    const style = document.createElement('style');
    style.textContent = `
        .mobile-toast-container {
            position: fixed;
            bottom: 5.5rem;
            left: 1rem;
            right: 1rem;
            max-width: 400px;
            margin: 0 auto;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            pointer-events: none;
        }
        .mobile-toast {
            pointer-events: auto;
        }
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(1rem); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideOutDown {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(1rem); }
        }
        @media (min-width: 768px) {
            .mobile-toast-container {
                bottom: 2rem;
                right: 2rem;
                left: auto;
                margin: 0;
            }
        }
    `;
    document.head.appendChild(style);
</script>
@endpush