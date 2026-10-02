@extends('layouts.kasir')

@section('title', 'Kasir / POS')
@section('page_title', 'Kasir / POS')

@section('content')
    @php
        $rp = fn ($value) => 'Rp ' . number_format($value, 0, ',', '.');
    @endphp

    <div class="pos-screen">
        <div class="pos-screen__pane">
            <div class="pane__header">
                <h2 class="pane__title h5">Pilih Produk</h2>
                <span class="badge badge-soft badge-soft--neutral rounded-pill" id="posProdukCount">{{ $produk->count() }} produk</span>
            </div>

            <div class="search-box mb-3">
                <i class="bi bi-search search-box__icon"></i>
                <input type="search" id="posSearch" class="form-control" placeholder="Cari produk / kode barcode..." aria-label="Cari produk atau kode barcode" autocomplete="off">
            </div>

            <ul class="nav nav-pills gap-1 mb-3 flex-nowrap overflow-x-auto pb-1" id="posKategori">
                @foreach ($kategoriList as $kat)
                    <li class="nav-item flex-shrink-0">
                        <a href="#" class="nav-link {{ $kat['active'] ? 'active' : '' }}" data-kategori="{{ $kat['label'] }}">
                            {{ $kat['label'] }}
                            <span class="badge rounded-pill ms-1 {{ $kat['active'] ? 'bg-white text-primary' : 'bg-body-secondary text-body' }}">
                                {{ $kat['count'] }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="scroll-area pos-product-list">
                <div class="row g-2" id="productGrid">
                    @foreach ($produk as $p)
                        <div class="col-6 col-md-4 col-xl-3" data-pos-item data-nama="{{ mb_strtolower($p['nama']) }}" data-kategori="{{ $p['kategori'] }}">
                            @if ($p['stok'] > 0)
                                <button
                                    type="button"
                                    class="pos-tile pos-tile--grid w-100 text-start"
                                    data-add-to-cart
                                    data-id="{{ $p['id'] }}"
                                    data-name="{{ $p['nama'] }}"
                                    data-price="{{ $p['harga'] }}"
                                    data-stock="{{ $p['stok'] }}"
                                >
                                    <span class="pos-tile__image">{{ mb_substr($p['nama'], 0, 1) }}</span>
                                    <span class="pos-tile__name">{{ $p['nama'] }}</span>
                                    <span class="pos-tile__footer">
                                        <span class="pos-tile__price">{{ $rp($p['harga']) }}</span>
                                        <span class="badge badge-soft badge-soft--neutral" data-stock-badge>Stok {{ $p['stok'] }}</span>
                                    </span>
                                </button>
                            @else
                                <button type="button" class="pos-tile pos-tile--grid pos-tile--out w-100 text-start" data-add-to-cart data-id="{{ $p['id'] }}" data-name="{{ $p['nama'] }}" data-price="{{ $p['harga'] }}" data-stock="0" disabled>
                                    <span class="pos-tile__image">{{ mb_substr($p['nama'], 0, 1) }}</span>
                                    <span class="pos-tile__name">{{ $p['nama'] }}</span>
                                    <span class="pos-tile__footer">
                                        <span class="pos-tile__price">{{ $rp($p['harga']) }}</span>
                                        <span class="badge badge-soft badge-soft--danger" data-stock-badge>Habis</span>
                                    </span>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div id="posKosong" class="text-center py-4" hidden>
                    <p class="text-muted-pos mb-0">Tidak ada produk yang cocok dengan pencarian.</p>
                </div>
            </div>

            <p class="pos-screen__footer-note small text-muted-pos mb-0">
                <i class="bi bi-info-circle me-1"></i> Klik produk untuk menambahkannya ke keranjang (tersimpan di halaman ini).
            </p>
        </div>

        <div class="pos-screen__pane">
            <div class="pane__header">
                <h2 class="pane__title h5">Keranjang</h2>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-soft badge-soft--neutral rounded-pill" id="cartItemCount">0 item</span>
                    <button type="button" class="btn btn-link btn-sm link-secondary p-0" id="btnClearCart" disabled>
                        <i class="bi bi-trash me-1"></i> Kosongkan
                    </button>
                </div>
            </div>

            <div class="scroll-area mb-3" id="cartList"></div>

            <section aria-label="Kode promo" class="pb-3">
                <label class="form-label" for="kodePromo">Kode Promo (manual)</label>
                <form id="promoForm" class="input-group input-group-sm mb-2">
                    @csrf
                    <input type="text" id="kodePromo" name="code" class="form-control text-uppercase" placeholder="Masukkan kode promo" autocomplete="off">
                    <button type="submit" class="btn btn-outline-primary">Terapkan</button>
                </form>
                <div id="promoActive" class="note-box note-box--success" hidden>
                    <i class="bi bi-tag note-box__icon"></i>
                    <span class="flex-grow-1"><span id="promoCodeLabel"></span> &mdash; Diskon <span id="promoDiscLabel"></span></span>
                    <button type="button" class="link-secondary small fw-semibold bg-transparent border-0" id="btnRemovePromo">Hapus</button>
                </div>
            </section>

            <div class="order-summary">
                <div class="order-summary__row">
                    <span>Subtotal</span>
                    <span id="sumSubtotal">{{ $rp(0) }}</span>
                </div>
                <div class="order-summary__row">
                    <span>Diskon</span>
                    <span class="order-summary__disc" id="sumDiskon">&#8722;{{ $rp(0) }}</span>
                </div>
                <div class="order-summary__row order-summary__row--total">
                    <span>Total</span>
                    <span id="sumTotal">{{ $rp(0) }}</span>
                </div>
            </div>

            <div class="mt-3">
                <form method="POST" action="{{ route('kasir.pos.order') }}" id="orderForm">
                    @csrf
                    <select name="payment_method" class="form-select mb-2" required>
                        @foreach ($paymentMethods as $method => $label)
                            <option value="{{ $method }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="promo_code" value="">
                    <button type="submit" class="btn btn-brand btn-lg w-100" id="btnOrder">
                        <i class="bi bi-bag-check me-2"></i> Order
                    </button>
                </form>
                <p class="small text-muted-pos text-center mt-2 mb-0">
                    Keranjang disimpan di browser. Data baru dikirim ke database saat tombol <strong>Order</strong> diklik.
                </p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cartList = document.getElementById('cartList');
            const cartItemCount = document.getElementById('cartItemCount');
            const btnClearCart = document.getElementById('btnClearCart');
            const productGrid = document.getElementById('productGrid');
            const sumSubtotal = document.getElementById('sumSubtotal');
            const sumDiskon = document.getElementById('sumDiskon');
            const sumTotal = document.getElementById('sumTotal');
            const promoForm = document.getElementById('promoForm');
            const promoActive = document.getElementById('promoActive');
            const promoCodeLabel = document.getElementById('promoCodeLabel');
            const promoDiscLabel = document.getElementById('promoDiscLabel');
            const btnRemovePromo = document.getElementById('btnRemovePromo');
            const orderForm = document.getElementById('orderForm');
            const btnOrder = document.getElementById('btnOrder');

            const orderButtonLabel = btnOrder.innerHTML;
            const emptyCartHtml = `
                <div class="empty-state">
                    <span class="empty-state__icon"><i class="bi bi-cart"></i></span>
                    <div class="empty-state__title">Keranjang kosong</div>
                    <div class="empty-state__text">Pilih produk untuk mulai bertransaksi.</div>
                </div>`;

            /* cart: Map<number, { id, name, price, stock, qty }> */
            const cart = new Map();
            /* promo: { code, type, value, min_order } | null */
            let promo = null;

            const rp = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');
            const escapeHtml = (value) => String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            function subtotal() {
                let sum = 0;
                cart.forEach((item) => { sum += item.price * item.qty; });

                return sum;
            }

            function discount() {
                const sub = subtotal();
                if (!promo || sub < promo.min_order) {
                    return 0;
                }

                return promo.type === 'fixed'
                    ? Math.min(promo.value, sub)
                    : Math.round(sub * promo.value / 100);
            }

            function itemCount() {
                let count = 0;
                cart.forEach((item) => { count += item.qty; });

                return count;
            }

            function cartItemHtml(item) {
                return `
                    <div class="cart-item">
                        <div class="cart-item__info">
                            <div class="cart-item__name">${escapeHtml(item.name)}</div>
                            <div class="cart-item__meta">${rp(item.price)} &times; ${item.qty}</div>
                        </div>
                        <div class="btn-group btn-group-sm flex-shrink-0" role="group" aria-label="Ubah jumlah ${escapeHtml(item.name)}">
                            <button type="button" class="btn btn-outline-secondary" data-cart-action="dec" data-id="${item.id}">&minus;</button>
                            <span class="btn btn-outline-secondary disabled px-2">${item.qty}</span>
                            <button type="button" class="btn btn-outline-secondary" data-cart-action="inc" data-id="${item.id}">+</button>
                        </div>
                        <span class="cart-item__subtotal">${rp(item.price * item.qty)}</span>
                        <button type="button" class="btn btn-link btn-sm link-secondary p-0" data-cart-action="remove" data-id="${item.id}" aria-label="Hapus ${escapeHtml(item.name)}">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>`;
            }

            function render() {
                if (cart.size === 0) {
                    cartList.innerHTML = emptyCartHtml;
                } else {
                    let html = '';
                    cart.forEach((item) => { html += cartItemHtml(item); });
                    cartList.innerHTML = html;
                }

                const sub = subtotal();
                const disc = discount();

                cartItemCount.textContent = itemCount() + ' item';
                btnClearCart.disabled = cart.size === 0;
                sumSubtotal.textContent = rp(sub);
                sumDiskon.textContent = '−' + rp(disc);
                sumTotal.textContent = rp(sub - disc);

                promoActive.hidden = promo === null;
                if (promo) {
                    promoCodeLabel.textContent = promo.code;
                    promoDiscLabel.textContent = rp(disc);
                }
            }

            function applyStocks(stocks) {
                if (!Array.isArray(stocks)) {
                    return;
                }

                stocks.forEach((entry) => {
                    const tile = productGrid.querySelector(`[data-add-to-cart][data-id="${entry.id}"]`);
                    if (tile) {
                        tile.dataset.stock = entry.stock;
                        const badge = tile.querySelector('[data-stock-badge]');
                        if (entry.stock <= 0) {
                            tile.disabled = true;
                            tile.classList.add('pos-tile--out');
                            if (badge) {
                                badge.textContent = 'Habis';
                                badge.classList.remove('badge-soft--neutral');
                                badge.classList.add('badge-soft--danger');
                            }
                        } else {
                            tile.disabled = false;
                            tile.classList.remove('pos-tile--out');
                            if (badge) {
                                badge.textContent = 'Stok ' + entry.stock;
                                badge.classList.remove('badge-soft--danger');
                                badge.classList.add('badge-soft--neutral');
                            }
                        }
                    }

                    const item = cart.get(entry.id);
                    if (item) {
                        item.stock = entry.stock;
                        if (entry.stock <= 0) {
                            cart.delete(entry.id);
                        } else if (item.qty > entry.stock) {
                            item.qty = entry.stock;
                        }
                    }
                });
            }

            function toast(title, icon) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: icon,
                    title: title,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
            }

            productGrid.addEventListener('click', function (event) {
                const tile = event.target.closest('[data-add-to-cart]');
                if (!tile || tile.disabled) {
                    return;
                }

                const id = Number(tile.dataset.id);
                const stock = Number(tile.dataset.stock);
                const existing = cart.get(id);

                if (existing && existing.qty + 1 > stock) {
                    toast('Stok ' + tile.dataset.name + ' tidak mencukupi.', 'warning');
                    return;
                }

                if (existing) {
                    existing.qty += 1;
                } else {
                    cart.set(id, {
                        id: id,
                        name: tile.dataset.name,
                        price: Number(tile.dataset.price),
                        stock: stock,
                        qty: 1,
                    });
                }

                render();
            });

            cartList.addEventListener('click', function (event) {
                const button = event.target.closest('[data-cart-action]');
                if (!button) {
                    return;
                }

                const id = Number(button.dataset.id);
                const item = cart.get(id);
                if (!item) {
                    return;
                }

                if (button.dataset.cartAction === 'inc') {
                    if (item.qty + 1 > item.stock) {
                        toast('Stok ' + item.name + ' tidak mencukupi.', 'warning');
                        return;
                    }
                    item.qty += 1;
                } else if (button.dataset.cartAction === 'dec') {
                    item.qty -= 1;
                    if (item.qty <= 0) {
                        cart.delete(id);
                    }
                } else if (button.dataset.cartAction === 'remove') {
                    cart.delete(id);
                }

                render();
            });

            btnClearCart.addEventListener('click', function () {
                cart.clear();
                promo = null;
                render();
            });

            btnRemovePromo.addEventListener('click', function () {
                promo = null;
                render();
            });

            promoForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                const formData = new FormData(promoForm);
                formData.append('subtotal', subtotal());

                try {
                    const response = await fetch('{{ route('kasir.pos.promoValidate') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Promo Tidak Valid',
                            text: data.message || 'Kode promo tidak valid.',
                        });
                        return;
                    }

                    promo = {
                        code: data.code,
                        type: data.type,
                        value: data.value,
                        min_order: data.min_order,
                    };
                    promoForm.reset();
                    render();
                    toast('Promo ' + data.code + ' diterapkan.', 'success');
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Promo Gagal Diperiksa',
                        text: 'Tidak dapat memeriksa promo, coba lagi.',
                    });
                }
            });

            orderForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                if (cart.size === 0) {
                    toast('Keranjang masih kosong.', 'warning');
                    return;
                }

                const formData = new FormData(orderForm);
                formData.set('promo_code', promo ? promo.code : '');
                let index = 0;
                cart.forEach((item) => {
                    formData.append('items[' + index + '][product_id]', item.id);
                    formData.append('items[' + index + '][qty]', item.qty);
                    index += 1;
                });

                btnOrder.disabled = true;
                btnOrder.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

                try {
                    const response = await fetch(orderForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        if (data.code === 'promo_invalid') {
                            promo = null;
                        }
                        applyStocks(data.stocks);
                        render();
                        toast(data.message || 'Order gagal diproses.', 'error');
                        return;
                    }

                    applyStocks(data.stocks);
                    cart.clear();
                    promo = null;
                    render();
                    showSuccess(data);
                } catch (error) {
                    toast('Terjadi kesalahan jaringan, data belum terkirim.', 'error');
                } finally {
                    btnOrder.disabled = false;
                    btnOrder.innerHTML = orderButtonLabel;
                }
            });

            function showSuccess(data) {
                Swal.fire({
                    icon: 'success',
                    title: 'Transaksi Berhasil',
                    html: '<p class="mb-1">Nomor transaksi <strong class="font-monospace">' + escapeHtml(data.transaction_number) + '</strong></p>' +
                        '<p class="mb-0">' + escapeHtml(data.items_count + ' item') + ' &mdash; Total <strong>' + escapeHtml(rp(data.total)) + '</strong></p>',
                    timer: 3500,
                    showConfirmButton: false,
                });
            }

            /* pencarian & filter kategori (client-side) */
            const posSearch = document.getElementById('posSearch');
            const posKategoriNav = document.getElementById('posKategori');
            const posProdukCount = document.getElementById('posProdukCount');
            const posKosong = document.getElementById('posKosong');
            const posItems = Array.from(document.querySelectorAll('[data-pos-item]'));
            const kategoriSemua = posKategoriNav.querySelector('[data-kategori]')?.dataset.kategori || 'Semua';
            let kategoriAktif = kategoriSemua;
            let jedaCariPos = null;

            function filterPos() {
                const kata = (posSearch.value || '').trim().toLowerCase();
                let tampil = 0;

                posItems.forEach((item) => {
                    const cocokKategori = kategoriAktif === kategoriSemua || item.dataset.kategori === kategoriAktif;
                    const cocokKata = !kata || item.dataset.nama.includes(kata);
                    const terlihat = cocokKategori && cocokKata;

                    item.classList.toggle('d-none', !terlihat);

                    if (terlihat) {
                        tampil += 1;
                    }
                });

                posProdukCount.textContent = tampil + ' produk';
                posKosong.hidden = tampil > 0;
            }

            posSearch.addEventListener('input', function () {
                window.clearTimeout(jedaCariPos);
                jedaCariPos = window.setTimeout(filterPos, 300);
            });

            posKategoriNav.addEventListener('click', function (event) {
                const tautan = event.target.closest('[data-kategori]');

                if (!tautan) {
                    return;
                }

                event.preventDefault();
                window.clearTimeout(jedaCariPos);

                kategoriAktif = tautan.dataset.kategori;

                posKategoriNav.querySelectorAll('[data-kategori]').forEach((link) => {
                    const aktif = link.dataset.kategori === kategoriAktif;
                    link.classList.toggle('active', aktif);

                    const badge = link.querySelector('.badge');
                    if (badge) {
                        badge.classList.toggle('bg-white', aktif);
                        badge.classList.toggle('text-primary', aktif);
                        badge.classList.toggle('bg-body-secondary', !aktif);
                        badge.classList.toggle('text-body', !aktif);
                    }
                });

                filterPos();
            });

            render();
        });
    </script>
@endpush
