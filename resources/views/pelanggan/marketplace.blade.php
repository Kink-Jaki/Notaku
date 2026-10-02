@extends('layouts.pelanggan')

@section('title', 'Marketplace — Pelanggan')

@section('content')
    @php
        $langkah = [
            ['judul' => 'Pilih menu', 'teks' => 'Tambah produk favoritmu ke keranjang.', 'ikon' => 'bi-bag-heart'],
            ['judul' => 'Checkout & submit', 'teks' => 'Isi data pesanan lalu kirim.', 'ikon' => 'bi-receipt'],
            ['judul' => 'Kasir konfirmasi', 'teks' => 'Kasir memverifikasi pesanan sebelum diproses.', 'ikon' => 'bi-person-check'],
        ];
        $filterAktif = request()->filled('category_id') || request()->filled('search');
    @endphp

    {{-- ================= HERO ================= --}}
    <div class="pane mb-4 overflow-hidden position-relative marketplace-hero">
        <div class="marketplace-hero__glow" aria-hidden="true"></div>

        <div class="d-flex flex-wrap align-items-end justify-content-between gap-4 position-relative">
            <div class="marketplace-hero__intro">
                <span class="badge badge-soft badge-soft--accent rounded-pill mb-2">
                    <i class="bi bi-stars me-1"></i>Segarkan laparmu
                </span>
                <h1 class="h3 mb-2">Marketplace</h1>
                <p class="text-muted-pos mb-0 marketplace-hero__lead">
                    Pesan dari {{ $settings->brand_name ?? 'kami' }} &mdash; konfirmasi kasir sebelum diproses.
                </p>
            </div>

            <div class="search-box search-box--wide" data-search-box>
                <form action="{{ route('marketplace') }}" method="get" class="d-flex align-items-center gap-2" id="katalog-search-form">
                    <div class="search-box__field" data-search-field>
                        <i class="bi bi-search search-box__icon" aria-hidden="true"></i>
                        <input
                            type="search"
                            class="form-control"
                            name="search"
                            data-cf-search
                            placeholder="Cari menu atau kategori..."
                            aria-label="Cari menu"
                            value="{{ request('search') }}"
                            autocomplete="off"
                        >
                        <span class="search-box__kbd" aria-hidden="true">/</span>
                    </div>
                    <button type="submit" class="btn btn-accent flex-shrink-0">
                        <i class="bi bi-search d-lg-none"></i>
                        <span class="d-none d-lg-inline">Cari</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= FILTER KATEGORI ================= --}}
    <ul class="nav cat-tabs mb-4" id="katalog-filter" data-cf="katalog">
        <li class="nav-item">
            <a
                class="nav-link {{ $filterAktif ? '' : 'active' }}"
                href="{{ route('marketplace') }}"
                data-cf-field="category_id"
                data-cf-value=""
            >
                <i class="bi bi-grid" aria-hidden="true"></i> Semua
                <span class="cat-tabs__count" data-cat-count="semua">&mdash;</span>
            </a>
        </li>
    </ul>

    {{-- ================= GRID PRODUK (di-load via /api/produk) ================= --}}
    <div
        id="product-grid"
        class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 g-3 g-lg-4 mb-4"
        data-api="{{ route('api.produk') }}"
        data-marketplace="{{ route('marketplace') }}"
        data-cart-update="{{ route('pelanggan.cart.update') }}"
        data-cf="katalog"
    >
        @for ($i = 0; $i < 8; $i++)
            <div class="col"><div class="skeleton-card"></div></div>
        @endfor
    </div>

    {{-- ================= PAGINASI ================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4" data-cf="katalog">
        <small class="text-muted-pos" id="katalog-meta" data-cf-summary="Menampilkan {n} produk di halaman ini"></small>
        <nav aria-label="Navigasi halaman marketplace" class="pagination-arrow" id="katalog-pagination"></nav>
    </div>

    {{-- ================= CARA TRANSAKSI ================= --}}
    <section class="pane">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h2 class="h5 mb-0">Cara Bertransaksi</h2>
            <span class="badge badge-soft badge-soft--info rounded-pill">
                <i class="bi bi-shield-check me-1"></i>Konfirmasi kasir
            </span>
        </div>

        <div class="row g-3">
            @foreach ($langkah as $i => $step)
                <div class="col-12 col-md-4">
                    <div class="step-card">
                        <span class="step-card__number">{{ $i + 1 }}</span>
                        <div class="min-w-0">
                            <h3 class="h6 mb-1 step-card__title">
                                <i class="bi {{ $step['ikon'] }} me-1" aria-hidden="true"></i>{{ $step['judul'] }}
                            </h3>
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
    (function () {
        const boxes = document.querySelectorAll('[data-search-box]');

        boxes.forEach((box) => {
            const input = box.querySelector('[data-cf-search], input[name="search"]');
            const field = box.querySelector('[data-search-field]') ?? box;
            const clear = document.createElement('button');
            const form = box.querySelector('form');

            clear.type = 'button';
            clear.className = 'search-box__clear';
            clear.setAttribute('aria-label', 'Bersihkan pencarian');
            clear.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';

            const sync = () => {
                box.classList.toggle('has-value', Boolean(input?.value));
            };

            clear.addEventListener('click', () => {
                if (! input) {
                    return;
                }

                input.value = '';
                sync();
                input.focus();

                if (form && form.id === 'katalog-search-form') {
                    form.requestSubmit?.();
                }
            });

            input?.addEventListener('input', sync);
            sync();

            field.appendChild(clear);
        });

        document.addEventListener('keydown', (event) => {
            const target = event.target;
            const isTyping = target instanceof HTMLElement
                && (target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName));

            if (event.key !== '/' || isTyping || event.metaKey || event.ctrlKey || event.altKey) {
                return;
            }

            const input = document.querySelector('[data-cf-search], input[name="search"]');

            if (! input) {
                return;
            }

            event.preventDefault();
            input.focus();
            input.select();
        });
    })();
</script>
@endpush
