@extends('layouts.kasir')

@section('title', 'Laporan Bulanan — Kasir')
@section('page_title', 'Laporan Bulanan')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <p class="text-muted-pos mb-0 small">Laporan rekap penjualan per bulan (PDF)</p>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge badge-soft badge-soft--success fs-6 fw-semibold">
                <i class="bi bi-check-circle me-1"></i> Rekap <span data-rt-bulan>{{ $labelBulan }}</span>
            </span>
        </div>
    </div>

    <div class="pane mb-4">
        <form action="{{ route('kasir.laporan-bulanan') }}" method="get" data-realtime>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                <div class="d-flex flex-wrap align-items-end gap-2">
                    <div>
                        <label class="form-label" for="bulanLaporan">Bulan</label>
                        <select class="form-select" id="bulanLaporan" name="bulan">
                            @foreach ($daftarBulan as $kode => $nama)
                                <option value="{{ $kode }}" @selected($kode === $bulan)>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="tahunLaporan">Tahun</label>
                        <select class="form-select" id="tahunLaporan" name="tahun">
                            @foreach ($daftarTahun as $tahunOpt)
                                <option value="{{ $tahunOpt }}" @selected((string) $tahunOpt === $tahun)>{{ $tahunOpt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
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
                    <button type="submit" class="btn btn-brand">
                        <i class="bi bi-funnel me-1"></i> Tampilkan
                    </button>
                </div>
                <div class="text-end">
                    <a href="{{ route('kasir.laporan-bulanan.export', ['bulan' => $bulan, 'tahun' => $tahun, 'kasir_id' => $kasirId]) }}" class="btn btn-brand text-uppercase" data-rt-href>
                        <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div data-rt-results>
        @include('kasir.laporan-bulanan-results')
    </div>
@endsection
