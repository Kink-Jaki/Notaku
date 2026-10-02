@extends('layouts.pelanggan')

@section('title', 'Keranjang Mobile — Pelanggan')

@push('styles')
<style>
    /* Mobile-optimized cart styles */
    .cart-mobile-header {
        position: sticky;
        top: var(--pos-topbar-height);
        z-index: 100;
        background-color: var(--color-surface);
        border-bottom: 1px solid var(--color-border);
        padding: 1rem;
        margin: 0 -1rem 1rem;
    }

    @media (min-width: 768px) {
        .cart-mobile-header {
            position: static;
            margin: 0;
            padding: 0 0 1rem;
            border-bottom: none;
        }
    }

    .cart-item-mobile {
        display: flex;
        gap: 0.75rem;
        padding: 1rem;
        background-color: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--pos-radius);
        margin-bottom: 0.75rem;
    }

    .cart-item-mobile__image {
        flex: 0 0 60px;
        aspect-ratio: 1;
        border-radius: 0.5rem;
        overflow: hidden;
        background: linear-gradient(135deg, #eef2ff, #f5f6fa);
        display: grid;
        place-items: center;
        color: #c7d2fe;
        font-size: 1.5rem;
    }

    .cart-item-mobile__image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cart-item-mobile__content {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .cart-item-mobile__name {
        font-weight: 600;
        color: var(--color-text);
        margin-bottom: 0.25rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .cart-item-mobile__meta {
        font-size: 0.75rem;
        color: var(--color-text-muted);
        margin-bottom: 0.5rem;
    }

    .cart-item-mobile__price {
        font-weight: 600;
        color: var(--color-text);
    }

    .cart-item-mobile__qty {
        display: flex;
        align-items: center;
        gap: 0;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        overflow: hidden;
        max-width: 120px;
    }

    .cart-item-mobile__qty-btn {
        width: 36px;
        height: 36px;
        display: grid;
        place-items: center;
        background-color: var(--color-bg);
        border: none;
        color: var(--color-text);
        font-size: 1rem;
        line-height: 1;
    }

    .cart-item-mobile__qty-btn:active {
        background-color: var(--color-border);
    }

    .cart-item-mobile__qty-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .cart-item-mobile__qty-input {
        width: 48px;
        text-align: center;
        border: none;
        background: transparent;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--color-text);
        -moz-appearance: textfield;
    }

    .cart-item-mobile__qty-input::-webkit-outer-spin-button,
    .cart-item-mobile__qty-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .cart-item-mobile__subtotal {
        flex: 0 0 auto;
        font-weight: 700;
        color: var(--color-primary);
        font-size: 1rem;
        text-align: right;
        min-width: 70px;
    }

    .cart-item-mobile__remove {
        flex: 0 0 auto;
        width: 36px;
        height: 36px;
        display: grid;
        place-items: center;
        background-color: transparent;
        border: none;
        color: var(--color-text-muted);
        border-radius: 0.5rem;
    }

    .cart-item-mobile__remove:active {
        background-color: rgba(var(--color-danger-rgb), 0.1);
        color: var(--color-danger);
    }

    .cart-summary-mobile {
        position: sticky;
        bottom: 0;
        background-color: var(--color-surface);
        border-top: 1px solid var(--color-border);
        border-radius: var(--pos-radius) var(--pos-radius) 0 0;
        padding: 1rem;
        box-shadow: 0 -4px 12px rgba(15, 23, 42, 0.08);
    }

    @media (min-width: 768px) {
        .cart-summary-mobile {
            position: static;
            box-shadow: var(--pos-shadow-sm);
            border-radius: var(--pos-radius);
            border: 1px solid var(--color-border);
            border-top: none;
        }
    }

    .cart-summary-mobile__row {
        display: flex;
        justify-content: space-between;
        padding: 0.375rem 0;
        font-size: 0.875rem;
    }

    .cart-summary-mobile__row--total {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--color-text);
        border-top: 1px dashed var(--color-border);
        margin-top: 0.5rem;
        padding-top: 0.75rem;
    }

    .cart-summary-mobile__row--discount {
        color: var(--color-success);
    }

    .cart-summary-mobile__checkout-btn {
        width: 100%;
        padding: 1rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: var(--pos-radius);
        margin-top: 1rem;
    }

    .cart-promo-mobile {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--color-border);
    }

    .cart-promo-mobile__input-group {
        display: flex;
        gap: 0.5rem;
    }

    .cart-promo-mobile__input {
        flex: 1;
    }

    .cart-promo-mobile__btn {
        white-space: nowrap;
    }

    .cart-promo-mobile__applied {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 0.75rem;
        background-color: rgba(var(--color-success-rgb), 0.1);
        border-radius: var(--pos-radius);
        font-size: 0.8125rem;
        color: var(--color-success);
    }

    .cart-promo-mobile__remove {
        background: none;
        border: none;
        color: var(--color-success);
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }

    .cart-promo-mobile__remove:active {
        background-color: rgba(var(--color-success-rgb), 0.2);
    }

    .cart-empty-mobile {
        text-align: center;
        padding: 3rem 1rem;
    }

    .cart-empty-mobile__icon {
        font-size: 3.5rem;
        color: var(--color-border);
        margin-bottom: 1rem;
    }

    .cart-empty-mobile__title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--color-text);
        margin-bottom: 0.5rem;
    }

    .cart-empty-mobile__text {
        color: var(--color-text-muted);
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
    }

    .cart-empty-mobile__btn {
        padding: 0.875rem 2rem;
        font-size: 1rem;
    }
</style>
@endpush

@section('content')
    @php
        $rp = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
    @endphp

    {{-- ================= HEADER ================= --}}
    <header class="cart-mobile-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h1 class="h5 mb-0">Keranjang</h1>
            <p class="small text-muted-pos mb-0"><span data-cart-item-count>{{ $jumlahItem }} item</span></p>
        </div>
        <a href="{{ route('marketplace') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Belanja
        </a>
    </header>

    {{-- ================= EMPTY CART ================= --}}
    <div class="cart-empty-mobile {{ $jumlahItem === 0 ? '' : 'd-none' }}" data-cart-empty-state>
        <div class="cart-empty-mobile__icon"><i class="bi bi-cart-x"></i></div>
        <div class="cart-empty-mobile__title">Keranjang kosong</div>
        <p class="cart-empty-mobile__text">Belum ada menu di keranjang. Yuk pilih menu favoritmu dari katalog.</p>
        <a href="{{ route('marketplace') }}" class="btn btn-brand cart-empty-mobile__btn">
            <i class="bi bi-grid me-1"></i> Mulai Belanja
        </a>
    </div>

    {{-- ================= ITEM LIST ================= --}}
    <div class="px-3 pb-5 {{ $jumlahItem === 0 ? 'd-none' : '' }}" id="cartItems" data-cart-body>
            @foreach ($items as $productId => $item)
                @php
                    $itemTotal = $item['line_total'] ?? ($item['price'] * $item['qty']);
                    $productName = $item['product']?->name ?? $item['name'] ?? 'Produk';
                    $productThumb = $item['product']?->image ?? null;
                @endphp
                <article class="cart-item-mobile" data-cart-row="{{ $productId }}" data-product-id="{{ $productId }}">
                    <div class="cart-item-mobile__image">
                        @if ($productThumb)
                            <img src="{{ asset('storage/' . $productThumb) }}" alt="{{ $productName }}" loading="lazy" width="60" height="60">
                        @else
                            <x-product-placeholder size="sm" :name="$productName" />
                        @endif
                    </div>

                    <div class="cart-item-mobile__content">
                        <div>
                            <div class="cart-item-mobile__name">{{ $productName }}</div>
                            <div class="cart-item-mobile__meta">
                                {{ $item['product']?->category?->name ?? '-' }} · {{ $rp($item['price']) }}
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">
                            {{-- Qty Stepper --}}
                            <form method="POST" action="{{ route('pelanggan.cart.update') }}" class="cart-update-form-mobile d-flex align-items-center gap-0" data-product-id="{{ $productId }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $productId }}">
                                <input type="hidden" name="qty" value="{{ $item['qty'] }}" data-cart-qty-input>
                                <button type="button" class="cart-item-mobile__qty-btn" data-cart-step="-1" {{ $item['qty'] <= 1 ? 'disabled' : '' }} aria-label="Kurangi"><i class="bi bi-dash-lg"></i></button>
                                <input type="text" class="cart-item-mobile__qty-input" value="{{ $item['qty'] }}" readonly aria-label="Jumlah" data-cart-row-qty>
                                <button type="button" class="cart-item-mobile__qty-btn" data-cart-step="1" aria-label="Tambah"><i class="bi bi-plus-lg"></i></button>
                            </form>

                            <div class="cart-item-mobile__subtotal" data-cart-row-subtotal>{{ $rp($itemTotal) }}</div>

                            <button type="button" class="cart-item-mobile__remove cart-remove-btn-mobile" data-product-id="{{ $productId }}" data-product-name="{{ $productName }}" aria-label="Hapus {{ $productName }}">
                                <i class="bi bi-trash" style="font-size: 1.125rem;"></i>
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
    </div>

    {{-- ================= STICKY SUMMARY / CHECKOUT ================= --}}
    <footer class="cart-summary-mobile {{ $jumlahItem === 0 ? 'd-none' : '' }}" data-cart-body>
        <div class="cart-summary-mobile__row">
            <span>Subtotal</span>
            <span data-cart-summary-subtotal>{{ $rp($subtotal) }}</span>
        </div>
        <div class="cart-summary-mobile__row cart-summary-mobile__row--discount {{ $diskon > 0 ? '' : 'd-none' }}" data-cart-discount-row>
            <span>Diskon (<span data-promo-code>{{ $promoSession['code'] ?? '' }}</span>)</span>
            <span data-cart-summary-discount>−{{ $rp($diskon) }}</span>
        </div>
        <div class="cart-summary-mobile__row cart-summary-mobile__row--total">
            <span>Total</span>
            <span data-cart-summary-total>{{ $rp($total) }}</span>
        </div>

        <div class="cart-promo-mobile" id="promoSection">
            <div class="cart-promo-mobile__applied {{ $diskon > 0 ? '' : 'd-none' }}" data-promo-applied>
                <span>Promo <strong data-promo-code>{{ $promoSession['code'] ?? '' }}</strong> aktif — Diskon {{ $rp($diskon) }}</span>
                <form method="POST" action="{{ route('pelanggan.cart.promoRemove') }}" class="cart-promo-remove-form" style="display:inline;">
                    @csrf
                    <button type="submit" class="cart-promo-mobile__remove"><i class="bi bi-x-lg me-1"></i> Hapus</button>
                </form>
            </div>
            <form method="POST" action="{{ route('pelanggan.cart.promo') }}" class="cart-promo-form cart-promo-mobile__input-group {{ $diskon > 0 ? 'd-none' : '' }}" id="promoForm" data-promo-apply>
                @csrf
                <input type="text" name="code" class="form-control cart-promo-mobile__input" placeholder="Kode promo" aria-label="Kode promo" autocomplete="off">
                <button type="submit" class="btn btn-outline-primary cart-promo-mobile__btn">Terapkan</button>
            </form>
        </div>

        <a href="{{ route('pelanggan.checkout') }}" class="btn btn-brand btn-lg cart-summary-mobile__checkout-btn">
            <i class="bi bi-bag-check me-1"></i> Checkout (<span data-cart-checkout-total>{{ $rp($total) }}</span>)
        </a>

        <p class="small text-muted-pos text-center mt-2 mb-0">Pembayaran di tempat saat pesanan siap diambil/antar</p>
    </footer>
@endsection
