@extends('layouts.pelanggan')

@section('title', 'Katalog — Pelanggan')

@section('content')
    @php
        $rp = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
        $stokStatus = fn (int $stok) => $stok <= 0 ? 'habis' : ($stok <= 10 ? 'menipis' : 'tersedia');
        $stokLabel = ['tersedia' => 'Tersedia', 'menipis' => 'Stok menipis', 'habis' => 'Habis'];
        $stokBadge = ['tersedia' => 'badge-soft--success', 'menipis' => 'badge-soft--warning', 'habis' => 'badge-soft--danger'];
        $aktifSlug = request('category_id') ? $kategori->firstWhere('id', request('category_id'))?->slug ?? 'semua' : 'semua';
        $langkah = [
            ['judul' => 'Pilih menu', 'teks' => 'Tambah produk favoritmu ke keranjang.'],
            ['judul' => 'Checkout & submit', 'teks' => 'Isi data pesanan lalu kirim.'],
            ['judul' => 'Kasir konfirmasi', 'teks' => 'Kasir memverifikasi pesanan sebelum diproses.'],
        ];
    @endphp

    {{-- ================= HERO ================= --}}
    <div class="pane d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Katalog Menu</h1>
            <p class="small text-muted-pos mb-0">Pesan dari kami — konfirmasi kasir sebelum diproses.</p>
        </div>
        <div class="search-box search-box--wide">
            <form action="{{ route('pelanggan.katalog.index') }}" method="get" class="d-flex">
                <i class="bi bi-search search-box__icon"></i>
                <input type="search" class="form-control" name="search" placeholder="Cari menu..." aria-label="Cari menu" value="{{ request('search') }}">
                <button type="submit" class="btn btn-brand">Cari</button>
            </form>
        </div>
    </div>

    {{-- ================= FILTER KATEGORI ================= --}}
    <ul class="nav nav-pills flex-wrap gap-2 mb-4">
        <li class="nav-item">
            <a class="nav-link {{ request('category_id') === '' && request('search') === '' ? 'active' : '' }}" href="{{ route('pelanggan.katalog.index') }}">
                <i class="bi bi-grid me-1"></i> Semua
                <span class="badge badge-soft badge-soft--neutral badge-soft--count ms-1">{{ $produk->total() }}</span>
            </a>
        </li>
        @foreach ($kategori as $k)
            <li class="nav-item">
                <a class="nav-link {{ request('category_id') == $k->id ? 'active' : '' }}" href="{{ route('pelanggan.katalog.index', ['category_id' => $k->id, 'search' => request('search')]) }}">
                    <i class="bi {{ $k->icon }} me-1"></i>
                    {{ $k->name }}
                    <span class="badge badge-soft badge-soft--neutral badge-soft--count ms-1">{{ $kategoriCounts[$k->slug] ?? 0 }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    {{-- ================= GRID PRODUK ================= --}}
    <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 g-3 mb-4">
        @forelse ($produk as $p)
            @php
                $stokSt = $stokStatus($p->stock);
            @endphp
            <div class="col">
                <div class="product-card {{ $stokSt === 'habis' ? 'product-card--out' : '' }}" style="position:relative;display:flex;flex-direction:column;height:100%;">
                    <div class="product-card__image">
                        @if ($p->image)
                            <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->name }}" class="product-card__img" style="width:100%;height:160px;object-fit:cover;">
                        @else
                            <x-product-placeholder size="lg" />
                        @endif
                        @if ($stokSt === 'habis')
                            <span class="product-card__out-badge">Habis</span>
                        @endif
                    </div>
                    <div class="product-card__body d-flex flex-column gap-1 flex-grow-1">
                        <div class="product-card__name">{{ $p->name }}</div>
                        <div class="product-card__meta">
                            <span class="badge badge-soft badge-soft--neutral">{{ $p->category?->name ?? '-' }}</span>
                        </div>
                        <div class="product-card__price">{{ $rp($p->price) }}</div>
                        <div class="small mt-auto pt-1">
                            <span class="badge badge-soft {{ $stokBadge[$stokSt] }}"><span class="badge-soft__dot"></span>{{ $stokLabel[$stokSt] }}</span>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('marketplace.produk', $p->id) }}" class="stretched-link" aria-label="Lihat detail {{ $p->name }}"></a>
                            @if ($stokSt !== 'habis')
                                @auth
                                    <form method="POST" action="{{ route('pelanggan.cart.add') }}" class="cart-add-form" style="display:inline;" data-product-id="{{ $p->id }}" data-stock="{{ $p->stock }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $p->id }}">
                                        <input type="hidden" name="qty" value="1" class="cart-qty-input">
                                        <button type="submit" class="btn btn-brand btn-sm w-100 d-flex align-items-center justify-content-center cart-add-btn">
                                            <i class="bi bi-cart-plus me-1"></i> Tambah ke Keranjang
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-brand btn-sm w-100 d-flex align-items-center justify-content-center" onclick="requireLogin('menambahkan item ke keranjang')">
                                        <i class="bi bi-cart-plus me-1"></i> Tambah ke Keranjang
                                    </button>
                                @endauth
                            @else
                                <button type="button" class="btn btn-brand btn-sm w-100" disabled>
                                    <i class="bi bi-cart-plus me-1"></i> Habis
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="pane text-center py-4">
                    <p class="text-muted-pos">Tidak ada produk ditemukan.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- ================= PAGINASI ================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <small class="text-muted-pos">Menampilkan {{ $produk->firstItem() ?? 0 }}-{{ $produk->lastItem() ?? 0 }} dari {{ $produk->total() }} produk</small>
        <nav aria-label="Navigasi halaman katalog" class="pagination-arrow">
            {{ $produk->links() }}
        </nav>
    </div>

    {{-- ================= CARA TRANSAKSI ================= --}}
    <section class="pane">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h2 class="h5 mb-0">Cara Bertransaksi</h2>
            <span class="badge badge-soft badge-soft--neutral">Konfirmasi kasir</span>
        </div>
        <div class="row g-3">
            @foreach ($langkah as $i => $step)
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-start gap-3">
                        <span class="avatar avatar--lg flex-shrink-0"><strong>{{ $i + 1 }}</strong></span>
                        <div>
                            <h3 class="h6 mb-1">{{ $step['judul'] }}</h3>
                            <p class="small text-muted-pos mb-0">{{ $step['teks'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection

@push('scripts')
<script>
    const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cart-add-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                if (! isLoggedIn) {
                    e.preventDefault();
                    requireLogin();
                    return;
                }
                e.preventDefault();
                const btn = form.querySelector('.cart-add-btn');
                btn.disabled = true;
                fetch(form.action, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
                    body: new FormData(form)
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Berhasil ditambahkan ke keranjang!', showConfirmButton: false, timer: 2000, timerProgressBar: true});
                        btn.disabled = false;
                    } else {
                        Swal.fire({icon: 'error', title: 'Gagal', text: data.error || 'Terjadi kesalahan'});
                        btn.disabled = false;
                    }
                }).catch(() => {
                    Swal.fire({icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan jaringan'});
                    btn.disabled = false;
                });
            });
        });
    });
</script>
@endpush
