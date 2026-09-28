@props(['product', 'showAddToCart' => true, 'compact' => false])

@php
    $rp = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
    $stokStatus = fn (int $stok) => $stok <= 0 ? 'habis' : ($stok <= 10 ? 'menipis' : 'tersedia');
    $stokLabel = ['tersedia' => 'Tersedia', 'menipis' => 'Stok menipis', 'habis' => 'Habis'];
    $stokBadgeClass = ['tersedia' => 'bg-success-subtle text-success', 'menipis' => 'bg-warning-subtle text-warning', 'habis' => 'bg-danger-subtle text-danger'];
    $stokSt = $stokStatus($product->stock);
    $isOut = $stokSt === 'habis';
@endphp

<div class="product-card-mobile {{ $compact ? 'product-card-mobile--compact' : '' }} {{ $isOut ? 'product-card-mobile--out' : '' }}">
    <a href="{{ route('marketplace.produk', $product->id) }}" class="product-card-mobile__link" aria-label="Lihat detail {{ $product->name }}">
        <div class="product-card-mobile__image">
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="product-card-mobile__img" loading="lazy" width="200" height="160">
            @else
                <div class="product-card-mobile__placeholder"><x-product-placeholder size="lg" /></div>
            @endif
            @if ($isOut)
                <span class="product-card-mobile__out-badge">Habis</span>
            @endif
            @if ($product->is_featured ?? false)
                <span class="product-card-mobile__featured-badge"><i class="bi bi-star-fill"></i></span>
            @endif
        </div>

        <div class="product-card-mobile__content">
            <div class="product-card-mobile__category">
                <span class="badge badge-soft badge-soft--neutral badge-sm">{{ $product->category?->name ?? '-' }}</span>
            </div>

            <h3 class="product-card-mobile__name">{{ $product->name }}</h3>

            <div class="product-card-mobile__meta">
                <span class="product-card-mobile__price">{{ $rp($product->price) }}</span>
                <span class="badge badge-soft {{ $stokBadgeClass[$stokSt] }} badge-sm">
                    <span class="badge-soft__dot"></span>{{ $stokLabel[$stokSt] }}
                </span>
            </div>
        </div>
    </a>

    @if ($showAddToCart && !$isOut)
        <div class="product-card-mobile__actions">
            @auth
                <form method="POST" action="{{ route('pelanggan.cart.add') }}" class="cart-add-form-mobile" data-product-id="{{ $product->id }}" data-stock="{{ $product->stock }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="qty" value="1" class="cart-qty-input">
                    <button type="submit" class="btn btn-brand btn-sm w-100 d-flex align-items-center justify-content-center cart-add-btn">
                        <i class="bi bi-cart-plus me-1"></i> Tambah
                    </button>
                </form>
            @else
                <button type="button" class="btn btn-brand btn-sm w-100 d-flex align-items-center justify-content-center" onclick="requireLogin('menambahkan item ke keranjang')">
                    <i class="bi bi-cart-plus me-1"></i> Tambah
                </button>
            @endauth
        </div>
    @elseif ($isOut)
        <div class="product-card-mobile__actions">
            <button type="button" class="btn btn-outline-secondary btn-sm w-100" disabled>
                <i class="bi bi-cart-x me-1"></i> Habis
            </button>
        </div>
    @endif
</div>