@extends('layouts.admin')

@section('title', 'Laporan Harian — Admin')
@section('page_title', 'Laporan Harian')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <p class="text-muted-pos mb-0 small" data-rt-date>
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
        <form class="row g-2 align-items-end" action="{{ route('admin.laporan-harian') }}" method="get" data-realtime>
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
                    <i class="bi bi-cash-stack me-1"></i> {{ $rp($totalHariIni) }} hari ini
                </span>
            </div>
        </form>
    </div>

    <div data-rt-results>
        @include('admin.laporan-harian-results')
    </div>
@endsection

@push('scripts')
    <script>
        const cetakLaporanHarian = () => window.print();
        document.getElementById('btnCetakLaporan')?.addEventListener('click', cetakLaporanHarian);
    </script>
@endpush
