import NProgress from 'nprogress';
import { getCart } from './cart';

    const grid = document.getElementById('product-grid');

    if (grid) {
        const api = grid.dataset.api;
        const marketplace = grid.dataset.marketplace;
        const filterNav = document.getElementById('katalog-filter');
        const meta = document.getElementById('katalog-meta');
        const pagination = document.getElementById('katalog-pagination');

    const numberFormat = new Intl.NumberFormat('id-ID');
    const stokLabel = { tersedia: 'Tersedia', menipis: 'Stok menipis', habis: 'Habis' };

    let konteks = null;
    let permintaan = 0;

    const escapeHtml = (nilai) => String(nilai ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    }[char]));

    const rupiah = (nilai) => `Rp ${numberFormat.format(Number(nilai) || 0)}`;

    const stokStatus = (stok) => {
        const jumlah = Number(stok) || 0;

        return jumlah <= 0 ? 'habis' : (jumlah <= 10 ? 'menipis' : 'tersedia');
    };

    /* Placeholder per kategori: ikon + gradient supaya tiap kartu terasa beda. */
    const PETAPAN_KATEGORI = [
        { kunci: 'makanan', kelas: 'ph-makanan', ikon: 'bi-egg-fried', warna: '#F97316' },
        { kunci: 'minuman', kelas: 'ph-minuman', ikon: 'bi-cup-straw', warna: '#0EA5E9' },
        { kunci: 'sembako', kelas: 'ph-sembako', ikon: 'bi-basket2', warna: '#84CC16' },
        { kunci: 'snack', kelas: 'ph-snack', ikon: 'bi-cookie', warna: '#EC4899' },
        { kunci: 'bumbu', kelas: 'ph-sembako', ikon: 'bi-mortarboard', warna: '#84CC16' },
    ];

    const metaKategori = (nama) => {
        const judul = String(nama ?? '').toLowerCase();

        return PETAPAN_KATEGORI.find((item) => judul.includes(item.kunci))
            ?? { kelas: '', ikon: 'bi-box-seam', warna: '#64748B' };
    };

    const placeholder = (kategori) => {
        const meta = metaKategori(kategori);

        return `<span class="product-placeholder product-placeholder--lg ${meta.kelas}" style="--ph-gradient: linear-gradient(135deg, ${meta.warna} 0%, ${meta.warna}cc 100%);" role="img" aria-label="Gambar belum tersedia untuk ${escapeHtml(kategori || 'produk')}">
            <i class="bi ${meta.ikon} product-placeholder__icon" aria-hidden="true"></i>
            <span class="product-placeholder__brand">${escapeHtml(kategori || 'Produk')}</span>
        </span>`;
    };

    const paramsHalaman = () => new URLSearchParams(window.location.search);

    const updateUrl = () => grid.dataset.cartUpdate || '';

    /* Baca CartStore (bukan seed mentah) agar sinkron dengan mutasi terbaru. */
    const cartAwal = () => {
        const cart = getCart();

        return (cart && typeof cart === 'object') ? cart : {};
    };

    const skeleton = (jumlah = 8) => Array.from({ length: jumlah }, () => '<div class="col"><div class="skeleton-card"></div></div>').join('');

    /**
     * Stepper memakai `cart-update-form` supaya qty yang dikirim bersifat absolut
     * (endpoint update mengeset qty), bukan menambah seperti endpoint add.
     */
    const stepperMarkup = (produk, nama, url) => `<form method="POST" action="${escapeHtml(url)}" class="cart-update-form card-stepper" data-product-id="${produk.id}" hidden>
        <input type="hidden" name="_token" value="${produk.csrf}">
        <input type="hidden" name="product_id" value="${produk.id}">
        <input type="hidden" name="qty" value="1" data-cart-qty-input>
        <button type="button" class="card-stepper__btn" data-cart-step="-1" aria-label="Kurangi jumlah ${escapeHtml(nama)}">
            <i class="bi bi-dash-lg" aria-hidden="true"></i>
        </button>
        <span class="card-stepper__value" data-stepper-value aria-live="polite">1</span>
        <button type="button" class="card-stepper__btn card-stepper__btn--accent" data-cart-step="1" aria-label="Tambah jumlah ${escapeHtml(nama)}">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
        </button>
    </form>`;

    const kartu = (produk, data) => {
        const status = stokStatus(produk.stock);
        const nama = escapeHtml(produk.name);
        const kategori = produk.category ? escapeHtml(produk.category.name) : '';
        const habis = status === 'habis';
        const gambar = produk.image
            ? `<img src="${escapeHtml(data.urls.storage + '/' + produk.image)}" alt="${nama}" class="product-card__img" loading="lazy" width="320" height="240">`
            : placeholder(produk.category ? produk.category.name : '');

        const eyebrow = `<div class="product-card__eyebrow">
            ${kategori ? `<span class="badge badge-soft badge-soft--neutral badge-sm">${kategori}</span>` : '<span></span>'}
            <span class="stock-badge stock-badge--${status}">${stokLabel[status]}</span>
        </div>`;

        let aksi;
        if (habis) {
            aksi = '<button type="button" class="btn btn-soft btn-sm w-100" disabled><i class="bi bi-cart-x me-1" aria-hidden="true"></i> Stok Habis</button>';
        } else if (! data.is_logged_in) {
            aksi = `<button type="button" class="btn btn-accent btn-sm w-100 d-flex align-items-center justify-content-center gap-1" onclick="requireLogin('menambahkan item ke keranjang')">
                <i class="bi bi-cart-plus" aria-hidden="true"></i> Tambah ke Keranjang
            </button>`;
        } else {
            const url = updateUrl();
            const addForm = `<form method="POST" action="${escapeHtml(data.urls.cart_add)}" class="cart-add-form" data-product-id="${produk.id}" data-stock="${produk.stock}">
                <input type="hidden" name="_token" value="${data.csrf}">
                <input type="hidden" name="product_id" value="${produk.id}">
                <input type="hidden" name="qty" value="1" class="cart-qty-input">
                <button type="submit" class="btn btn-accent btn-sm w-100 d-flex align-items-center justify-content-center gap-1 cart-add-btn">
                    <i class="bi bi-cart-plus" aria-hidden="true"></i> Tambah ke Keranjang
                </button>
            </form>`;

            aksi = url
                ? `${addForm}${stepperMarkup({ ...produk, csrf: data.csrf }, nama, url)}`
                : addForm;
        }

        return `<div class="col" data-cf-row data-cf-name="${escapeHtml(produk.name)}" data-category="${escapeHtml(produk.category_id ?? '')}" data-product-id="${produk.id}" data-stock="${produk.stock}">
            <div class="product-card ${habis ? 'product-card--out' : ''}">
                <div class="product-card__image">
                    ${gambar}
                    ${habis ? '<span class="product-card__out-badge"><i class="bi bi-x-circle" aria-hidden="true"></i> Habis</span>' : ''}
                    ${produk.is_featured ? '<span class="product-card__featured-badge" title="Produk unggulan"><i class="bi bi-star-fill" aria-hidden="true"></i></span>' : ''}
                </div>
                <div class="product-card__body">
                    ${eyebrow}
                    <h3 class="product-card__name">${nama}</h3>
                    <div class="product-card__price">${rupiah(produk.price)}</div>
                    <div class="product-card__actions">
                        <a href="${escapeHtml(data.urls.produk + '/' + produk.id)}" class="stretched-link" aria-label="Lihat detail ${nama}"></a>
                        ${aksi}
                    </div>
                </div>
            </div>
        </div>`;
    };

    /* Selaraskan tombol tambah / stepper dengan isi keranjang saat ini. */
    const syncCart = (cart) => {
        grid.querySelectorAll('.col[data-product-id]').forEach((row) => {
            const addForm = row.querySelector('.cart-add-form');
            const stepForm = row.querySelector('.card-stepper');

            if (! addForm || ! stepForm) {
                return;
            }

            const nilai = stepForm.querySelector('[data-stepper-value]');
            const item = cart[row.dataset.productId];

            if (! item) {
                addForm.hidden = false;
                stepForm.hidden = true;

                return;
            }

            const qty = Math.max(Number(item.qty) || 1, 1);
            const stok = Math.max(Number(row.dataset.stock) || 0, 1);

            addForm.hidden = true;
            stepForm.hidden = false;

            stepForm.querySelector('[data-cart-qty-input]').value = String(qty);
            nilai.textContent = String(qty);

            stepForm.querySelector('[data-cart-step="-1"]').disabled = qty <= 1;
            stepForm.querySelector('[data-cart-step="1"]').disabled = qty >= stok;
        });
    };

    const renderFilter = (params, data) => {
        const kategoriAktif = params.get('category_id');
        const search = params.get('search') || '';
        const semuaAktif = ! kategoriAktif;
        const jumlah = data.kategori_counts || {};

        const itemSemua = `<li class="nav-item">
            <a class="nav-link ${semuaAktif ? 'active' : ''}" href="${escapeHtml(marketplace)}" data-filter="semua" data-cf-field="category_id" data-cf-value="">
                <i class="bi bi-grid" aria-hidden="true"></i> Semua
                <span class="cat-tabs__count">${jumlah.semua ?? 0}</span>
            </a>
        </li>`;

        const itemKategori = (data.kategori || []).map((kategori) => {
            const aktif = String(kategoriAktif) === String(kategori.id);
            const href = `${marketplace}?category_id=${encodeURIComponent(kategori.id)}${search ? `&search=${encodeURIComponent(search)}` : ''}`;

            return `<li class="nav-item">
                <a class="nav-link ${aktif ? 'active' : ''}" href="${escapeHtml(href)}" data-filter="${kategori.id}" data-cf-field="category_id" data-cf-value="${escapeHtml(kategori.id)}" ${aktif ? 'aria-current="page"' : ''}>
                    <i class="bi ${escapeHtml(kategori.icon || 'bi-tag')}" aria-hidden="true"></i>
                    ${escapeHtml(kategori.name)}
                    <span class="cat-tabs__count">${jumlah[kategori.slug] ?? 0}</span>
                </a>
            </li>`;
        }).join('');

        filterNav.innerHTML = itemSemua + itemKategori;
    };

    const renderGrid = (data) => {
        const produk = data.produk.data || [];
        const pesan = escapeHtml(
            paramsHalaman().get('search')
                ? 'Tidak ada produk yang cocok dengan pencarian.'
                : 'Tidak ada produk yang cocok dengan filter.',
        );
        const kartuKosong = `<div class="col-12" data-cf-empty hidden>
            <div class="pane text-center py-5">
                <div class="empty-state__icon"><i class="bi bi-search"></i></div>
                <p class="text-muted-pos mb-0">${pesan}</p>
            </div>
        </div>`;

        grid.innerHTML = (produk.length
            ? produk.map((item) => kartu(item, data)).join('')
            : `<div class="col-12">
                <div class="pane text-center py-5">
                    <div class="empty-state__icon"><i class="bi bi-bag-x"></i></div>
                    <p class="text-muted-pos mb-0">Tidak ada produk ditemukan.</p>
                </div>
            </div>`)
            + (produk.length ? kartuKosong : '');
    };

    const hrefHalaman = (params, halaman) => {
        const query = new URLSearchParams(params);

        if (halaman > 1) {
            query.set('page', halaman);
        } else {
            query.delete('page');
        }

        const terpakai = query.toString();

        return terpakai ? `${marketplace}?${terpakai}` : marketplace;
    };

    const daftarHalaman = (aktif, terakhir) => {
        const kandidat = [...new Set([1, aktif - 1, aktif, aktif + 1, terakhir])]
            .filter((halaman) => halaman >= 1 && halaman <= terakhir)
            .sort((a, b) => a - b);

        const hasil = [];
        let sebelum = 0;

        kandidat.forEach((halaman) => {
            if (sebelum && halaman - sebelum > 1) {
                hasil.push('...');
            }
            hasil.push(halaman);
            sebelum = halaman;
        });

        return hasil;
    };

    const panahKiri = '<svg width="18" height="18" viewBox="0 0 16 16" fill="currentColor" focusable="false" aria-hidden="true"><path d="M11.854 3.646a.5.5 0 0 1 0 .708L6.207 8l5.647 4.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/></svg>';
    const panahKanan = '<svg width="18" height="18" viewBox="0 0 16 16" fill="currentColor" focusable="false" aria-hidden="true"><path d="M4.646 3.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>';

    const renderPagination = (params, data) => {
        const paginator = data.produk;
        const halamanIni = Number(paginator.current_page) || 1;
        const halamanTerakhir = Number(paginator.last_page) || 1;

        meta.textContent = `Menampilkan ${paginator.from ?? 0}-${paginator.to ?? 0} dari ${paginator.total} produk`;

        if (halamanTerakhir <= 1) {
            pagination.innerHTML = '';

            return;
        }

        const tombolSebelumnya = halamanIni > 1
            ? `<li class="page-item"><a class="page-link" href="${escapeHtml(hrefHalaman(params, halamanIni - 1))}" data-page="${halamanIni - 1}" rel="prev" aria-label="Halaman sebelumnya">${panahKiri}</a></li>`
            : `<li class="page-item disabled"><span class="page-link" aria-hidden="true">${panahKiri}</span></li>`;

        const tombolHalaman = daftarHalaman(halamanIni, halamanTerakhir).map((halaman) => {
            if (halaman === '...') {
                return '<li class="page-item disabled" aria-disabled="true"><span class="page-link">...</span></li>';
            }

            const aktif = halaman === halamanIni;

            return `<li class="page-item ${aktif ? 'active' : ''}" ${aktif ? 'aria-current="page"' : ''}>
                <a class="page-link" href="${escapeHtml(hrefHalaman(params, halaman))}" data-page="${halaman}" aria-label="Halaman ${halaman}">${halaman}</a>
            </li>`;
        }).join('');

        const tombolBerikutnya = halamanIni < halamanTerakhir
            ? `<li class="page-item"><a class="page-link" href="${escapeHtml(hrefHalaman(params, halamanIni + 1))}" data-page="${halamanIni + 1}" rel="next" aria-label="Halaman berikutnya">${panahKanan}</a></li>`
            : `<li class="page-item disabled"><span class="page-link" aria-hidden="true">${panahKanan}</span></li>`;

        pagination.innerHTML = `<ul class="pagination pagination-arrow mb-0">${tombolSebelumnya}${tombolHalaman}${tombolBerikutnya}</ul>`;
    };

    const renderGagal = () => {
        grid.innerHTML = '<div class="col-12"><div class="pane text-center py-4"><p class="text-muted-pos">Gagal memuat produk. Muat ulang halaman untuk mencoba lagi.</p></div></div>';
        meta.textContent = '';
        pagination.innerHTML = '';
    };

    const muat = async (params, tampilProgress = true) => {
        const id = ++permintaan;
        grid.innerHTML = skeleton();

        const kueri = new URLSearchParams();
        const halaman = params.get('page');

        if (halaman) {
            kueri.set('page', halaman);
        }

        if (tampilProgress) {
            NProgress.start();
        }

        try {
            const respons = await fetch(`${api}?${kueri.toString()}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (! respons.ok) {
                throw new Error(`HTTP ${respons.status}`);
            }

            const data = await respons.json();

            if (id !== permintaan) {
                return;
            }

            konteks = data;
            renderFilter(params, data);
            renderGrid(data);
            syncCart(cartAwal());
            renderPagination(params, data);
            document.dispatchEvent(new CustomEvent('cf:refresh'));
        } catch (error) {
            if (id !== permintaan) {
                return;
            }

            console.error('Gagal memuat katalog:', error);
            renderGagal();
        } finally {
            if (id === permintaan && tampilProgress) {
                NProgress.done();
            }
        }
    };

    const navigasi = (params) => {
        const query = params.toString();

        window.history.pushState(null, '', query ? `?${query}` : window.location.pathname);
        muat(params);
    };

    pagination.addEventListener('click', (event) => {
        const tautan = event.target.closest('[data-page]');

        if (! tautan) {
            return;
        }

        event.preventDefault();

        const params = paramsHalaman();

        params.set('page', tautan.dataset.page);
        navigasi(params);
    });

    /* Tampilkan stepper begitu produk masuk keranjang, dan kembali ke tombol
       tambah bila item dihapus dari halaman keranjang. */
    document.addEventListener('cart:updated', (event) => {
        syncCart(event.detail?.cart || {});
    });

    window.addEventListener('popstate', () => {
        muat(paramsHalaman());
    });

    muat(paramsHalaman(), false);
}
