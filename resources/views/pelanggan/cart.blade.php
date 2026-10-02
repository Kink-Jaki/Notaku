@extends('layouts.pelanggan')

@section('title', 'Keranjang — Pelanggan')

@section('content')
    {{-- ================= HEADER ================= --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Keranjang Belanja</h1>
            <p class="small text-muted-pos mb-0"><span data-cart-item-count>{{ $jumlahItem }} item</span> — periksa kembali sebelum checkout.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('marketplace') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Lanjut Belanja
            </a>
            <button type="button" class="btn btn-brand checkout-empty-btn {{ $jumlahItem === 0 ? '' : 'd-none' }}">
                <i class="bi bi-bag-check me-1"></i> Lanjut Checkout
            </button>
            <a href="{{ route('pelanggan.checkout') }}" class="btn btn-brand {{ $jumlahItem === 0 ? 'd-none' : '' }}">
                <i class="bi bi-bag-check me-1"></i> Lanjut Checkout
            </a>
        </div>
    </div>

    {{-- ================= KERANJANG KOSONG ================= --}}
    <div class="pane {{ $jumlahItem === 0 ? '' : 'd-none' }}" data-cart-empty-state>
        <div class="empty-state">
            <div class="empty-state__icon"><i class="bi bi-cart-x"></i></div>
            <div class="empty-state__title">Keranjang kosong</div>
            <p class="empty-state__text">Belum ada menu di keranjang. Yuk pilih menu favoritmu dari katalog.</p>
            <a href="{{ route('marketplace') }}" class="btn btn-brand mt-3">
                <i class="bi bi-grid me-1"></i> Belanja Sekarang
            </a>
        </div>
    </div>

    <div class="row g-3 {{ $jumlahItem === 0 ? 'd-none' : '' }}" data-cart-body>
        {{-- ================= DAFTAR ITEM ================= --}}
        <div class="col-lg-8">
            <div class="pane">
                <div class="pane__header">
                    <h2 class="pane__title">Daftar Item</h2>
                    <span class="badge badge-soft badge-soft--neutral" data-cart-item-count>{{ $jumlahItem }} item</span>
                </div>

                <div class="table-wrap">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $productId => $item)
                                    @php
                                        $itemTotal = $item['line_total'] ?? ($item['price'] * $item['qty']);
                                        $productName = $item['product']?->name ?? $item['name'] ?? 'Produk';
                                        $productThumb = $item['product']?->image ?? null;
                                    @endphp
                                    <tr data-cart-row="{{ $productId }}">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                @if ($productThumb)
                                                    <img src="{{ asset('storage/' . $productThumb) }}" alt="{{ $productName }}" class="thumb-sm" style="max-width:40px;object-fit:cover;">
                                                @else
                                                    <span class="thumb-sm"><x-product-placeholder size="sm" :name="$productName" /></span>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold">{{ $productName }}</div>
                                                    <span class="badge badge-soft badge-soft--neutral mt-1">{{ $item['product']?->category?->name ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end text-nowrap">{{ 'Rp ' . number_format($item['price'], 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('pelanggan.cart.update') }}" class="d-flex align-items-center gap-1 cart-update-form" data-product-id="{{ $productId }}">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $productId }}">
                                                <input type="hidden" name="qty" value="{{ $item['qty'] }}" data-cart-qty-input>
                                                <button type="button" class="btn btn-outline-secondary btn-sm cart-qty-btn" data-cart-step="-1" {{ $item['qty'] <= 1 ? 'disabled' : '' }} aria-label="Kurangi">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                                <input type="text" class="form-control form-control-sm text-center input-qty" value="{{ $item['qty'] }}" readonly aria-label="Jumlah" data-cart-row-qty>
                                                <button type="button" class="btn btn-outline-secondary btn-sm cart-qty-btn" data-cart-step="1" aria-label="Tambah">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end fw-semibold text-nowrap" data-cart-row-subtotal>{{ 'Rp ' . number_format($itemTotal, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm cart-remove-btn" data-product-id="{{ $productId }}" data-product-name="{{ $productName }}" aria-label="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= RINGKASAN ================= --}}
        <div class="col-lg-4">
            <div class="pane">
                <div class="pane__header">
                    <h2 class="pane__title">Ringkasan</h2>
                </div>

                <div class="order-summary">
                    <div class="order-summary__row">
                        <span>Subtotal</span>
                        <span data-cart-summary-subtotal>{{ 'Rp ' . number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="order-summary__row {{ $diskon > 0 ? '' : 'd-none' }}" data-cart-discount-row>
                        <span>Diskon (<span data-promo-code>{{ $promoSession['code'] ?? '' }}</span>)</span>
                        <span data-cart-summary-discount>&#8722;{{ 'Rp ' . number_format($diskon, 0, ',', '.') }}</span>
                    </div>
                    <div class="order-summary__row order-summary__row--total">
                        <span>Total</span>
                        <span data-cart-summary-total>{{ 'Rp ' . number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <a href="{{ route('pelanggan.checkout') }}" class="btn btn-brand btn-lg w-100 mt-3">
                    <i class="bi bi-bag-check me-1"></i> Checkout
                </a>

                <div class="mt-2 w-100 {{ $diskon > 0 ? 'd-none' : '' }}" data-promo-apply>
                    <form method="POST" action="{{ route('pelanggan.cart.promo') }}" class="cart-promo-form w-100">
                        @csrf
                        <div class="input-group">
                            <input type="text" class="form-control" name="code" placeholder="Kode promo" aria-label="Kode promo">
                            <button type="submit" class="btn btn-outline-secondary">Terapkan</button>
                        </div>
                    </form>
                </div>

                <div class="mt-2 w-100 {{ $diskon > 0 ? '' : 'd-none' }}" data-promo-applied>
                    <form method="POST" action="{{ route('pelanggan.cart.promoRemove') }}" class="cart-promo-remove-form w-100">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-warning w-100">
                            <i class="bi bi-x-lg me-1"></i> Hapus Promo
                        </button>
                    </form>
                </div>

                <div class="note-box note-box--info mt-3">
                    <i class="bi bi-ticket-perforated note-box__icon"></i>
                    <span>Punya kode promo? Masukkan saat checkout.</span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.checkout-empty-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Keranjang Masih Kosong',
                        text: 'Tambahkan produk ke keranjang terlebih dahulu sebelum checkout.',
                        confirmButtonText: 'Belanja Sekarang',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route('marketplace') }}';
                        }
                    });
                });
            });
        });
    </script>
    @endpush
@endsection
