@extends('layouts.pelanggan')

@section('title', 'Marketplace — Pelanggan')

@section('content')
    @php
        $langkah = [
            ['judul' => 'Pilih menu', 'teks' => 'Tambah produk favoritmu ke keranjang.'],
            ['judul' => 'Checkout & submit', 'teks' => 'Isi data pesanan lalu kirim.'],
            ['judul' => 'Kasir konfirmasi', 'teks' => 'Kasir memverifikasi pesanan sebelum diproses.'],
        ];
        $filterAktif = request()->filled('category_id') || request()->filled('search');
    @endphp

    {{-- ================= HERO ================= --}}
    <div class="pane d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Marketplace</h1>
            <p class="small text-muted-pos mb-0">Pesan dari kami — konfirmasi kasir sebelum diproses.</p>
        </div>
        <div class="search-box search-box--wide">
            <form action="{{ route('marketplace') }}" method="get" class="d-flex" id="katalog-search-form">
                <i class="bi bi-search search-box__icon"></i>
                <input type="search" class="form-control" name="search" placeholder="Cari menu..." aria-label="Cari menu" value="{{ request('search') }}">
                <button type="submit" class="btn btn-brand">Cari</button>
            </form>
        </div>
    </div>

    {{-- ================= FILTER KATEGORI ================= --}}
    <ul class="nav nav-pills flex-wrap gap-2 mb-4" id="katalog-filter">
        <li class="nav-item">
            <a class="nav-link {{ $filterAktif ? '' : 'active' }}" href="{{ route('marketplace') }}">
                <i class="bi bi-grid me-1"></i> Semua
                <span class="badge badge-soft badge-soft--neutral badge-soft--count ms-1">&mdash;</span>
            </a>
        </li>
    </ul>

    {{-- ================= GRID PRODUK (di-load via /api/produk) ================= --}}
    <div
        id="product-grid"
        class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 g-3 mb-4"
        data-api="{{ route('api.produk') }}"
        data-marketplace="{{ route('marketplace') }}"
    >
        @for ($i = 0; $i < 8; $i++)
            <div class="col"><div class="skeleton-card"></div></div>
        @endfor
    </div>

    {{-- ================= PAGINASI ================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <small class="text-muted-pos" id="katalog-meta"></small>
        <nav aria-label="Navigasi halaman marketplace" class="pagination-arrow" id="katalog-pagination"></nav>
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
    document.addEventListener('submit', function(e) {
        const form = e.target.closest('.cart-add-form');
        if (! form) {
            return;
        }

        e.preventDefault();

        if (! isLoggedIn) {
            requireLogin();
            return;
        }

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
</script>
@endpush