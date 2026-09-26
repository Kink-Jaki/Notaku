@extends('layouts.kasir')

@section('title', 'Laporan Harian — Kasir')
@section('page_title', 'Laporan Harian')

@section('content')
    <noscript>
        <div class="alert alert-warning">
            JavaScript nonaktif, laporan tidak dapat dimuat. Aktifkan JavaScript lalu muat ulang halaman.
        </div>
    </noscript>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <p class="text-muted-pos mb-0 small" id="lh-tanggal-label" aria-busy="true">
            <span class="skeleton skeleton--text" style="width: 14rem"></span>
        </p>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-brand" id="btnCetakLaporan">
                <i class="bi bi-printer me-1"></i> Cetak / PDF
            </button>
            <a href="{{ route('kasir.laporan-bulanan') }}" class="btn btn-outline-secondary">
                <i class="bi bi-calendar-month me-1"></i> Laporan Bulanan
            </a>
        </div>
    </div>

    <div class="pane mb-4">
        <form class="row g-2 align-items-end" id="lh-filter" action="{{ route('kasir.laporan-harian') }}" method="get">
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label" for="tanggalLaporan">Tanggal</label>
                <input type="date" class="form-control" id="tanggalLaporan" name="tanggal" value="{{ $tanggal }}">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label" for="kasirFilter">Kasir</label>
                <select class="form-select" id="kasirFilter" name="kasir_id">
                    <option value="">Semua Kasir</option>
                    @foreach ($kasirList as $kasir)
                        <option value="{{ $kasir->id }}" {{ $kasirId == $kasir->id ? 'selected' : '' }}>
                            {{ $kasir->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-brand">
                    <i class="bi bi-search me-1"></i> Tampilkan
                </button>
            </div>
            <div class="col-12 col-md-auto ms-md-auto">
                <span id="lh-badge-penjualan" aria-busy="true">
                    <span class="skeleton skeleton--badge"></span>
                </span>
            </div>
        </form>
    </div>

    {{-- Ringkasan (skeleton, diisi JS) --}}
    <div class="row g-3 mb-4" id="lh-stats" aria-busy="true">
        @foreach (range(1, 4) as $statIndex)
            <div class="col-12 col-sm-6 col-xl-3">
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

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="pane h-100" id="lh-metode-pane" aria-busy="true">
                <div class="pane__header">
                    <h2 class="pane__title h5">Penjualan per Metode Pembayaran</h2>
                    <span id="lh-metode-count" aria-busy="true">
                        <span class="skeleton skeleton--badge"></span>
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Metode</th>
                                <th class="text-nowrap">Transaksi</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Bagian</th>
                            </tr>
                        </thead>
                        <tbody id="lh-metode-body" aria-busy="true">
                            @foreach (range(1, 4) as $metodeIndex)
                                <tr>
                                    <td><span class="skeleton skeleton--text w-75"></span></td>
                                    <td><span class="skeleton skeleton--text w-50"></span></td>
                                    <td class="text-end"><span class="skeleton skeleton--text w-75 ms-auto"></span></td>
                                    <td class="text-end"><span class="skeleton skeleton--text w-25 ms-auto"></span></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot id="lh-metode-foot" aria-busy="true">
                            <tr class="fw-semibold">
                                <td class="border-top"><span class="skeleton skeleton--text w-50"></span></td>
                                <td class="border-top"><span class="skeleton skeleton--text w-50"></span></td>
                                <td class="border-top text-end"><span class="skeleton skeleton--text w-50 ms-auto"></span></td>
                                <td class="border-top text-end"><span class="skeleton skeleton--text w-25 ms-auto"></span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="pane h-100" id="lh-rincian-pane" aria-busy="true">
                <div class="pane__header">
                    <h2 class="pane__title h5">Rincian Transaksi</h2>
                    <span id="lh-rincian-count" aria-busy="true">
                        <span class="skeleton skeleton--badge"></span>
                    </span>
                </div>

                <div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Jam</th>
                                    <th>ID</th>
                                    <th>Jenis</th>
                                    <th>Metode</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="lh-rincian-body" aria-busy="true">
                                @foreach (range(1, 5) as $rincianIndex)
                                    <tr>
                                        <td><span class="skeleton skeleton--text w-50"></span></td>
                                        <td><span class="skeleton skeleton--text w-75"></span></td>
                                        <td><span class="skeleton skeleton--text w-50"></span></td>
                                        <td><span class="skeleton skeleton--text w-75"></span></td>
                                        <td class="text-end"><span class="skeleton skeleton--text w-50 ms-auto"></span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot id="lh-rincian-foot" aria-busy="true">
                                <tr class="fw-semibold">
                                    <td class="border-top" colspan="4"><span class="skeleton skeleton--text w-50"></span></td>
                                    <td class="border-top text-end"><span class="skeleton skeleton--text w-50 ms-auto"></span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pane" id="lh-ringkasan-pane" aria-busy="true">
        <div class="pane__header">
            <h2 class="pane__title h5">Ringkasan Kesimpulan</h2>
            <span id="lh-ringkasan-badge" aria-busy="true">
                <span class="skeleton skeleton--badge"></span>
            </span>
        </div>

        <div class="row g-3" id="lh-ringkasan-body" aria-busy="true">
            @foreach (range(1, 3) as $ringkasIndex)
                <div class="col-sm-12 col-md-4">
                    <div class="note-box h-100">
                        <span class="skeleton" style="flex: none; width: 1.5rem; height: 1.5rem"></span>
                        <span class="w-100">
                            <span class="d-block mb-1"><span class="skeleton skeleton--text w-50"></span></span>
                            <span class="d-block"><span class="skeleton skeleton--text w-75"></span></span>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const cetakLaporanHarian = () => window.print();
        document.getElementById('btnCetakLaporan')?.addEventListener('click', cetakLaporanHarian);
    </script>
@endpush

@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @push('scripts')
        @vite('resources/js/laporan-harian-loader.js')
    @endpush
@endif
