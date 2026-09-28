@extends('layouts.pelanggan')

@section('title', 'Katalog Mobile — Pelanggan')

@push('styles')
<style>
    /* Mobile-optimized styles inline for fast loading */
    .catalog-mobile-hero {
        padding: 1rem;
        background: linear-gradient(135deg, var(--color-primary-soft), var(--color-bg));
        border-radius: var(--pos-radius);
        margin-bottom: 1rem;
    }

    .category-pills {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding: 0.5rem 0;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .category-pills::-webkit-scrollbar {
        display: none;
    }

    .category-pill {
        flex: 0 0 auto;
        scroll-snap-align: start;
        padding: 0.5rem 1rem;
        border-radius: 999px;
        background-color: var(--color-surface);
        border: 1px solid var(--color-border);
        color: var(--color-text-muted);
        font-size: 0.8125rem;
        font-weight: 500;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .category-pill.active,
    .category-pill:active {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
        color: #ffffff;
    }

    .category-pill:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 2px;
    }

    .product-grid-mobile {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    @media (max-width: 480px) {
        .product-grid-mobile {
            gap: 0.625rem;
        }
    }

    @media (min-width: 768px) {
        .product-grid-mobile {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 992px) {
        .product-grid-mobile {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .empty-state-mobile {
        padding: 3rem 1rem;
        text-align: center;
    }

    .empty-state-mobile__icon {
        font-size: 3rem;
        color: var(--color-border);
        margin-bottom: 1rem;
    }

    .empty-state-mobile__title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--color-text);
        margin-bottom: 0.5rem;
    }

    .empty-state-mobile__text {
        color: var(--color-text-muted);
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
    }

    .search-mobile {
        position: relative;
    }

    .search-mobile__input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        border: 1px solid var(--color-border);
        border-radius: var(--pos-radius);
        background-color: var(--color-surface);
        font-size: 1rem;
        /* Prevent zoom on iOS */
    }

    .search-mobile__input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 0.25rem rgba(var(--color-primary-rgb), 0.15);
    }

    .search-mobile__icon {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--color-text-muted);
        pointer-events: none;
        font-size: 1rem;
    }

    .skeleton-product {
        background: linear-gradient(90deg, var(--color-border) 25%, #f1f5f9 50%, var(--color-border) 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.2s ease-in-out infinite;
        border-radius: var(--pos-radius);
        overflow: hidden;
    }

    .skeleton-product__image {
        aspect-ratio: 4 / 3;
    }

    .skeleton-product__content {
        padding: 0.75rem;
    }

    .skeleton-product__line {
        height: 0.875rem;
        border-radius: 0.25rem;
        margin-bottom: 0.375rem;
        background: linear-gradient(90deg, var(--color-border) 25%, #f1f5f9 50%, var(--color-border) 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.2s ease-in-out infinite;
    }

    .skeleton-product__line--short {
        width: 60%;
    }

    .skeleton-product__line--price {
        height: 1.125rem;
        width: 40%;
        margin-top: 0.5rem;
    }

    @keyframes skeleton-loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .pagination-mobile {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 0;
    }

    .pagination-mobile .page-link {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid var(--color-border);
        color: var(--color-text);
    }

    .pagination-mobile .page-link:hover {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
        color: #ffffff;
    }

    .pagination-mobile .page-item.active .page-link {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
        color: #ffffff;
    }

    .how-it-works-mobile {
        background-color: var(--color-surface);
        border-radius: var(--pos-radius);
        padding: 1rem;
        margin-top: 1.5rem;
    }

    .how-it-works-mobile__step {
        display: flex;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--color-border);
    }

    .how-it-works-mobile__step:last-child {
        border-bottom: none;
    }

    .how-it-works-mobile__number {
        flex: 0 0 auto;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background-color: var(--color-primary-soft);
        color: var(--color-primary);
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 0.875rem;
    }

    .how-it-works-mobile__text {
        flex: 1;
        min-width: 0;
    }

    .how-it-works-mobile__title {
        font-weight: 600;
        color: var(--color-text);
        margin-bottom: 0.125rem;
    }

    .how-it-works-mobile__desc {
        font-size: 0.8125rem;
        color: var(--color-text-muted);
    }

    /* Touch feedback for product cards */
    .product-card-mobile:active {
        transform: scale(0.98);
        transition: transform 0.05s ease;
    }
</style>
@endpush

@section('content')
    @php
        $rp = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
        $stokStatus = fn (int $stok) => $stok <= 0 ? 'habis' : ($stok <= 10 ? 'menipis' : 'tersedia');
        $stokLabel = ['tersedia' => 'Tersedia', 'menipis' => 'Stok menipis', 'habis' => 'Habis'];
        $stokBadge = ['tersedia' => 'badge-soft--success', 'menipis' => 'badge-soft--warning', 'habis' => 'badge-soft--danger'];
        $langkah = [
            ['judul' => 'Pilih menu', 'teks' => 'Tambah produk favoritmu ke keranjang.'],
            ['judul' => 'Checkout & submit', 'teks' => 'Isi data pesanan lalu kirim.'],
            ['judul' => 'Kasir konfirmasi', 'teks' => 'Kasir memverifikasi pesanan sebelum diproses.'],
        ];
        $currentCategory = request('category_id');
        $currentSearch = request('search');
    @endphp

    {{-- ================= HERO / SEARCH ================= --}}
    <div class="catalog-mobile-hero">
        <h1 class="h5 mb-1">Katalog Menu</h1>
        <p class="small text-muted-pos mb-2">Pesan dari kami — konfirmasi kasir sebelum diproses.</p>

        <div class="search-mobile">
            <form action="{{ route('pelanggan.katalog.index') }}" method="get" class="d-flex">
                <i class="bi bi-search search-mobile__icon"></i>
                <input type="search" name="search" class="search-mobile__input" placeholder="Cari menu..." value="{{ $currentSearch }}" aria-label="Cari menu" autocomplete="off">
            </form>
        </div>
    </div>

    {{-- ================= CATEGORY FILTER ================= --}}
    <nav class="category-pills" aria-label="Filter kategori" role="tablist">
        <a href="{{ route('pelanggan.katalog.index') }}"
           class="category-pill {{ !$currentCategory && !$currentSearch ? 'active' : '' }}"
           role="tab"
           aria-selected="{{ !$currentCategory && !$currentSearch ? 'true' : 'false' }}">
            <i class="bi bi-grid me-1"></i> Semua
        </a>
        @foreach ($kategori as $k)
            <a href="{{ route('pelanggan.katalog.index', ['category_id' => $k->id, 'search' => $currentSearch]) }}"
               class="category-pill {{ $currentCategory == $k->id ? 'active' : '' }}"
               role="tab"
               aria-selected="{{ $currentCategory == $k->id ? 'true' : 'false' }}">
                <i class="bi {{ $k->icon }} me-1"></i> {{ $k->name }}
            </a>
        @endforeach
    </nav>

    {{-- ================= PRODUCT GRID ================= --}}
    <div class="product-grid-mobile" id="productGrid">
        @forelse ($produk as $p)
            @php
                $stokSt = $stokStatus($p->stock);
            @endphp
            <x-product-card-mobile :product="$p" :showAddToCart="true" />
        @empty
            <div class="col-12">
                <div class="empty-state-mobile">
                    <div class="empty-state-mobile__icon"><i class="bi bi-box-seam"></i></div>
                    <div class="empty-state-mobile__title">Tidak ada produk</div>
                    <p class="empty-state-mobile__text">Coba ubah filter atau kata kunci pencarian.</p>
                    <a href="{{ route('pelanggan.katalog.index') }}" class="btn btn-brand btn-sm">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset Filter
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- ================= SKELETON LOADING (hidden by default) ================= --}}
    <div class="product-grid-mobile d-none" id="productSkeleton">
        @for ($i = 0; $i < 8; $i++)
            <div class="skeleton-product">
                <div class="skeleton-product__image"></div>
                <div class="skeleton-product__content">
                    <div class="skeleton-product__line"></div>
                    <div class="skeleton-product__line skeleton-product__line--short"></div>
                    <div class="skeleton-product__line skeleton-product__line--price"></div>
                </div>
            </div>
        @endfor
    </div>

    {{-- ================= PAGINATION ================= --}}
    <nav class="pagination-mobile" aria-label="Navigasi halaman katalog">
        {{ $produk->onEachSide(1)->links('vendor.pagination.custom') }}
    </nav>

    {{-- ================= HOW IT WORKS ================= --}}
    <section class="how-it-works-mobile">
        <h2 class="h6 mb-3">Cara Bertransaksi</h2>
        @foreach ($langkah as $i => $step)
            <div class="how-it-works-mobile__step">
                <span class="how-it-works-mobile__number">{{ $i + 1 }}</span>
                <div class="how-it-works-mobile__text">
                    <div class="how-it-works-mobile__title">{{ $step['judul'] }}</div>
                    <div class="how-it-works-mobile__desc">{{ $step['teks'] }}</div>
                </div>
            </div>
        @endforeach
    </section>
@endsection

@push('scripts')
<script>
    // Enhanced mobile UX: smooth scroll for category pills
    document.addEventListener('DOMContentLoaded', function() {
        const categoryPills = document.querySelector('.category-pills');
        if (categoryPills) {
            let isDown = false;
            let startX;
            let scrollLeft;

            categoryPills.addEventListener('mousedown', (e) => {
                isDown = true;
                startX = e.pageX - categoryPills.offsetLeft;
                scrollLeft = categoryPills.scrollLeft;
            });

            categoryPills.addEventListener('mouseleave', () => { isDown = false; });
            categoryPills.addEventListener('mouseup', () => { isDown = false; });
            categoryPills.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - categoryPills.offsetLeft;
                const walk = (x - startX) * 2;
                categoryPills.scrollLeft = scrollLeft - walk;
            });
        }

        // Product card touch feedback
        document.querySelectorAll('.product-card-mobile').forEach(card => {
            card.addEventListener('touchstart', () => {
                card.style.transform = 'scale(0.98)';
            }, { passive: true });

            card.addEventListener('touchend', () => {
                card.style.transform = '';
            }, { passive: true });
        });
    });
</script>
@endpush