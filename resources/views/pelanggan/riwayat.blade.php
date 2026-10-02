@extends('layouts.pelanggan')

@section('title', 'Riwayat Pesanan — Pelanggan')

@section('content')
    {{-- ================= HEADER ================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Riwayat Pesanan</h1>
            <p class="small text-muted-pos mb-0">Pesanan menunggu konfirmasi kasir akan masuk sebagai transaksi resmi setelah disetujui</p>
        </div>
        <a href="{{ route('marketplace') }}" class="btn btn-brand">
            <i class="bi bi-plus-circle me-1"></i> Buat Pesanan Baru
        </a>
    </div>

    {{-- ================= FILTER + DAFTAR + PAGINASI ================= --}}
    <div data-rt-results data-cf="riwayat">
        @include('pelanggan.riwayat-results')
    </div>

    {{-- ================= SHORTCUT ================= --}}
    <div class="text-center mt-4 pt-3">
        <small class="text-muted-pos d-block">Mau mencoba pesan lagi?</small>
        <a href="{{ route('marketplace') }}" class="fw-semibold">
            Buat Pesanan Baru &rarr;
        </a>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('submit', function (event) {
            const form = event.target.closest('.cancel-order-form');

            if (! form || event.defaultPrevented) {
                return;
            }

            event.preventDefault();

            Swal.fire({
                title: 'Batalkan pesanan ini?',
                text: 'Pesanan yang dibatalkan tidak dapat diproses kembali.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, batalkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
