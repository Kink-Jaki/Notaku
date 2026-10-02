@extends('layouts.pelanggan')

@section('title', 'Checkout — Pelanggan')

@section('content')
    {{-- ================= HEADER + BREADCRUMB ================= --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('marketplace') }}">Katalog</a></li>
            @unless (request()->filled('product_id'))
                <li class="breadcrumb-item"><a href="{{ route('pelanggan.cart') }}">Keranjang</a></li>
            @endunless
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Checkout</h1>
            <p class="small text-muted-pos mb-0">Konfirmasi data pesanan sebelum dikirim ke kasir.</p>
        </div>
    </div>

    {{-- ================= PROGRESS ================= --}}
    <div class="pane mb-4">
        <div class="checkout-stepper" role="list" aria-label="Langkah pemesanan">
            <div class="checkout-step is-done" role="listitem">
                <span class="checkout-step__dot" aria-hidden="true"><i class="bi bi-check-lg"></i></span>
                <span class="checkout-step__label">{{ request()->filled('product_id') ? 'Beli Sekarang' : 'Keranjang' }}</span>
            </div>
            <div class="checkout-step is-active" role="listitem" aria-current="step">
                <span class="checkout-step__dot" aria-hidden="true">2</span>
                <span class="checkout-step__label">Checkout</span>
            </div>
            <div class="checkout-step" role="listitem">
                <span class="checkout-step__dot" aria-hidden="true">3</span>
                <span class="checkout-step__label">Menunggu Kasir</span>
            </div>
        </div>
    </div>

    {{-- Form promo harus di luar form checkout: <form> tidak boleh di-nest,
         karena </form> pertama akan menutup form utama dan membuat tombol
         submit berada di luar form. --}}
    <form method="POST" action="{{ route('pelanggan.cart.promo') }}" id="promoForm" hidden></form>
    <form method="POST" action="{{ route('pelanggan.cart.promoRemove') }}" id="promoRemoveForm" hidden></form>

    <form method="POST" action="{{ route('pelanggan.checkout.store') }}">
        @csrf
        @if (request()->filled('product_id'))
            <input type="hidden" name="product_id" value="{{ request('product_id') }}">
            <input type="hidden" name="qty" value="{{ request('qty', 1) }}">
        @endif
        <div class="row g-3">
            {{-- ================= KIRI ================= --}}
            <div class="col-lg-8">
                <div class="pane mb-3">
                    <div class="pane__header">
                        <h2 class="pane__title">Detail Pesanan / Penerima</h2>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="customer_name" class="form-label">Nama Penerima</label>
                            <input type="text" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" required placeholder="Nama lengkap">
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="customer_phone" class="form-label">No. WhatsApp</label>
                            <input type="tel" id="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="08xx-xxxx-xxxx">
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="delivery_type" class="form-label">Jenis Pengiriman</label>
                            <select class="form-select @error('delivery_type') is-invalid @enderror" id="delivery_type" name="delivery_type" required>
                                <option value="pickup" {{ old('delivery_type', 'pickup') === 'pickup' ? 'selected' : '' }}>Ambil di Tempat</option>
                                <option value="delivery" {{ old('delivery_type') === 'delivery' ? 'selected' : '' }}>Antar</option>
                            </select>
                            @error('delivery_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @if (old('delivery_type') === 'delivery' || request('delivery_type') === 'delivery')
                            <div class="col-12">
                                <label for="address" class="form-label">Alamat Pengiriman</label>
                                <textarea id="address" class="form-control @error('address') is-invalid @enderror" name="address" rows="2" placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                        <div class="col-12">
                            <label for="note" class="form-label">Catatan</label>
                            <textarea id="note" class="form-control @error('note') is-invalid @enderror" name="note" rows="2" placeholder="Mis. level pedas, tambah saus, dst.">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label d-block">Metode Pembayaran</label>
                            @php
                                $deliveryType = old('delivery_type', 'pickup');
                                $canTunai = $deliveryType === 'pickup';
                            @endphp

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="payment_method" id="metodeOnline" value="online" {{ old('payment_method', 'online') === 'online' ? 'checked' : '' }}>
                                <label class="form-check-label" for="metodeOnline"><i class="bi bi-credit-card me-1"></i> Bayar Online (QRIS/VA/e-Wallet)</label>
                            </div>
                            <div class="form-check form-check-inline {{ ! $canTunai ? 'd-none' : '' }}">
                                <input class="form-check-input" type="radio" name="payment_method" id="metodeTunai" value="tunai" {{ old('payment_method') === 'tunai' ? 'checked' : '' }} {{ ! $canTunai ? 'disabled' : '' }}>
                                <label class="form-check-label {{ ! $canTunai ? 'text-muted' : '' }}" for="metodeTunai"><i class="bi bi-cash me-1"></i> Tunai {{ ! $canTunai ? '(hanya pickup)' : '' }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="payment_method" id="metodeTransfer" value="transfer" {{ old('payment_method') === 'transfer' ? 'checked' : '' }}>
                                <label class="form-check-label" for="metodeTransfer"><i class="bi bi-building me-1"></i> Transfer Manual</label>
                            </div>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted d-block mt-1">
                                <span id="tunaiHint" class="{{ ! $canTunai ? 'text-danger' : 'text-muted' }}">{{ ! $canTunai ? 'Pembayaran tunai hanya tersedia untuk Ambil di Tempat (pickup).' : '' }}</span>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="pane">
                    <div class="pane__header">
                        <h2 class="pane__title">Rincian Item</h2>
                        <span class="badge badge-soft badge-soft--neutral">{{ $jumlahItem }} item</span>
                    </div>

                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cart as $productId => $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    @if ($item['image'] ?? null)
                                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="thumb-sm" style="max-width:40px;object-fit:cover;">
                                                    @else
                                                        <span class="thumb-sm"><x-product-placeholder size="sm" :name="$item['name']" /></span>
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold">{{ $item['name'] }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $item['qty'] }} &times;</td>
                                            <td class="text-end fw-semibold text-nowrap">{{ 'Rp ' . number_format($item['price'] * $item['qty'], 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= KANAN ================= --}}
            <div class="col-lg-4">
                <div class="pane">
                    <div class="pane__header">
                        <h2 class="pane__title">Ringkasan Order</h2>
                    </div>

                    <div class="order-summary">
                        <div class="order-summary__row">
                            <span>Subtotal</span>
                            <span>{{ 'Rp ' . number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if ($diskon > 0)
                            <div class="order-summary__row">
                                <span>Diskon ({{ $promoSession['code'] ?? '' }})</span>
                                <span class="order-summary__disc">&#8722;{{ 'Rp ' . number_format($diskon, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="order-summary__row order-summary__row--total">
                            <span>Total</span>
                            <span>{{ 'Rp ' . number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="kodePromo" class="form-label">Kode Promo</label>
                        <div class="input-group mb-2">
                            <input type="text" id="kodePromo" class="form-control text-uppercase" name="code" form="promoForm" placeholder="Masukkan kode promo">
                            <button type="submit" class="btn btn-outline-primary" form="promoForm">Terapkan</button>
                        </div>
                        @if ($diskon > 0)
                            <div class="alert alert-success alert-dismissible fade show mt-2 mb-2" role="alert">
                                Promo <strong>{{ $promoSession['code'] ?? '' }}</strong> diterapkan! Diskon: {{ 'Rp ' . number_format($diskon, 0, ',', '.') }}
                                <button type="submit" class="btn-close" form="promoRemoveForm" data-bs-dismiss="alert" aria-label="Hapus promo"></button>
                            </div>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-brand btn-lg w-100 mt-3">
                        <i class="bi bi-send me-1"></i> Submit Pesanan
                    </button>
</div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deliverySelect = document.getElementById('delivery_type');
        const tunaiRadio = document.getElementById('metodeTunai');
        const tunaiLabel = tunaiRadio ? tunaiRadio.closest('.form-check-inline') : null;
        const tunaiHint = document.getElementById('tunaiHint');

        // If critical elements don't exist, bail out
        if (!deliverySelect || !tunaiRadio) {
            return;
        }

        function updateTunai() {
            const isPickup = deliverySelect.value === 'pickup';
            if (tunaiLabel) tunaiLabel.classList.toggle('d-none', !isPickup);
            if (tunaiRadio) tunaiRadio.disabled = !isPickup;
            if (tunaiHint) {
                tunaiHint.classList.toggle('text-danger', !isPickup);
                tunaiHint.classList.toggle('text-muted', isPickup);
                tunaiHint.textContent = isPickup ? '' : 'Pembayaran tunai hanya tersedia untuk Ambil di Tempat (pickup).';
            }

            if (!isPickup && tunaiRadio && tunaiRadio.checked) {
                const onlineRadio = document.getElementById('metodeOnline');
                if (onlineRadio) onlineRadio.checked = true;
            }
        }

        deliverySelect.addEventListener('change', updateTunai);
        updateTunai();
    });
</script>
@endpush
@endsection