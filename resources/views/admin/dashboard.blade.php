@extends('layouts.admin')

@section('title', 'Dashboard — Admin')
@section('page_title', 'Dashboard Admin')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div class="fw-semibold">Overview keseluruhan POS &amp; Order</div>
        <div class="text-muted-pos small">{{ now()->translatedFormat('l, d F Y') }}</div>
    </div>

    {{-- ================= RINGKASAN (skeleton, diisi via /api/admin/dashboard) ================= --}}
    <div class="row g-3 mb-4 row-cols-2 row-cols-md-4" id="admin-stats" aria-busy="true">
        @foreach (range(1, 4) as $statIndex)
            <div class="col">
                <div class="skeleton-card skeleton-card--stat"></div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <div class="pane h-100">
                <div class="pane__header">
                    <h2 class="pane__title h5">Ringkasan Penjualan 7 Hari Terakhir</h2>
                    <span class="badge badge-soft badge-soft--neutral">7 hari</span>
                </div>
                <div class="chart-container" id="admin-sales-chart" aria-busy="true">
                    <div class="skeleton-card skeleton-card--chart"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="pane h-100 d-flex flex-column" id="admin-stocks-pane" aria-busy="true">
                <div class="pane__header">
                    <h2 class="pane__title h5">Stok Menipis / Habis</h2>
                    <span id="admin-stocks-count">
                        <span class="skeleton skeleton--badge"></span>
                    </span>
                </div>
                <div class="d-flex flex-column gap-2 flex-grow-1" id="admin-stocks">
                    @foreach (range(1, 4) as $stockIndex)
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="skeleton skeleton--text w-75"></span>
                            <span class="skeleton skeleton--badge"></span>
                        </div>
                    @endforeach
                </div>
                <div class="text-end mt-3">
                    <a href="{{ route('admin.produk.index') }}" class="small fw-semibold">Kelola Produk &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="pane" id="admin-transactions-pane" aria-busy="true">
        <div class="pane__header">
            <h2 class="pane__title h5">Transaksi Terbaru (Semua Outlet/Sesi)</h2>
            <span id="admin-transactions-count">
                <span class="skeleton skeleton--badge"></span>
            </span>
        </div>
        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Waktu</th>
                            <th>Kasir</th>
                            <th>Jenis</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="admin-transactions-body">
                        @foreach (range(1, 5) as $trxIndex)
                            <tr>
                                <td><span class="skeleton skeleton--text w-75"></span></td>
                                <td><span class="skeleton skeleton--text w-50"></span></td>
                                <td><span class="skeleton skeleton--text w-75"></span></td>
                                <td><span class="skeleton skeleton--text w-50"></span></td>
                                <td><span class="skeleton skeleton--text w-75"></span></td>
                                <td><span class="skeleton skeleton--text w-50"></span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="text-end mt-3">
            <a href="{{ url('/kasir/riwayat') }}" class="small fw-semibold">Lihat Riwayat &rarr;</a>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-12 col-md-4">
            <div class="pane text-center py-2">
                <div class="small text-muted-pos mb-1">Total Produk</div>
                <div class="fw-semibold" data-summary-key="products">
                    <span class="skeleton skeleton--value w-25 mx-auto"></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="pane text-center py-2">
                <div class="small text-muted-pos mb-1">Total User</div>
                <div class="fw-semibold" data-summary-key="users">
                    <span class="skeleton skeleton--value w-25 mx-auto"></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="pane text-center py-2">
                <div class="small text-muted-pos mb-1">Kode Promo Aktif</div>
                <div class="fw-semibold" data-summary-key="promos">
                    <span class="skeleton skeleton--value w-25 mx-auto"></span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/admin-dashboard-loader.js'])
    @endif
@endsection
