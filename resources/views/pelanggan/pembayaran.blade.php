@extends('layouts.pelanggan')

@section('title', 'Status Pembayaran — Pelanggan')

@section('content')
    {{-- ================= HEADER ================= --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('marketplace') }}">Katalog</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pelanggan.pesanan-saya') }}">Pesanan Saya</a></li>
            <li class="breadcrumb-item active" aria-current="page">Status Pembayaran</li>
        </ol>
    </nav>

    <div class="pane text-center py-5">
        <div class="status-icon mb-3">
            @if ($order->status === 'unpaid')
                <div class="spinner-border text-warning" role="status" style="width: 4rem; height: 4rem;">
                    <span class="visually-hidden">Memproses...</span>
                </div>
            @elseif ($order->status === 'expired')
                <i class="bi bi-x-circle text-danger" style="font-size: 4rem;"></i>
            @endif
        </div>

        <h2 class="h4 mb-2">{{ $order->order_number }}</h2>

        @if ($order->status === 'unpaid')
            <p class="text-muted-pos mb-3">Menunggu pembayaran. Silakan selesaikan pembayaran di halaman Xendit.</p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                <a href="{{ $order->xendit_invoice_id ? route('pelanggan.pembayaran.bayar', $order) : route('marketplace') }}" class="btn btn-brand btn-lg" target="_blank" rel="noopener">
                    <i class="bi bi-credit-card me-1"></i> Bayar Sekarang
                </a>
                <button type="button" class="btn btn-outline-secondary btn-lg" id="checkStatusBtn">
                    <i class="bi bi-arrow-clockwise me-1"></i> Cek Status
                </button>
            </div>
            <p class="small text-muted-pos mt-3">
                Jika sudah membayar, klik <strong>Cek Status</strong> atau tunggu notifikasi.
            </p>
        @elseif ($order->status === 'expired')
            <p class="text-danger fw-semibold mb-3">Pesanan Kadaluarsa</p>
            <p class="text-muted-pos mb-3">Waktu pembayaran telah habis. Silakan pesan ulang jika masih menginginkan item ini.</p>
            <a href="{{ route('pelanggan.pesanan.pesan-lagi', $order) }}" class="btn btn-outline-primary btn-lg">
                <i class="bi bi-arrow-repeat me-1"></i> Pesan Lagi
            </a>
        @endif

        <div class="mt-4">
            <a href="{{ route('pelanggan.pesanan-saya') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
            </a>
        </div>
    </div>

    {{-- ================= SCRIPTS ================= --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkBtn = document.getElementById('checkStatusBtn');
            if (! checkBtn) return;

            checkBtn.addEventListener('click', function () {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Mengecek...';

                fetch(window.location.href, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(r => {
                    if (! r.ok) throw new Error('Network response was not ok');
                    return r.json();
                }).then(data => {
                    if (data.status === 'pending') {
                        window.location.reload();
                    } else if (data.status === 'expired') {
                        window.location.reload();
                    } else {
                        btn.disabled = false;
                        btn.textContent = 'Cek Status';
                    }
                }).catch(() => {
                    btn.disabled = false;
                    btn.textContent = 'Cek Status';
                });
            });

            // Auto-check every 15 seconds while unpaid
            @if ($order->status === 'unpaid')
            setInterval(function () {
                if (document.hidden) return; // don't poll when tab hidden
                fetch(window.location.href, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(r => r.json()).then(data => {
                    if (data.status !== 'unpaid') {
                        window.location.reload();
                    }
                }).catch(() => {});
            }, 15000);
            @endif
        });
    </script>
    @endpush
@endsection