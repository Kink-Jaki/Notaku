@extends('layouts.kasir')

@section('title', 'Riwayat Transaksi — Kasir')
@section('page_title', 'Riwayat Transaksi')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <p class="text-muted-pos mb-0 small">Seluruh transaksi kasir manual & pesanan online yang disetujui</p>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge badge-soft badge-soft--success fs-6 fw-semibold">
                <i class="bi bi-receipt me-1"></i> <span id="riwayat-badge-transaksi"><span class="skeleton skeleton--badge"></span></span> transaksi
            </span>
            <span class="badge badge-soft badge-soft--success fs-6 fw-semibold">
                <i class="bi bi-cash-stack me-1"></i> <span id="riwayat-badge-penjualan"><span class="skeleton skeleton--badge"></span></span>
            </span>
        </div>
    </div>

    <div class="btn-group btn-group-sm mb-4" role="group" aria-label="Rentang tanggal cepat">
        <a href="{{ route('kasir.riwayat', ['dari' => now()->format('Y-m-d'), 'sampai' => now()->format('Y-m-d'), 'jenis' => 'Semua', 'status' => 'Semua', 'q' => '']) }}" class="btn btn-outline-secondary {{ request()->query('dari') == now()->format('Y-m-d') ? 'active' : '' }}">Hari Ini</a>
        <a href="{{ route('kasir.riwayat', ['dari' => now()->subDays(7)->format('Y-m-d'), 'sampai' => now()->format('Y-m-d'), 'jenis' => 'Semua', 'status' => 'Semua', 'q' => '']) }}" class="btn btn-outline-secondary {{ request()->query('dari') == now()->subDays(7)->format('Y-m-d') ? 'active' : '' }}">7 Hari</a>
        <a href="{{ route('kasir.riwayat', ['dari' => now()->subDays(30)->format('Y-m-d'), 'sampai' => now()->format('Y-m-d'), 'jenis' => 'Semua', 'status' => 'Semua', 'q' => '']) }}" class="btn btn-outline-secondary {{ request()->query('dari') == now()->subDays(30)->format('Y-m-d') ? 'active' : '' }}">Bulan Ini</a>
    </div>

    <div class="pane mb-4">
        <form class="row g-2 align-items-end" action="{{ route('kasir.riwayat') }}" method="get" id="riwayat-filter" data-cf="riwayat" data-cf-fetch-submit>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label" for="tanggalDari">Tanggal dari</label>
                <input type="date" class="form-control" id="tanggalDari" name="dari" value="{{ $dari }}">
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label" for="tanggalSampai">Tanggal s/d</label>
                <input type="date" class="form-control" id="tanggalSampai" name="sampai" value="{{ $sampai }}">
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label" for="filterJenis">Jenis</label>
                <select class="form-select" id="filterJenis" name="jenis" data-cf-field="jenis">
                    <option value="Semua" {{ $jenis === 'Semua' ? 'selected' : '' }}>Semua</option>
                    <option value="Kasir" {{ $jenis === 'Kasir' ? 'selected' : '' }}>Kasir</option>
                    <option value="Online" {{ $jenis === 'Online' ? 'selected' : '' }}>Online</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label" for="filterStatus">Status</label>
                <select class="form-select" id="filterStatus" name="status" data-cf-field="status">
                    <option value="Semua" {{ $status === 'Semua' ? 'selected' : '' }}>Semua</option>
                    <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Sukses</option>
                    <option value="dibatalkan" {{ $status === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="search-box">
                    <i class="bi bi-search search-box__icon"></i>
                    <input type="search" class="form-control" placeholder="Cari ID / No. Pesanan..." aria-label="Cari ID transaksi atau nomor pesanan" name="q" value="{{ $q }}" data-cf-search>
                </div>
            </div>
            <div class="col-12 col-lg">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <button type="submit" class="btn btn-brand">
                        <i class="bi bi-funnel me-1"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('kasir.riwayat') }}" class="btn btn-outline-secondary" data-rt-link>
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4" id="riwayat-stats" aria-busy="true">
        @foreach ([
            ['label' => 'Total Transaksi', 'icon' => 'bi-receipt', 'modifier' => 'primary'],
            ['label' => 'Total Penjualan', 'icon' => 'bi-cash-stack', 'modifier' => 'success'],
            ['label' => 'Rata-rata Transaksi', 'icon' => 'bi-graph-up', 'modifier' => 'info'],
        ] as $stat)
            <div class="col-12 col-md-4">
                <div class="stat-card stat-card--{{ $stat['modifier'] }}">
                    <div>
                        <div class="stat-card__label">{{ $stat['label'] }}</div>
                        <div class="stat-card__value"><span class="skeleton skeleton--value w-75"></span></div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="bi {{ $stat['icon'] }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="table-wrap">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Tanggal & Jam</th>
                        <th>Kasir / Pelanggan</th>
                        <th>Jenis</th>
                        <th>Metode</th>
                        <th>Item</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="riwayat-body" aria-busy="true" data-cf="riwayat">
                    @foreach (range(1, 10) as $rowIndex)
                        <tr class="skeleton-row">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
        <small class="text-muted-pos" id="riwayat-info" data-cf="riwayat" data-cf-summary="Menampilkan {n} transaksi di halaman ini">
            <span class="skeleton skeleton--text w-50"></span>
        </small>
        <nav aria-label="Navigasi halaman riwayat transaksi" id="riwayat-pagination"></nav>
    </div>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @push('scripts')
            @vite('resources/js/kasir-riwayat-loader.js')
        @endpush
    @endif
@endsection