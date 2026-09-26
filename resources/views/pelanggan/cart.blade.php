@extends('layouts.pelanggan')

@section('title', 'Keranjang — Pelanggan')

@section('content')
    {{-- ================= HEADER ================= --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Keranjang Belanja</h1>
            <p class="small text-muted-pos mb-0">{{ $jumlahItem }} item — periksa kembali sebelum checkout.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('marketplace') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Lanjut Belanja
            </a>
            @if ($jumlahItem === 0)
                <button type="button" class="btn btn-brand checkout-empty-btn">
                    <i class="bi bi-bag-check me-1"></i> Lanjut Checkout
                </button>
            @else
                <a href="{{ route('pelanggan.checkout') }}" class="btn btn-brand">
                    <i class="bi bi-bag-check me-1"></i> Lanjut Checkout
                </a>
            @endif
        </div>
    </div>

    @if ($jumlahItem === 0)
        {{-- ================= KERANJANG KOSONG ================= --}}
        <div class="pane">
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-cart-x"></i></div>
                <div class="empty-state__title">Keranjang kosong</div>
                <p class="empty-state__text">Belum ada menu di keranjang. Yuk pilih menu favoritmu dari katalog.</p>
                <a href="{{ route('marketplace') }}" class="btn btn-brand mt-3">
                    <i class="bi bi-grid me-1"></i> Belanja Sekarang
                </a>
            </div>
        </div>
    @else
        <div class="row g-3">
            {{-- ================= DAFTAR ITEM ================= --}}
            <div class="col-lg-8">
                <div class="pane">
                    <div class="pane__header">
                        <h2 class="pane__title">Daftar Item</h2>
                        <span class="badge badge-soft badge-soft--neutral">{{ $jumlahItem }} item</span>
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
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    @if ($productThumb)
                                                        <img src="{{ asset('storage/' . $productThumb) }}" alt="{{ $productName }}" class="thumb-sm" style="max-width:40px;object-fit:cover;">
                                                    @else
                                                        <span class="thumb-sm"><i class="bi bi-box-seam"></i></span>
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
                                                <button type="submit" class="btn btn-outline-secondary btn-sm cart-qty-btn" name="qty" value="{{ $item['qty'] - 1 }}" {{ $item['qty'] <= 1 ? 'disabled' : '' }} aria-label="Kurangi">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                                <input type="text" class="form-control form-control-sm text-center input-qty" value="{{ $item['qty'] }}" readonly aria-label="Jumlah">
                                                <button type="submit" class="btn btn-outline-secondary btn-sm cart-qty-btn" name="qty" value="{{ $item['qty'] + 1 }}" aria-label="Tambah">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end fw-semibold text-nowrap">{{ 'Rp ' . number_format($itemTotal, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm cart-remove-btn" data-product-id="{{ $productId }}" data-product-name="{{ $productName }}" aria-label="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
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
                            <span>{{ 'Rp ' . number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if ($diskon > 0)
                            <div class="order-summary__row">
                                <span>Diskon ({{ $promoSession['code'] ?? '' }})</span>
                                <span>&#8722;{{ 'Rp ' . number_format($diskon, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="order-summary__row order-summary__row--total">
                            <span>Total</span>
                            <span>{{ 'Rp ' . number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <a href="{{ route('pelanggan.checkout') }}" class="btn btn-brand btn-lg w-100 mt-3">
                        <i class="bi bi-bag-check me-1"></i> Checkout
                    </a>

                    <form method="POST" action="{{ route('pelanggan.cart.promo') }}" style="display:inline;" class="mt-2 w-100">
                        @csrf
                        <div class="input-group">
                            <input type="text" class="form-control" name="code" placeholder="Kode promo" aria-label="Kode promo">
                            <button type="submit" class="btn btn-outline-secondary">Terapkan</button>
                        </div>
                    </form>

                    @if ($diskon > 0)
                        <form method="POST" action="{{ route('pelanggan.cart.promoRemove') }}" style="display:inline;" class="mt-2 w-100">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning w-100">
                                <i class="bi bi-x-lg me-1"></i> Hapus Promo
                            </button>
                        </form>
                    @endif

                    <div class="note-box note-box--info mt-3">
                        <i class="bi bi-ticket-perforated note-box__icon"></i>
                        <span>Punya kode promo? Masukkan saat checkout.</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

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

            document.querySelectorAll('.cart-update-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const productId = form.querySelector('input[name="product_id"]').value;
                    const qtyInput = form.querySelector('.cart-qty-btn[name="qty"]');
                    fetch(form.action, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
                        body: new FormData(form)
                    }).then(r => r.json()).then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            Swal.fire({icon: 'error', title: 'Gagal', text: data.error || 'Terjadi kesalahan'});
                        }
                    }).catch(() => {
                        Swal.fire({icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan jaringan'});
                    });
                });
            });

            document.querySelectorAll('.cart-remove-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const productId = btn.dataset.productId;
                    const productName = btn.dataset.productName;
                    Swal.fire({
                        title: 'Hapus dari keranjang?',
                        text: productName + ' akan dihapus.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('{{ route('pelanggan.cart.remove') }}', {
                                method: 'POST',
                                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
                                body: JSON.stringify({product_id: productId})
                            }).then(r => r.json()).then(data => {
                                if (data.success) {
                                    Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Item dihapus dari keranjang', showConfirmButton: false, timer: 2000});
                                    location.reload();
                                } else {
                                    Swal.fire({icon: 'error', title: 'Gagal', text: data.error || 'Terjadi kesalahan'});
                                }
                            }).catch(() => {
                                Swal.fire({icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan jaringan'});
                            });
                        }
                    });
                });
            });
        });
    </script>
    @endpush
@endsection