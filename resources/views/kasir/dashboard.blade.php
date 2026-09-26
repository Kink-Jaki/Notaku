@extends('layouts.kasir')

@section('title', 'Dashboard — Kasir')
@section('page_title', 'Dashboard Kasir')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div class="fw-semibold">{{ now()->translatedFormat('l, d F Y') }}</div>
        <div class="small text-muted-pos">
            <i class="bi bi-person-badge me-1"></i> Sesi: {{ Auth::user()->name }}
        </div>
    </div>

    {{-- Ringkasan (skeleton, diisi JS) --}}
    <div class="row g-3 mb-4" id="kasir-stats" aria-busy="true">
        @foreach (range(1, 4) as $statIndex)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="stat-card">
                    <div>
                        <div class="stat-card__label">
                            <span class="skeleton skeleton--text w-50"></span>
                        </div>
                        <div class="stat-card__value">
                            <span class="skeleton skeleton--value w-75"></span>
                        </div>
                    </div>
                    <span class="skeleton skeleton--icon"></span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="pane h-100" id="kasir-transactions-pane" aria-busy="true">
                <div class="pane__header">
                    <h2 class="pane__title h5">Transaksi Terbaru Hari Ini</h2>
                    <span id="kasir-transactions-count">
                        <span class="skeleton skeleton--badge"></span>
                    </span>
                </div>
                <div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Jam</th>
                                <th>Kasir</th>
                                <th>Jenis</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kasir-transactions-body">
                            @foreach (range(1, 5) as $trxIndex)
                                <tr>
                                    <td><span class="skeleton skeleton--text w-75"></span></td>
                                    <td><span class="skeleton skeleton--text w-50"></span></td>
                                    <td><span class="skeleton skeleton--text w-75"></span></td>
                                    <td><span class="skeleton skeleton--text w-50"></span></td>
                                    <td><span class="skeleton skeleton--text w-75"></span></td>
                                    <td><span class="skeleton skeleton--text w-50"></span></td>
                                    <td class="text-end"><span class="skeleton skeleton--text w-25 ms-auto"></span></td>
                                </tr>
                            @endforeach
                        </tbody>
                        </table>
                    </div>
                </div>
                <div class="text-end mt-3">
                    <a href="{{ route('kasir.riwayat') }}" class="small fw-semibold">Lihat Semua &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="pane h-100" id="kasir-pending-pane" aria-busy="true">
                <div class="pane__header">
                    <h2 class="pane__title h5">Pesanan Menunggu Konfirmasi</h2>
                    <span id="kasir-pending-count">
                        <span class="skeleton skeleton--badge"></span>
                    </span>
                </div>
                <div id="kasir-pending-orders">
                    @foreach (range(1, 3) as $orderIndex)
                        <div class="order-card">
                            <div class="order-card__header">
                                <span class="skeleton skeleton--text w-50"></span>
                                <span class="skeleton skeleton--text w-25"></span>
                            </div>
                            <div class="order-card__meta">
                                <span class="skeleton skeleton--avatar"></span>
                                <span class="skeleton skeleton--text w-50"></span>
                                <span class="skeleton skeleton--text w-25"></span>
                            </div>
                            <div class="mt-2">
                                <span class="skeleton skeleton--text skeleton--w-40"></span>
                            </div>
                            <div class="order-card__actions">
                                <span class="skeleton skeleton--btn"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-end mt-3">
                    <a href="{{ route('kasir.antrian') }}" class="small fw-semibold">Buka Antrian &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="pane d-flex flex-wrap gap-2 mt-4">
        <a href="{{ route('kasir.pos') }}" class="btn btn-brand">
            <i class="bi bi-plus-lg me-1"></i> Transaksi Baru
        </a>
        <a href="{{ route('kasir.antrian') }}" class="btn btn-outline-primary">
            <i class="bi bi-list-check me-1"></i> Antrian Pesanan
        </a>
        <a href="{{ route('kasir.riwayat') }}" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i> Riwayat Transaksi
        </a>
        <a href="{{ route('kasir.laporan-harian') }}" class="btn btn-outline-secondary">
            <i class="bi bi-calendar-day me-1"></i> Laporan Hari Ini
        </a>
    </div>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @push('scripts')
            @vite('resources/js/kasir-dashboard-loader.js')
        @endpush
    @endif
@endsection
