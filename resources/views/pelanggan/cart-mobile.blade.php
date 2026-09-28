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

    /* Toast notification for mobile */
    .cart-toast {
        position: fixed;
        bottom: 5.5rem;
        left: 1rem;
        right: 1rem;
        max-width: 320px;
        margin: 0 auto;
        z-index: 1100;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(1rem); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (min-width: 768px) {
        .cart-toast {
            bottom: 2rem;
            right: 2rem;
            left: auto;
            margin: 0;
        }
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
            <p class="small text-muted-pos mb-0">{{ $jumlahItem }} item</p>
        </div>
        <a href="{{ route('marketplace') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Belanja
        </a>
    </header>

    @if ($jumlahItem === 0)
        {{-- ================= EMPTY CART ================= --}}
        <div class="cart-empty-mobile">
            <div class="cart-empty-mobile__icon"><i class="bi bi-cart-x"></i></div>
            <div class="cart-empty-mobile__title">Keranjang kosong</div>
            <p class="cart-empty-mobile__text">Belum ada menu di keranjang. Yuk pilih menu favoritmu dari katalog.</p>
            <a href="{{ route('marketplace') }}" class="btn btn-brand cart-empty-mobile__btn">
                <i class="bi bi-grid me-1"></i> Mulai Belanja
            </a>
        </div>
    @else
        {{-- ================= ITEM LIST ================= --}}
        <div class="px-3 pb-5" id="cartItems">
            @foreach ($items as $productId => $item)
                @php
                    $itemTotal = $item['line_total'] ?? ($item['price'] * $item['qty']);
                    $productName = $item['product']?->name ?? $item['name'] ?? 'Produk';
                    $productThumb = $item['product']?->image ?? null;
                @endphp
                <article class="cart-item-mobile" data-product-id="{{ $productId }}">
                    <div class="cart-item-mobile__image">
                        @if ($productThumb)
                            <img src="{{ asset('storage/' . $productThumb) }}" alt="{{ $productName }}" loading="lazy" width="60" height="60">
                        @else
                            <x-product-placeholder size="sm" />
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
                                <button type="submit" class="cart-item-mobile__qty-btn" name="qty" value="{{ $item['qty'] - 1 }}" {{ $item['qty'] <= 1 ? 'disabled' : '' }} aria-label="Kurangi"><i class="bi bi-dash-lg"></i></button>
                                <input type="text" class="cart-item-mobile__qty-input" value="{{ $item['qty'] }}" readonly aria-label="Jumlah">
                                <button type="submit" class="cart-item-mobile__qty-btn" name="qty" value="{{ $item['qty'] + 1 }}" aria-label="Tambah"><i class="bi bi-plus-lg"></i></button>
                            </form>

                            <div class="cart-item-mobile__subtotal">{{ $rp($itemTotal) }}</div>

                            <button type="button" class="cart-item-mobile__remove cart-remove-btn-mobile" data-product-id="{{ $productId }}" data-product-name="{{ $productName }}" aria-label="Hapus {{ $productName }}">
                                <i class="bi bi-trash" style="font-size: 1.125rem;"></i>
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- ================= STICKY SUMMARY / CHECKOUT ================= --}}
        <footer class="cart-summary-mobile">
            <div class="cart-summary-mobile__row">
                <span>Subtotal</span>
                <span>{{ $rp($subtotal) }}</span>
            </div>
            @if ($diskon > 0)
                <div class="cart-summary-mobile__row cart-summary-mobile__row--discount">
                    <span>Diskon ({{ $promoSession['code'] ?? '' }})</span>
                    <span>−{{ $rp($diskon) }}</span>
                </div>
            @endif
            <div class="cart-summary-mobile__row cart-summary-mobile__row--total">
                <span>Total</span>
                <span>{{ $rp($total) }}</span>
            </div>

            <div class="cart-promo-mobile" id="promoSection">
                @if ($diskon > 0)
                    <div class="cart-promo-mobile__applied">
                        <span>Promo <strong>{{ $promoSession['code'] }}</strong> aktif — Diskon {{ $rp($diskon) }}</span>
                        <form method="POST" action="{{ route('pelanggan.cart.promoRemove') }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="cart-promo-mobile__remove"><i class="bi bi-x-lg me-1"></i> Hapus</button>
                        </form>
                    </div>
                @else
                    <form method="POST" action="{{ route('pelanggan.cart.promo') }}" class="cart-promo-mobile__input-group" id="promoForm">
                        @csrf
                        <input type="text" name="code" class="form-control cart-promo-mobile__input" placeholder="Kode promo" aria-label="Kode promo" autocomplete="off">
                        <button type="submit" class="btn btn-outline-primary cart-promo-mobile__btn">Terapkan</button>
                    </form>
                @endif
            </div>

            <a href="{{ route('pelanggan.checkout') }}" class="btn btn-brand btn-lg cart-summary-mobile__checkout-btn">
                <i class="bi bi-bag-check me-1"></i> Checkout ({{ $rp($total) }})
            </a>

            <p class="small text-muted-pos text-center mt-2 mb-0">Pembayaran di tempat saat pesanan siap diambil/antar</p>
        </footer>
    @endif

    {{-- Toast container --}}
    <div id="cartToast" class="cart-toast d-none" role="alert" aria-live="polite"></div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Qty update via fetch
        document.querySelectorAll('.cart-update-form-mobile').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const productId = form.querySelector('input[name="product_id"]').value;
                const btn = e.submitter;

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        showToast('Keranjang diperbarui', 'success');
                        // Update UI without reload for smooth UX
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showToast(data.error || 'Gagal memperbarui', 'error');
                    }
                }).catch(() => showToast('Terjadi kesalahan jaringan', 'error'));
            });
        });

        // Remove item
        document.querySelectorAll('.cart-remove-btn-mobile').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const productId = btn.dataset.productId;
                const productName = btn.dataset.productName;

                Swal.fire({
                    title: 'Hapus dari keranjang?',
                    text: productName + ' akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ef4444'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('{{ route('pelanggan.cart.remove') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({product_id: productId})
                        }).then(r => r.json()).then(data => {
                            if (data.success) {
                                showToast(productName + ' dihapus', 'success');
                                setTimeout(() => location.reload(), 500);
                            } else {
                                showToast(data.error || 'Gagal menghapus', 'error');
                            }
                        }).catch(() => showToast('Terjadi kesalahan jaringan', 'error'));
                    }
                });
            });
        });

        // Promo form submit
        const promoForm = document.getElementById('promoForm');
        if (promoForm) {
            promoForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = promoForm.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

                fetch(promoForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: new FormData(promoForm)
                }).then(r => r.json()).then(data => {
                    btn.disabled = false;
                    btn.textContent = 'Terapkan';
                    if (data.success) {
                        showToast('Promo diterapkan!', 'success');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showToast(data.error || 'Kode promo tidak valid', 'error');
                    }
                }).catch(() => {
                    btn.disabled = false;
                    btn.textContent = 'Terapkan';
                    showToast('Terjadi kesalahan jaringan', 'error');
                });
            });
        }

        // Toast helper
        function showToast(message, type = 'info') {
            const toast = document.getElementById('cartToast');
            const bgColor = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#0ea5e9');
            toast.innerHTML = `
                <div class="toast show" role="alert" style="background: ${bgColor}; color: white; border-radius: var(--pos-radius); padding: 0.75rem 1rem; box-shadow: var(--pos-shadow-md); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : (type === 'error' ? 'x-circle' : 'info-circle')}"></i>
                    <span>${message}</span>
                </div>
            `;
            toast.classList.remove('d-none');

            setTimeout(() => {
                toast.classList.add('d-none');
            }, 3000);
        }
    });
</script>
@endpush