<nav class="category-pills" aria-label="Filter kategori" role="tablist">
    <a href="{{ route('pelanggan.katalog.mobile') }}"
       class="category-pill {{ ! request('category_id') ? 'active' : '' }}"
       role="tab"
       aria-selected="{{ ! request('category_id') ? 'true' : 'false' }}"
       data-cf-field="category_id"
       data-cf-value="">
        <i class="bi bi-grid me-1"></i> Semua
    </a>
    @foreach ($kategori as $k)
        <a href="{{ route('pelanggan.katalog.mobile', ['category_id' => $k->id, 'search' => request('search')]) }}"
           class="category-pill {{ request('category_id') == $k->id ? 'active' : '' }}"
           role="tab"
           aria-selected="{{ request('category_id') == $k->id ? 'true' : 'false' }}"
           data-cf-field="category_id"
           data-cf-value="{{ $k->id }}">
            <i class="bi {{ $k->icon }} me-1"></i> {{ $k->name }}
        </a>
    @endforeach
</nav>

<div class="product-grid-mobile" id="productGrid">
    @forelse ($produk as $p)
        <x-product-card-mobile :product="$p" :showAddToCart="true" data-cf-row data-category="{{ $p->category_id }}" data-cf-name="{{ $p->name }}" />
    @empty
        <div class="col-12">
            <div class="empty-state-mobile">
                <div class="empty-state-mobile__icon"><i class="bi bi-box-seam"></i></div>
                <div class="empty-state-mobile__title">Tidak ada produk</div>
                <p class="empty-state-mobile__text">Coba ubah filter atau kata kunci pencarian.</p>
                <a href="{{ route('pelanggan.katalog.mobile') }}" class="btn btn-brand btn-sm" data-rt-link>
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset Filter
                </a>
            </div>
        </div>
    @endforelse
    <div class="col-12" data-cf-empty hidden>
        <div class="empty-state-mobile">
            <div class="empty-state-mobile__icon"><i class="bi bi-search"></i></div>
            <div class="empty-state-mobile__title">Tidak ada produk yang cocok</div>
            <p class="empty-state-mobile__text">Coba ubah filter atau kata kunci pencarian.</p>
        </div>
    </div>
</div>

<nav class="pagination-mobile" aria-label="Navigasi halaman katalog">
    {{ $produk->onEachSide(1)->links('vendor.pagination.custom') }}
</nav>
