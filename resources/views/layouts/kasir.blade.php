@extends('layouts.app')

@php
    $hideFullFooter = true;
@endphp

@section('title', 'Kasir — Notaku')

@section('sidebar-menu')
    @php
        $current = Route::currentRouteName();
        $pendingCount = \App\Models\Order::where('status', 'pending')->count();
        $kasirNav = [
            ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'kasir.dashboard', 'href' => route('kasir.dashboard')],
            ['label' => 'Antrian Pesanan', 'icon' => 'bi-list-check', 'route' => 'kasir.antrian', 'href' => route('kasir.antrian'), 'count' => $pendingCount],
            ['label' => 'Kasir / POS', 'icon' => 'bi-calculator', 'route' => 'kasir.pos', 'href' => route('kasir.pos')],
            ['label' => 'Riwayat Transaksi', 'icon' => 'bi-clock-history', 'route' => 'kasir.riwayat', 'href' => route('kasir.riwayat')],
        ];
        $kasirReports = [
            ['label' => 'Laporan Harian', 'icon' => 'bi-calendar-day', 'route' => 'kasir.laporan-harian', 'href' => route('kasir.laporan-harian')],
            ['label' => 'Laporan Bulanan (PDF)', 'icon' => 'bi-file-earmark-pdf', 'route' => 'kasir.laporan-bulanan', 'href' => route('kasir.laporan-bulanan')],
        ];
        $settings = \App\Support\SettingsHelper::get();
    @endphp

    {{-- Brand --}}
    <div class="app-sidebar__brand">
        <span class="app-sidebar__brand-mark">
            @if ($settings->logo_path)
                <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="{{ $settings->brand_name }}" style="height: 28px; width: auto;">
            @else
                <i class="bi bi-bag"></i>
            @endif
        </span>
        <span class="app-sidebar__brand-text">{{ $settings->brand_name }}</span>
    </div>

    {{-- Collapse/Expand Button --}}
    <button type="button" class="app-sidebar__collapse-btn" id="sidebarCollapseBtn" aria-label="Perlebar sidebar" aria-expanded="false">
        <i class="bi bi-chevron-left" aria-hidden="true"></i>
        <span>Perlebar</span>
    </button>

    {{-- Navigation Sections --}}
    <nav class="app-sidebar__nav nav flex-column" aria-label="Menu kasir">
    <div class="app-sidebar__section-title">Kasir</div>

    @foreach ($kasirNav as $item)
        <a
            class="nav-link {{ $current === $item['route'] ? 'active' : '' }}"
            href="{{ $item['href'] }}"
            {{ $current === $item['route'] ? 'aria-current="page"' : '' }}
            data-tooltip="{{ $item['label'] }}"
        >
            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
            <span class="app-sidebar__nav-text">{{ $item['label'] }}</span>
            @if (! empty($item['count']))
                <span class="app-sidebar__badge" data-count="{{ $item['count'] }}">{{ $item['count'] }}</span>
            @endif
        </a>
    @endforeach

    <div class="app-sidebar__section-title">Laporan</div>

    @foreach ($kasirReports as $item)
        <a class="nav-link {{ $current === $item['route'] ? 'active' : '' }}" href="{{ $item['href'] }}" data-tooltip="{{ $item['label'] }}">
            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
            <span class="app-sidebar__nav-text">{{ $item['label'] }}</span>
        </a>
    @endforeach

    <div class="app-sidebar__section-title">Lainnya</div>

    @include('partials.nav-member')
    </nav>
@endsection

@push('scripts')
<script>
    let lastPendingCount = null;
    function checkNewOrders() {
        fetch('/kasir/antrian/check-new', {
            headers: { 'Accept': 'application/json' },
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const newCount = data.pending_count;
                const isFirstPoll = lastPendingCount === null;
                const previousCount = lastPendingCount;
                lastPendingCount = newCount;

                if (isFirstPoll || newCount <= previousCount) {
                    return;
                }

                // Update badge in sidebar
                const badge = document.querySelector('.app-sidebar__badge[data-count]');
                if (badge) {
                    badge.textContent = newCount;
                    badge.dataset.count = newCount;
                    // Trigger animation
                    badge.style.animation = 'none';
                    badge.offsetHeight; // Force reflow
                    badge.style.animation = 'badge-pulse 2s ease-in-out infinite';
                }

                if (typeof Swal !== 'undefined' && !Swal.isVisible()) {
                    Swal.fire({
                        title: 'Pesanan Baru!',
                        text: `${newCount} pesanan baru masuk`,
                        icon: 'success',
                        timer: 3000,
                        timerProgressBar: true,
                        position: 'top-end',
                        showConfirmButton: false
                    });
                }
            })
            .catch(error => {
                console.error('Gagal cek pesanan baru:', error);
            });
    }
    setInterval(checkNewOrders, 10000);
    checkNewOrders();
</script>
@endpush