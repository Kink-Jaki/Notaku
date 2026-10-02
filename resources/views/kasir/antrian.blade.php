@extends('layouts.kasir')

@section('title', 'Antrian Pesanan Masuk — Kasir')
@section('page_title', 'Antrian Pesanan Masuk')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <p class="text-muted-pos mb-0 small">Pesanan pelanggan yang menunggu konfirmasi kasir</p>
        <span class="badge badge-soft badge-soft--warning fs-6 fw-semibold" id="antrian-menunggu-badge">
            <i class="bi bi-hourglass-split me-1"></i> <span class="skeleton skeleton--badge"></span> pesanan menunggu
        </span>
    </div>

    <ul class="nav nav-pills gap-1 mb-4 flex-nowrap overflow-x-auto pb-1" id="antrian-tabs" aria-busy="true">
        @foreach (range(1, 4) as $tabIndex)
            <li class="nav-item flex-shrink-0 me-1">
                <span class="nav-link disabled"><span class="skeleton skeleton--text w-50"></span></span>
            </li>
        @endforeach
    </ul>

    <section class="pane pane--accent mb-4" id="antrian-pending-section" aria-labelledby="perluTinjauanTitle" aria-busy="true">
        <div class="pane__header">
            <h2 id="perluTinjauanTitle" class="pane__title h5">Perlu Tinjauan</h2>
            <span class="badge badge-soft badge-soft--warning" id="antrian-pending-count"><span class="skeleton skeleton--badge"></span></span>
        </div>
        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Waktu</th>
                            <th>Item</th>
                            <th>Total</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="antrian-pending-body">
                        @foreach (range(1, 4) as $rowIndex)
                            <tr class="skeleton-row">
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
    </section>

    <section aria-labelledby="riwayatTitle" id="antrian-handled-section" aria-busy="true">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 id="riwayatTitle" class="pane__title h5 mb-1">Riwayat Penanganan</h2>
                <p class="small text-muted-pos mb-0">Pesanan online yang sudah diputuskan kasir</p>
            </div>
            <span class="badge badge-soft badge-soft--neutral" id="antrian-handled-count"><span class="skeleton skeleton--badge"></span></span>
        </div>
        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Waktu</th>
                            <th>Item</th>
                            <th>Total</th>
                            <th>Keputusan</th>
                        </tr>
                    </thead>
                    <tbody id="antrian-handled-body">
                        @foreach (range(1, 4) as $rowIndex)
                            <tr class="skeleton-row">
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
    </section>

    <div id="antrian-modals" data-csrf="{{ csrf_token() }}"></div>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @push('scripts')
            @vite('resources/js/kasir-antrian-loader.js')
        @endpush
    @endif
@endsection
