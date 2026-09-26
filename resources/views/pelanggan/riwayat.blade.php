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

    {{-- ================= FILTER STATUS ================= --}}
    <ul class="nav nav-pills flex-wrap gap-2 mb-4">
        <li class="nav-item">
            <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('pelanggan.pesanan-saya') }}">
                Semua
                <span class="badge badge-soft badge-soft--count ms-1 badge-soft--neutral">{{ $orders->total() }}</span>
            </a>
        </li>
        @php
            $statusFilterMap = ['menunggu' => 'pending', 'diproses' => 'processing', 'selesai' => 'completed', 'ditolak' => 'rejected'];
        @endphp
        @foreach ($statusFilterMap as $display => $dbValue)
            <li class="nav-item">
                <a class="nav-link {{ request('status') === $display ? 'active' : '' }}" href="{{ route('pelanggan.pesanan-saya', ['status' => $display]) }}">
                    {{ ucfirst($display) }}
                    <span class="badge badge-soft badge-soft--count ms-1 badge-soft--neutral">{{ $orders->where('status', $dbValue)->count() }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    {{-- ================= DAFTAR PESANAN ================= --}}
    @forelse ($orders as $order)
        @php
            $badge = $order->displayBadgeClass();
            $label = [
                'pending' => 'Menunggu Konfirmasi Kasir',
                'processing' => 'Diproses',
                'completed' => 'Selesai',
                'rejected' => 'Ditolak',
            ];
            $displayLabel = $order->displayLabel();
            $status = $order->status;
            $modalId = str_replace('-', '', $order->order_number);
        @endphp
        <div class="order-card">
            <div class="order-card__header">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="order-card__id">{{ $order->order_number }}</span>
                    <span class="badge badge-soft {{ $badge }}">
                        <span class="badge-soft__dot"></span>{{ $label[$status] }}
                    </span>
                </div>
                <span class="order-card__time text-nowrap">{{ $order->created_at->diffForHumans() }}</span>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <ul class="list-unstyled mb-0">
                        @foreach ($order->items as $item)
                            <li class="small text-muted-pos">
                                <span class="text-body fw-semibold">&middot; {{ $item->qty }}&times;</span>
                                {{ $item->product_name }}
                            </li>
                        @endforeach
                    </ul>
                    @if ($order->promoCode)
                        <div class="small order-summary__disc mt-2">
                            <i class="bi bi-tag me-1"></i>Promo {{ $order->promoCode->code }}
                            (&minus;{{ 'Rp ' . number_format($order->discount, 0, ',', '.') }})
                        </div>
                    @endif
                </div>
                <div class="text-md-end">
                    <span class="order-card__total">{{ 'Rp ' . number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="order-card__actions">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#detail{{ $modalId }}">
                    <i class="bi bi-eye me-1"></i> Detail
                </button>
                @if ($status === 'pending')
                    <form method="POST" action="{{ route('pelanggan.order.cancel', $order) }}" style="display:inline;" class="cancel-order-form">
                        @csrf @method('PUT')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Batalkan
                        </button>
                    </form>
                @endif
                @if ($status === 'completed')
                    <a href="{{ route('marketplace') }}" class="btn btn-brand btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i> Beli Lagi
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="pane text-center py-4">
            <p class="text-muted-pos">Tidak ada riwayat pesanan.</p>
            <a href="{{ route('marketplace') }}" class="btn btn-brand mt-2">Buat Pesanan Baru</a>
        </div>
    @endforelse

    {{-- ================= PAGINASI ================= --}}
    <div class="text-center mt-4 pt-3">
        {{ $orders->links() }}
    </div>

    {{-- ================= SHORTCUT ================= --}}
    <div class="text-center mt-4 pt-3">
        <small class="text-muted-pos d-block">Mau mencoba pesan lagi?</small>
        <a href="{{ route('marketplace') }}" class="fw-semibold">
            Buat Pesanan Baru &rarr;
        </a>
    </div>

    {{-- ================= MODAL DETAIL + BATALKAN ================= --}}
    @foreach ($orders as $order)
        @php
            $modalId = str_replace('-', '', $order->order_number);
            $badge = $order->displayBadgeClass();
            $label = [
                'pending' => 'Menunggu Konfirmasi Kasir',
                'processing' => 'Diproses',
                'completed' => 'Selesai',
                'rejected' => 'Ditolak',
            ];
            $status = $order->status;
        @endphp

        <div id="detail{{ $modalId }}" class="modal fade" tabindex="-1" aria-labelledby="detail{{ $modalId }}Label" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detail{{ $modalId }}Label">Detail Pesanan {{ $order->order_number }}</h5>
                        <span class="badge badge-soft {{ $badge }} ms-2">
                            <span class="badge-soft__dot"></span>{{ $label[$status] }}
                        </span>
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <div class="order-detail-list mb-4">
                            <div class="order-detail-list__row">
                                <span class="order-detail-list__label">ID Pesanan</span>
                                <span class="order-detail-list__value font-monospace">{{ $order->order_number }}</span>
                            </div>
                            <div class="order-detail-list__row">
                                <span class="order-detail-list__label">Tanggal</span>
                                <span class="order-detail-list__value">{{ $order->created_at->translatedFormat('d M Y H:i') }}</span>
                            </div>
                            <div class="order-detail-list__row">
                                <span class="order-detail-list__label">Penerima</span>
                                <span class="order-detail-list__value">{{ $order->customer_name }}</span>
                            </div>
                            <div class="order-detail-list__row">
                                <span class="order-detail-list__label">Metode</span>
                                <span class="order-detail-list__value">{{ ucfirst($order->payment_method) }}</span>
                            </div>
                            <div class="order-detail-list__row">
                                <span class="order-detail-list__label">Total</span>
                                <span class="order-detail-list__value">{{ 'Rp ' . number_format($order->total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="table-wrap mb-4">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Harga</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order->items as $item)
                                            <tr>
                                                <td>{{ $item->product_name }}</td>
                                                <td class="text-center">{{ $item->qty }}</td>
                                                <td class="text-end text-nowrap">{{ 'Rp ' . number_format($item->price, 0, ',', '.') }}</td>
                                                <td class="text-end text-nowrap fw-semibold">{{ 'Rp ' . number_format($item->price * $item->qty, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="order-summary">
                            <div class="order-summary__row">
                                <span>Subtotal ({{ $order->items->count() }} item)</span>
                                <span>{{ 'Rp ' . number_format($order->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if ($order->discount > 0)
                                <div class="order-summary__row">
                                    <span>Diskon — {{ $order->promoCode->code ?? '' }}</span>
                                    <span class="order-summary__disc">&minus;{{ 'Rp ' . number_format($order->discount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="order-summary__row order-summary__row--total">
                                <span>Total</span>
                                <span>{{ 'Rp ' . number_format($order->total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        @if ($status === 'pending')
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Batalkan Pesanan</button>
                        @elseif ($status === 'completed')
                            <a href="{{ route('marketplace') }}" class="btn btn-brand">
                                <i class="bi bi-arrow-repeat me-1"></i> Beli Lagi
                            </a>
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        @else
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.cancel-order-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
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
    });
</script>
@endpush
