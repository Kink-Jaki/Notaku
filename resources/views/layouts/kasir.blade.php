@extends('layouts.app')

@php
    $sidebarMode = 'drawer';
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
        $user = Auth::user();
    @endphp

    <div class="app-sidebar__section-title">Kasir</div>

    @foreach ($kasirNav as $item)
        <a
            class="nav-link {{ $current === $item['route'] ? 'active' : '' }}"
            href="{{ $item['href'] }}"
            {{ $current === $item['route'] ? 'aria-current="page"' : '' }}
        >
            <i class="bi {{ $item['icon'] }}"></i>
            <span class="flex-grow-1">{{ $item['label'] }}</span>
            @if (! empty($item['count']))
                <span class="badge badge-soft badge-soft--warning rounded-pill">{{ $item['count'] }}</span>
            @endif
        </a>
    @endforeach

    <div class="app-sidebar__section-title">Laporan</div>

    @foreach ($kasirReports as $item)
        <a class="nav-link {{ $current === $item['route'] ? 'active' : '' }}" href="{{ $item['href'] }}">
            <i class="bi {{ $item['icon'] }}"></i>
            <span class="flex-grow-1">{{ $item['label'] }}</span>
        </a>
    @endforeach

    <div class="app-sidebar__section-title">Lainnya</div>

    @include('partials.nav-member')
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
