@extends('layouts.kasir')

@section('title', 'Detail Transaksi — Kasir')
@section('page_title', 'Detail Transaksi')

@section('content')
    @php
        $id = $id ?? Route::currentRouteParameter('id');
        $rp = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
        $metode = $isOnline ? 'Online → QRIS' : 'Tunai';
        $metodeBadge = $isOnline ? 'info' : 'neutral';
        $metode = match ($transaction->payment_method) {
            'qris' => 'QRIS',
            'ewallet' => 'E-Wallet',
            'debit' => 'Debit',
            'transfer' => 'Transfer',
            default => 'Tunai',
        };
    @endphp

    @if(isset($notFound))
        <div class="pane">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-x-circle"></i></span>
                <div class="empty-state__title">Transaksi tidak ditemukan</div>
                <div class="empty-state__text">ID transaksi {{ $id }} tidak ditemukan dalam sistem.</div>
                <a href="{{ route('kasir.riwayat') }}" class="btn btn-brand mt-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
                </a>
            </div>
        </div>
    @else
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <p class="text-muted-pos mb-0 small">Struk transaksi</p>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge badge-soft badge-soft--success fs-6 fw-semibold">
                    <span class="badge-soft__dot"></span>Sukses
                </span>
                <span class="badge badge-soft badge-soft--{{ $metodeBadge }} fs-6">
                    {{ $isOnline ? 'Online' : 'Kasir' }}
                </span>
                <span class="font-monospace fw-semibold text-muted-pos">{{ $id }}</span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('kasir.riwayat') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <button type="button" class="btn btn-brand" id="btnCetakStruk">
                <i class="bi bi-printer me-1"></i> Cetak Struk
            </button>
        </div>

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="pane pane--tight">
                    <div class="receipt">
                        <div class="receipt__title">POS &amp; Order</div>
                        <div class="receipt__store">
                            Jl. Contoh Raya No. 12, Jakarta<br>
                            WA 0812-XXXX-XXXX
                        </div>

                        <hr class="receipt-divider">

                        <div class="receipt-line receipt-line--label">
                            <span>No. Transaksi</span>
                            <span class="font-monospace">{{ $id }}</span>
                        </div>
                        <div class="receipt-line receipt-line--label">
                            <span>Tanggal</span>
                            <span>{{ $transaction->created_at->translatedFormat('d M Y H:i') }}</span>
                        </div>
                        <div class="receipt-line receipt-line--label">
                            <span>Kasir</span>
                            <span>{{ $kasir }}</span>
                        </div>
                        <div class="receipt-line receipt-line--label">
                            <span>Metode</span>
                            <span>{{ $metode }}</span>
                        </div>
                        <div class="receipt-line receipt-line--label">
                            <span>Jenis</span>
                            <span>{{ $isOnline ? 'Online' : 'Kasir' }}</span>
                        </div>
                        @if ($isOnline)
                            <div class="receipt-line receipt-line--label">
                                <span>Pelanggan</span>
                                <span>{{ $transaction->order?->customer_name }}</span>
                            </div>
                        @endif

                        <hr class="receipt-divider">

                        @foreach ($items as $item)
                            <div class="receipt-line">
                                <span>
                                    <span class="d-block">{{ $item['name'] }}</span>
                                    <span class="receipt-line--label d-block">{{ $item['qty'] }} × {{ number_format($item['price'], 0, ',', '.') }}</span>
                                </span>
                                <span class="text-nowrap">{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach

                        <hr class="receipt-divider">

                        <div class="receipt-line">
                            <span>Subtotal</span>
                            <span>{{ $rp($subtotal) }}</span>
                        </div>
                        @if ($discount > 0)
                            <div class="receipt-line">
                                <span>
                                    Diskon <span class="receipt-line--label">({{ $promo }} −10%)</span>
                                </span>
                                <span class="text-nowrap">−{{ $rp($discount) }}</span>
                            </div>
                        @endif
                        <div class="receipt-line receipt-line--total receipt-line--grand">
                            <span>Total</span>
                            <span>{{ $rp($total) }}</span>
                        </div>

                        <hr class="receipt-divider">

                        <div class="receipt__title">TERIMA KASIH</div>
                        <div class="receipt__store">Simpan struk ini sebagai bukti transaksi</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="pane">
                            <div class="pane__header">
                                <h2 class="pane__title h5">
                                    <i class="bi bi-bar-chart me-2"></i>Ringkasan
                                </h2>
                            </div>
                            <div class="row g-3">
                                @foreach ($stats as $s)
                                    <div class="col-6 col-md-3">
                                        <div class="stat-card stat-card--{{ $s['modifier'] }}">
                                            <div>
                                                <div class="stat-card__label">{{ $s['label'] }}</div>
                                                <div class="stat-card__value text-truncate" title="{{ $s['value'] }}">{{ $s['value'] }}</div>
                                            </div>
                                            <div class="stat-card__icon">
                                                <i class="bi {{ $s['icon'] }}"></i>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="pane">
                            <div class="pane__header">
                                <h2 class="pane__title h5">
                                    <i class="bi bi-receipt-cutoff me-2"></i>Detail Pesanan
                                </h2>
                                <span class="badge badge-soft badge-soft--{{ $isOnline ? 'info' : 'success' }}">
                                    {{ $isOnline ? 'Online' : 'Kasir' }}
                                </span>
                            </div>

                            <div class="table-wrap mb-3">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">Produk</th>
                                                <th scope="col">Qty</th>
                                                <th scope="col">Harga</th>
                                                <th scope="col" class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($items as $item)
                                                <tr>
                                                    <td>{{ $item['name'] }}</td>
                                                    <td class="text-nowrap">{{ $item['qty'] }}</td>
                                                    <td class="text-nowrap">{{ $rp($item['price']) }}</td>
                                                    <td class="text-end fw-semibold text-nowrap">{{ $rp($item['subtotal']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            @if ($isOnline)
                                <h6 class="text-uppercase small fw-semibold text-muted-pos mb-2">Info Pembeli</h6>
                                <div class="order-detail-list mb-1">
                                    @foreach ($buyer as $row)
                                        <div class="order-detail-list__row">
                                            <span class="order-detail-list__label">{{ $row['label'] }}</span>
                                            <span class="order-detail-list__value {{ ! empty($row['mono']) ? 'font-monospace' : '' }}">{{ $row['value'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="note-box note-box--info">
                                    <i class="bi bi-info-circle note-box__icon"></i>
                                    <span>Transaksi dibayar langsung di kasir.</span>
                                </div>
                            @endif

                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <a href="{{ route('kasir.riwayat') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-clock-history me-1"></i> Lihat Riwayat
                                </a>
                                <button type="button" class="btn btn-brand" id="btnCetakUlang">
                                    <i class="bi bi-printer me-1"></i> Cetak Ulang Struk
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        const cetakStruk = () => window.print();
        document.getElementById('btnCetakStruk')?.addEventListener('click', cetakStruk);
        document.getElementById('btnCetakUlang')?.addEventListener('click', cetakStruk);
    </script>
@endpush
