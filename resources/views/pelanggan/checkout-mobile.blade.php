@extends('layouts.pelanggan')

@section('title', 'Checkout Mobile — Pelanggan')

@push('styles')
<style>
    /* Mobile-optimized checkout styles */
    .checkout-mobile-header {
        position: sticky;
        top: var(--pos-topbar-height);
        z-index: 100;
        background-color: var(--color-surface);
        border-bottom: 1px solid var(--color-border);
        padding: 1rem;
        margin: 0 -1rem 1rem;
    }

    @media (min-width: 768px) {
        .checkout-mobile-header {
            position: static;
            margin: 0 0 1.5rem;
            padding: 0;
            border-bottom: none;
        }
    }

    .checkout-stepper-mobile {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.5rem 0;
        margin: 0 -1rem 1rem;
        background-color: var(--color-surface);
        border-bottom: 1px solid var(--color-border);
    }

    @media (min-width: 768px) {
        .checkout-stepper-mobile {
            margin: 0 0 1.5rem;
            padding: 0;
            background: transparent;
            border: none;
        }
    }

    .checkout-step-mobile {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.375rem;
        color: var(--color-text-muted);
        font-size: 0.6875rem;
        font-weight: 500;
    }

    .checkout-step-mobile__dot {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: var(--color-surface);
        border: 2px solid var(--color-border);
        color: var(--color-text-muted);
        display: grid;
        place-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .checkout-step-mobile:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: calc(100% - 28px);
        height: 2px;
        background-color: var(--color-border);
        transform: translateY(-50%);
        z-index: -1;
    }

    .checkout-step-mobile.is-done .checkout-step-mobile__dot {
        border-color: var(--color-success);
        background-color: var(--color-success);
        color: #ffffff;
    }

    .checkout-step-mobile.is-done:not(:last-child)::after {
        background-color: var(--color-success);
    }

    .checkout-step-mobile.is-active .checkout-step-mobile__dot {
        border-color: var(--color-primary);
        background-color: var(--color-primary-soft);
        color: var(--color-primary);
    }

    .checkout-step-mobile.is-active .checkout-step-mobile__label {
        color: var(--color-primary);
        font-weight: 600;
    }

    .checkout-step-mobile__label {
        white-space: nowrap;
    }

    .checkout-pane-mobile {
        background-color: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--pos-radius);
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .checkout-pane-mobile__title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--color-text);
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--color-border);
    }

    .checkout-form-group {
        margin-bottom: 1rem;
    }

    .checkout-form-group:last-child {
        margin-bottom: 0;
    }

    .checkout-label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--color-text);
        margin-bottom: 0.375rem;
    }

    .checkout-input,
    .checkout-select,
    .checkout-textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--color-border);
        border-radius: var(--pos-radius);
        background-color: var(--color-surface);
        color: var(--color-text);
        font-size: 1rem; /* Prevent zoom on iOS */
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .checkout-input:focus,
    .checkout-select:focus,
    .checkout-textarea:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 0.25rem rgba(var(--color-primary-rgb), 0.15);
    }

    .checkout-input.is-invalid,
    .checkout-select.is-invalid,
    .checkout-textarea.is-invalid {
        border-color: var(--color-danger);
    }

    .checkout-textarea {
        min-height: 80px;
        resize: vertical;
    }

    .checkout-error {
        display: block;
        font-size: 0.75rem;
        color: var(--color-danger);
        margin-top: 0.375rem;
    }

    .checkout-payment-options {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }

    .checkout-payment-option {
        border: 2px solid var(--color-border);
        border-radius: var(--pos-radius);
        padding: 0.75rem 0.5rem;
        cursor: pointer;
        transition: all 0.15s ease;
        text-align: center;
    }

    .checkout-payment-option:hover {
        border-color: var(--color-primary);
    }

    .checkout-payment-option:has(input:checked) {
        border-color: var(--color-primary);
        background-color: var(--color-primary-soft);
    }

    .checkout-payment-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .checkout-payment-option__icon {
        font-size: 1.5rem;
        color: var(--color-text-muted);
        margin-bottom: 0.25rem;
    }

    .checkout-payment-option:has(input:checked) .checkout-payment-option__icon {
        color: var(--color-primary);
    }

    .checkout-payment-option__label {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--color-text);
    }

    .checkout-order-summary {
        position: sticky;
        bottom: 0;
        background-color: var(--color-surface);
        border-top: 1px solid var(--color-border);
        border-radius: var(--pos-radius) var(--pos-radius) 0 0;
        padding: 1rem;
        box-shadow: 0 -4px 12px rgba(15, 23, 42, 0.08);
    }

    @media (min-width: 768px) {
        .checkout-order-summary {
            position: static;
            box-shadow: var(--pos-shadow-sm);
            border-radius: var(--pos-radius);
            border: 1px solid var(--color-border);
            border-top: none;
        }
    }

    .checkout-summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.375rem 0;
        font-size: 0.875rem;
    }

    .checkout-summary-row--total {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--color-text);
        border-top: 1px dashed var(--color-border);
        margin-top: 0.5rem;
        padding-top: 0.75rem;
    }

    .checkout-summary-row--discount {
        color: var(--color-success);
    }

    .checkout-submit-btn {
        width: 100%;
        padding: 1rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: var(--pos-radius);
        margin-top: 1rem;
    }

    .checkout-promo-mobile {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--color-border);
    }

    .checkout-promo-mobile__input-group {
        display: flex;
        gap: 0.5rem;
    }

    .checkout-promo-mobile__input {
        flex: 1;
    }

    .checkout-promo-mobile__btn {
        white-space: nowrap;
    }

    .checkout-promo-mobile__applied {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 0.75rem;
        background-color: rgba(var(--color-success-rgb), 0.1);
        border-radius: var(--pos-radius);
        font-size: 0.8125rem;
        color: var(--color-success);
    }

    .checkout-promo-mobile__remove {
        background: none;
        border: none;
        color: var(--color-success);
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }

    .checkout-order-items {
        max-height: 200px;
        overflow-y: auto;
        margin: 0 -1rem;
        padding: 0 1rem;
    }

    .checkout-order-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px dashed var(--color-border);
    }

    .checkout-order-item:last-child {
        border-bottom: none;
    }

    .checkout-order-item__info {
        flex: 1;
        min-width: 0;
    }

    .checkout-order-item__name {
        font-weight: 500;
        font-size: 0.875rem;
        color: var(--color-text);
    }

    .checkout-order-item__qty {
        font-size: 0.75rem;
        color: var(--color-text-muted);
    }

    .checkout-order-item__price {
        font-weight: 600;
        color: var(--color-text);
        white-space: nowrap;
        margin-left: 0.75rem;
    }

    .checkout-note {
        font-size: 0.75rem;
        color: var(--color-text-muted);
        text-align: center;
        margin-top: 1rem;
    }

    /* Skeleton for loading state */
    .skeleton-checkout {
        background: linear-gradient(90deg, var(--color-border) 25%, #f1f5f9 50%, var(--color-border) 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.2s ease-in-out infinite;
        border-radius: var(--pos-radius);
    }

    .skeleton-checkout__line {
        height: 1rem;
        margin-bottom: 0.75rem;
    }

    .skeleton-checkout__line--short {
        width: 60%;
    }

    @keyframes skeleton-loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Touch feedback */
    .checkout-payment-option:active {
        transform: scale(0.98);
        transition: transform 0.05s ease;
    }
</style>
@endpush

@section('content')
    @php
        $rp = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
        $steps = [
            ['label' => request()->filled('product_id') ? 'Beli Sekarang' : 'Keranjang', 'icon' => 'bi-cart3'],
            ['label' => 'Checkout', 'icon' => 'bi-bag-check'],
            ['label' => 'Menunggu Kasir', 'icon' => 'bi-clock'],
        ];
    @endphp

    {{-- ================= HEADER ================= --}}
    <header class="checkout-mobile-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h1 class="h5 mb-0">Checkout</h1>
            <p class="small text-muted-pos mb-0">Konfirmasi data pesanan</p>
        </div>
    </header>

    {{-- ================= STEPPER ================= --}}
    <nav class="checkout-stepper-mobile" aria-label="Langkah pemesanan">
        @foreach ($steps as $i => $step)
            <div class="checkout-step-mobile {{ $i === 0 ? 'is-done' : '' }} {{ $i === 1 ? 'is-active' : '' }}" role="listitem" {{ $i === 1 ? 'aria-current="step"' : '' }}>
                <span class="checkout-step-mobile__dot" aria-hidden="true">{{ $i === 0 ? '<i class="bi bi-check-lg" style="font-size: 0.625rem;"></i>' : ($i + 1) }}</span>
                <span class="checkout-step-mobile__label">{{ $step['label'] }}</span>
            </div>
        @endforeach
    </nav>

    {{-- Form promo harus di luar form checkout: <form> tidak boleh di-nest. --}}
    <form method="POST" action="{{ route('pelanggan.cart.promo') }}" id="promoForm" hidden></form>
    <form method="POST" action="{{ route('pelanggan.cart.promoRemove') }}" id="promoRemoveForm" hidden></form>

    <form method="POST" action="{{ route('pelanggan.checkout.store') }}" id="checkoutForm" novalidate>
        @csrf
        @if (request()->filled('product_id'))
            <input type="hidden" name="product_id" value="{{ request('product_id') }}">
            <input type="hidden" name="qty" value="{{ request('qty', 1) }}">
        @endif

        {{-- ================= STEP 1: DETAIL PESANAN / PENERIMA ================= --}}
        <section class="checkout-pane-mobile" id="stepRecipient">
            <h2 class="checkout-pane-mobile__title">
                <i class="bi bi-person-circle me-1"></i> Detail Penerima
            </h2>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="checkout-form-group">
                        <label for="customer_name" class="checkout-label">Nama Penerima <span class="text-danger">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" class="checkout-input @error('customer_name') is-invalid @enderror" value="{{ old('customer_name', auth()->user()->name) }}" required placeholder="Nama lengkap" autocomplete="name">
                        @error('customer_name')
                            <span class="checkout-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="checkout-form-group">
                        <label for="customer_phone" class="checkout-label">No. WhatsApp <span class="text-danger">*</span></label>
                        <input type="tel" id="customer_phone" name="customer_phone" class="checkout-input @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}" required placeholder="08xx-xxxx-xxxx" autocomplete="tel">
                        @error('customer_phone')
                            <span class="checkout-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-12">
                    <div class="checkout-form-group">
                        <label for="delivery_type" class="checkout-label">Jenis Pengiriman <span class="text-danger">*</span></label>
                        <select id="delivery_type" name="delivery_type" class="checkout-select @error('delivery_type') is-invalid @enderror" required>
                            <option value="pickup" {{ old('delivery_type', 'pickup') === 'pickup' ? 'selected' : '' }}>🏪 Ambil di Tempat</option>
                            <option value="delivery" {{ old('delivery_type') === 'delivery' ? 'selected' : '' }}>🚚 Antar</option>
                        </select>
                        @error('delivery_type')
                            <span class="checkout-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-12" id="addressField" {{ old('delivery_type', 'pickup') === 'delivery' ? '' : 'style="display:none"' }}>
                    <div class="checkout-form-group">
                        <label for="address" class="checkout-label">Alamat Pengiriman <span class="text-danger">*</span></label>
                        <textarea id="address" name="address" class="checkout-textarea @error('address') is-invalid @enderror" rows="2" placeholder="Alamat lengkap, RT/RW, kecamatan, kota">{{ old('address') }}</textarea>
                        @error('address')
                            <span class="checkout-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-12">
                    <div class="checkout-form-group">
                        <label for="note" class="checkout-label">Catatan</label>
                        <textarea id="note" name="note" class="checkout-textarea @error('note') is-invalid @enderror" rows="2" placeholder="Mis. level pedas, tambah saus, tanpa bawang, dst.">{{ old('note') }}</textarea>
                        @error('note')
                            <span class="checkout-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-12">
                    <div class="checkout-form-group">
                        <label class="checkout-label d-block">Metode Pembayaran <span class="text-danger">*</span></label>
                        @php
                            $deliveryType = old('delivery_type', 'pickup');
                            $canTunai = $deliveryType === 'pickup';
                        @endphp
                        <div class="checkout-payment-options" role="radiogroup" aria-label="Metode pembayaran">
                            <label class="checkout-payment-option">
                                <input type="radio" name="payment_method" value="online" {{ old('payment_method', 'online') === 'online' ? 'checked' : '' }} required>
                                <i class="checkout-payment-option__icon bi bi-credit-card"></i>
                                <span class="checkout-payment-option__label">Bayar Online (QRIS/VA/e-Wallet)</span>
                            </label>
                            <label class="checkout-payment-option {{ ! $canTunai ? 'd-none' : '' }}">
                                <input type="radio" name="payment_method" value="tunai" {{ old('payment_method') === 'tunai' ? 'checked' : '' }} {{ ! $canTunai ? 'disabled' : '' }} required>
                                <i class="checkout-payment-option__icon bi bi-cash"></i>
                                <span class="checkout-payment-option__label">Tunai {{ ! $canTunai ? '(hanya pickup)' : '' }}</span>
                            </label>
                            <label class="checkout-payment-option">
                                <input type="radio" name="payment_method" value="transfer" {{ old('payment_method') === 'transfer' ? 'checked' : '' }} required>
                                <i class="checkout-payment-option__icon bi bi-building"></i>
                                <span class="checkout-payment-option__label">Transfer Manual</span>
                            </label>
                        </div>
                        @error('payment_method')
                            <span class="checkout-error">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted d-block mt-1">
                            <span id="tunaiHint" class="{{ ! $canTunai ? 'text-danger' : 'text-muted' }}">{{ ! $canTunai ? 'Pembayaran tunai hanya tersedia untuk Ambil di Tempat (pickup).' : '' }}</span>
                        </small>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= STEP 2: RINCIAN ITEM ================= --}}
        <section class="checkout-pane-mobile" id="stepItems">
            <h2 class="checkout-pane-mobile__title">
                <i class="bi bi-receipt me-1"></i> Rincian Item ({{ $jumlahItem }} item)
            </h2>

            <div class="checkout-order-items">
                @foreach ($cart as $productId => $item)
                    <div class="checkout-order-item">
                        <div class="checkout-order-item__info">
                            <div class="checkout-order-item__name">{{ $item['name'] }}</div>
                            <div class="checkout-order-item__qty">{{ $item['qty'] }} &times; {{ $rp($item['price']) }}</div>
                        </div>
                        <div class="checkout-order-item__price">{{ $rp($item['price'] * $item['qty']) }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ================= STICKY ORDER SUMMARY ================= --}}
        <footer class="checkout-order-summary">
            <div class="checkout-summary-row">
                <span>Subtotal</span>
                <span>{{ $rp($subtotal) }}</span>
            </div>
            @if ($diskon > 0)
                <div class="checkout-summary-row checkout-summary-row--discount">
                    <span>Diskon ({{ $promoSession['code'] ?? '' }})</span>
                    <span>−{{ $rp($diskon) }}</span>
                </div>
            @endif
            <div class="checkout-summary-row checkout-summary-row--total">
                <span>Total Bayar</span>
                <span>{{ $rp($total) }}</span>
            </div>

            <div class="checkout-promo-mobile" id="promoSection">
                @if ($diskon > 0)
                    <div class="checkout-promo-mobile__applied">
                        <span>Promo <strong>{{ $promoSession['code'] }}</strong> aktif — Diskon {{ $rp($diskon) }}</span>
                        <button type="submit" class="checkout-promo-mobile__remove" form="promoRemoveForm"><i class="bi bi-x-lg me-1"></i> Hapus</button>
                    </div>
                @else
                    <div class="checkout-promo-mobile__input-group">
                        <input type="text" id="kodePromo" name="code" class="checkout-input checkout-promo-mobile__input" form="promoForm" placeholder="Kode promo" aria-label="Kode promo" autocomplete="off" style="padding: 0.75rem 1rem; font-size: 1rem;">
                        <button type="submit" id="promoSubmitBtn" class="btn btn-outline-primary checkout-promo-mobile__btn" form="promoForm" style="padding: 0.75rem 1rem; font-size: 0.875rem;">Terapkan</button>
                    </div>
                @endif
            </div>

            <button type="submit" class="btn btn-brand btn-lg checkout-submit-btn" id="submitBtn">
                <i class="bi bi-send me-1"></i> Submit Pesanan ({{ $rp($total) }})
            </button>

            <p class="checkout-note">Pembayaran di tempat saat pesanan siap diambil/antar</p>
        </footer>
    </form>

    {{-- Toast container --}}
    <div id="checkoutToast" class="cart-toast d-none" role="alert" aria-live="polite"></div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deliveryType = document.getElementById('delivery_type');
        const addressField = document.getElementById('addressField');
        const addressInput = document.getElementById('address');
        const checkoutForm = document.getElementById('checkoutForm');
        const submitBtn = document.getElementById('submitBtn');
        const promoForm = document.getElementById('promoForm');
        const promoInput = document.getElementById('kodePromo');
        const promoSubmitBtn = document.getElementById('promoSubmitBtn');

        // Toggle address field based on delivery type
        function toggleAddressField() {
            if (deliveryType.value === 'delivery') {
                addressField.style.display = 'block';
                addressInput.required = true;
            } else {
                addressField.style.display = 'none';
                addressInput.required = false;
            }
        }

        deliveryType.addEventListener('change', toggleAddressField);
        toggleAddressField();

        // Promo form submit
        if (promoForm && promoInput && promoSubmitBtn) {
            promoForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const originalText = promoSubmitBtn.textContent;
                promoSubmitBtn.disabled = true;
                promoSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

                // Input promo berada di luar elemen form (asosiasi via atribut
                // `form`), jadi FormData harus dirakit manual.
                const body = new FormData();
                body.append('code', promoInput.value);

                fetch(promoForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: body
                }).then(r => r.json()).then(data => {
                    promoSubmitBtn.disabled = false;
                    promoSubmitBtn.textContent = originalText;
                    if (data.success) {
                        showToast('Promo diterapkan!', 'success');
                        setTimeout(() => location.reload(), 500);
                    } else {
                        showToast(data.error || 'Kode promo tidak valid', 'error');
                    }
                }).catch(() => {
                    promoSubmitBtn.disabled = false;
                    promoSubmitBtn.textContent = originalText;
                    showToast('Terjadi kesalahan jaringan', 'error');
                });
            });
        }

        // Form validation & submit
        checkoutForm.addEventListener('submit', function(e) {
            // Basic validation
            const requiredFields = checkoutForm.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            // Check address if delivery
            if (deliveryType.value === 'delivery' && !addressInput.value.trim()) {
                addressInput.classList.add('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                showToast('Lengkapi semua field yang wajib diisi', 'error');

                // Scroll to first invalid field
                const firstInvalid = checkoutForm.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Memproses...';
        });

        // Remove invalid state on input
        checkoutForm.querySelectorAll('.checkout-input, .checkout-select, .checkout-textarea').forEach(field => {
            field.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        });

        // Tunai hanya untuk pickup
        const deliveryType = document.getElementById('delivery_type');
        const tunaiRadio = document.querySelector('input[name="payment_method"][value="tunai"]');
        const tunaiLabel = tunaiRadio ? tunaiRadio.closest('.checkout-payment-option') : null;
        const tunaiHint = document.getElementById('tunaiHint');

        // If critical elements don't exist, bail out
        if (!deliveryType || !tunaiRadio) {
            // continue to toast helper
        } else {
            function updateTunai() {
                const isPickup = deliveryType.value === 'pickup';
                if (tunaiLabel) tunaiLabel.classList.toggle('d-none', !isPickup);
                if (tunaiRadio) tunaiRadio.disabled = !isPickup;
                if (tunaiHint) {
                    tunaiHint.classList.toggle('text-danger', !isPickup);
                    tunaiHint.classList.toggle('text-muted', isPickup);
                    tunaiHint.textContent = isPickup ? '' : 'Pembayaran tunai hanya tersedia untuk Ambil di Tempat (pickup).';
                }

                if (!isPickup && tunaiRadio && tunaiRadio.checked) {
                    const onlineRadio = document.querySelector('input[name="payment_method"][value="online"]');
                    if (onlineRadio) onlineRadio.checked = true;
                }
            }

            deliveryType.addEventListener('change', updateTunai);
            updateTunai();
        }

        // Toast helper
        function showToast(message, type = 'info') {
            const toast = document.getElementById('checkoutToast');
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