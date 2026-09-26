@extends('layouts.pelanggan')

@section('title', $produk->name . ' — Pelanggan')

@section('content')
    {{-- ================= KEMBALI + BREADCRUMB ================= --}}
    <div class="mb-3">
        <a href="{{ route('marketplace') }}" class="d-inline-flex align-items-center gap-1 small text-decoration-none text-muted-pos mb-2">
            <i class="bi bi-arrow-left"></i> Kembali ke Katalog
        </a>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('marketplace') }}">Katalog</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $produk->name }}</li>
            </ol>
        </nav>
    </div>

    {{-- ================= DETAIL PRODUK ================= --}}
    <div class="pane">
        <div class="row g-4">
            <div class="col-md-5">
                <div class="product-image--square">
                    @if ($produk->image)
                        <img src="{{ asset('storage/' . $produk->image) }}" alt="{{ $produk->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                    @else
                        <i class="bi bi-box-seam"></i>
                    @endif
                </div>
            </div>

            <div class="col-md-7">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge badge-soft badge-soft--neutral">{{ $produk->category?->name ?? '-' }}</span>
                </div>

                <h1 class="h3 mb-1">{{ $produk->name }}</h1>
                <div class="fs-3 fw-bold text-primary mb-3">{{ 'Rp ' . number_format($produk->price, 0, ',', '.') }}</div>

                <p class="text-muted-pos mb-3">{{ $produk->description ?? 'Deskripsi tidak tersedia.' }}</p>

                <div class="mb-4">
                    @php
                        $status = $produk->stock <= 0 ? 'habis' : ($produk->stock <= 10 ? 'menipis' : 'tersedia');
                        $stokLabel = ['tersedia' => 'Tersedia', 'menipis' => 'Stok menipis (' . $produk->stock . ')', 'habis' => 'Habis'];
                        $stokBadge = ['tersedia' => 'badge-soft--success', 'menipis' => 'badge-soft--warning', 'habis' => 'badge-soft--danger'];
                    @endphp
                    <span class="badge badge-soft {{ $stokBadge[$status] }}"><span class="badge-soft__dot"></span>{{ $stokLabel[$status] }}</span>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                    <form method="POST" action="{{ route('pelanggan.cart.add') }}" id="qtyForm" style="display:inline;">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $produk->id }}">
                        <input type="hidden" name="qty" id="qtyInput" value="1">
                        <div class="btn-group" role="group" aria-label="Jumlah produk">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeQty(-1)" aria-label="Kurangi jumlah">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <input type="text" class="form-control text-center input-qty" id="qtyDisplay" value="1" readonly aria-label="Jumlah" style="width:3rem;">
                            <button type="button" class="btn btn-outline-secondary" onclick="changeQty(1)" aria-label="Tambah jumlah">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @auth
                        <form method="POST" action="{{ route('pelanggan.cart.add') }}" style="display:inline;" id="addToCartForm">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $produk->id }}">
                            <button type="submit" class="btn btn-brand btn-lg" id="addToCartBtn" {{ $produk->stock <= 0 ? 'disabled' : '' }}>
                                <i class="bi bi-cart-plus me-1"></i> Tambah ke Keranjang
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-brand btn-lg" onclick="requireLogin('menambahkan item ke keranjang')" {{ $produk->stock <= 0 ? 'disabled' : '' }}>
                            <i class="bi bi-cart-plus me-1"></i> Tambah ke Keranjang
                        </button>
                    @endauth
                    <a href="{{ route('pelanggan.checkout') }}" class="btn btn-outline-primary btn-lg" {{ $produk->stock <= 0 ? 'disabled' : '' }} onclick="if(!{{ Auth::check() ? 'true' : 'false' }}){event.preventDefault();requireLogin('checkout');}">
                        <i class="bi bi-bag-check me-1"></i> Beli Sekarang
                    </a>
                </div>

                <div class="divider"></div>

                <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small text-muted-pos">
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-primary"></i> Estimasi konfirmasi &lt; 15 menit
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-check text-primary"></i> Pesanan menunggu konfirmasi kasir
                    </li>
                </ul>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
        function changeQty(delta) {
            const input = document.getElementById('qtyInput');
            const display = document.getElementById('qtyDisplay');
            let val = parseInt(input.value) + delta;
            if (val < 1) val = 1;
            if (val > {{ $produk->stock }}) val = {{ $produk->stock }};
            input.value = val;
            display.value = val;
        }
        document.getElementById('addToCartForm').addEventListener('submit', function(e) {
            if (! isLoggedIn) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Perlu Login',
                    text: 'Silakan login untuk menambahkan item ke keranjang.',
                    showCancelButton: true,
                    confirmButtonText: 'Login',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route("login") }}';
                    }
                });
                return;
            }
            const form = this;
            const btn = document.getElementById('addToCartBtn');
            btn.disabled = true;
            fetch(form.action, {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
                body: new FormData(form)
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Berhasil ditambahkan ke keranjang!', showConfirmButton: false, timer: 2000, timerProgressBar: true});
                    form.reset();
                    document.getElementById('qtyInput').value = 1;
                    document.getElementById('qtyDisplay').value = 1;
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
@endsection
