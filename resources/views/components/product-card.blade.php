@props(['product', 'showAddToCart' => true])

@php
    $rp = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
    $stok = (int) $product->stock;
    $stokStatus = $stok <= 0 ? 'habis' : ($stok <= 10 ? 'menipis' : 'tersedia');
    $stokLabel = ['tersedia' => 'Tersedia', 'menipis' => 'Stok menipis', 'habis' => 'Habis'];
    $isOut = $stokStatus === 'habis';
    $kategori = $product->category?->name ?? null;
@endphp

<div class="product-card {{ $isOut ? 'product-card--out' : '' }}">
    <div class="product-card__image">
        @if ($product->image)
            <img
                src="{{ asset('storage/' . $product->image) }}"
                alt="{{ $product->name }}"
                class="product-card__img"
                loading="lazy"
                width="320"
                height="240"
            >
        @else
            <x-product-placeholder size="lg" :category="$kategori" />
        @endif

        @if ($isOut)
            <span class="product-card__out-badge"><i class="bi bi-x-circle"></i> Habis</span>
        @endif

        @if ($product->is_featured ?? false)
            <span class="product-card__featured-badge" title="Produk unggulan">
                <i class="bi bi-star-fill"></i>
            </span>
        @endif
    </div>

    <div class="product-card__body">
        <div class="product-card__eyebrow">
            @if ($kategori)
                <span class="badge badge-soft badge-soft--neutral badge-sm">{{ $kategori }}</span>
            @else
                <span></span>
            @endif
            <span class="stock-badge stock-badge--{{ $stokStatus }}">{{ $stokLabel[$stokStatus] }}</span>
        </div>

        <h3 class="product-card__name">{{ $product->name }}</h3>

        <div class="product-card__price">{{ $rp($product->price) }}</div>

        <div class="product-card__actions">
            <a href="{{ route('marketplace.produk', $product->id) }}" class="stretched-link" aria-label="Lihat detail {{ $product->name }}"></a>

            @if ($isOut)
                <button type="button" class="btn btn-soft btn-sm w-100" disabled>
                    <i class="bi bi-cart-x me-1"></i> Stok Habis
                </button>
            @elseif ($showAddToCart)
                @auth
                    <form
                        method="POST"
                        action="{{ route('pelanggan.cart.add') }}"
                        class="cart-add-form"
                        data-product-id="{{ $product->id }}"
                        data-stock="{{ $product->stock }}"
                    >
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="qty" value="1" class="cart-qty-input">
                        <button type="submit" class="btn btn-accent btn-sm w-100 d-flex align-items-center justify-content-center gap-1 cart-add-btn">
                            <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                        </button>
                    </form>
                @else
                    <button
                        type="button"
                        class="btn btn-accent btn-sm w-100 d-flex align-items-center justify-content-center gap-1"
                        onclick="requireLogin('menambahkan item ke keranjang')"
                    >
                        <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                    </button>
                @endauth
            @endif
        </div>
    </div>
</div>
