@extends('layouts.admin')

@section('title', 'Laporan Harian — Admin')
@section('page_title', 'Laporan Harian')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <p class="text-muted-pos mb-0 small">
            {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
        </p>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-brand" id="btnCetakLaporan">
                <i class="bi bi-printer me-1"></i> Cetak / PDF
            </button>
            <a href="{{ route('admin.laporan-bulanan') }}" class="btn btn-outline-secondary">
                <i class="bi bi-calendar-month me-1"></i> Laporan Bulanan
            </a>
        </div>
    </div>

    <div class="pane mb-4">
        <form class="row g-2 align-items-end" action="{{ route('admin.laporan-harian') }}" method="get">
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
                <span class="badge badge-soft badge-soft--success fs-6 fw-semibold">
                    <i class="bi bi-cash-stack me-1"></i> {{ $fmt($totalPenjualan) }} hari ini
                </span>
            </div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        @foreach ($statCards as $stat)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card stat-card--{{ $stat['modifier'] }}">
                    <div>
                        <div class="stat-card__label">{{ $stat['label'] }}</div>
                        <div class="stat-card__value text-truncate" title="{{ $stat['value'] }}">{{ $stat['value'] }}</div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="bi {{ $stat['icon'] }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="pane h-100">
                <div class="pane__header">
                    <h2 class="pane__title h5">Penjualan per Metode Pembayaran</h2>
                    <span class="badge badge-soft badge-soft--neutral">{{ count($metodePembayaran) }} metode</span>
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
                        <tbody>
                            @foreach ($metodePembayaran as $metode)
                                @php $bagian = $totalMetode > 0 ? round(($metode['total'] / $totalMetode) * 100) : 0; @endphp
                                <tr>
                                    <td>
                                        <i class="bi {{ $metode['icon'] }} me-2"></i>{{ $metode['nama'] }}
                                    </td>
                                    <td class="text-nowrap">{{ $metode['trx'] }} trx</td>
                                    <td class="text-end fw-semibold text-nowrap">{{ $rp($metode['total']) }}</td>
                                    <td class="text-end">
                                        <span class="badge badge-soft badge-soft--{{ $metode['badge'] }}">{{ $bagian }}%</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-semibold">
                                <td class="border-top">Total</td>
                                <td class="border-top text-nowrap">{{ count($metodePembayaran) }} metode</td>
                                <td class="border-top text-end text-nowrap">{{ $rp($totalMetode) }}</td>
                                <td class="border-top text-end">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="pane h-100">
                <div class="pane__header">
                    <h2 class="pane__title h5">Rincian Transaksi</h2>
                    <span class="badge badge-soft badge-soft--success">{{ count($rincian) }} transaksi</span>
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
                            <tbody>
                                @foreach ($rincian as $trx)
                                    <tr>
                                        <td class="text-nowrap">{{ $trx['jam'] }}</td>
                                        <td class="font-monospace text-nowrap">{{ $trx['id'] }}</td>
                                        <td>
                                            <span class="badge badge-soft badge-soft--{{ $jenisBadge[$trx['jenis']] }}">
                                                {{ $trx['jenis'] }}
                                            </span>
                                        </td>
                                        <td class="text-nowrap">{{ $trx['metode'] }}</td>
                                        <td class="text-end fw-semibold text-nowrap">{{ $rp($trx['total']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-semibold">
                                    <td class="border-top" colspan="4">Total</td>
                                    <td class="border-top text-end text-nowrap">{{ $rp($totalRincian) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pane">
        <div class="pane__header">
            <h2 class="pane__title h5">Ringkasan Kesimpulan</h2>
            <span class="badge badge-soft badge-soft--neutral">{{ $tanggal }}</span>
        </div>

        <div class="row g-3">
            @foreach ($ringkasan as $ringkas)
                <div class="col-sm-12 col-md-4">
                    <div class="note-box h-100">
                        <i class="bi {{ $ringkas['icon'] }} note-box__icon"></i>
                        <span>
                            <span class="d-block small text-muted-pos">{{ $ringkas['label'] }}</span>
                            <span class="fw-semibold text-nowrap">{{ $ringkas['value'] }}</span>
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
