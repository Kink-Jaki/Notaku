const grid = document.getElementById('product-grid');

if (grid) {
    const api = grid.dataset.api;
    const marketplace = grid.dataset.marketplace;
    const filterNav = document.getElementById('katalog-filter');
    const meta = document.getElementById('katalog-meta');
    const pagination = document.getElementById('katalog-pagination');
    const searchForm = document.getElementById('katalog-search-form');
    const searchInput = searchForm ? searchForm.querySelector('input[name="search"]') : null;

    const numberFormat = new Intl.NumberFormat('id-ID');
    const stokLabel = { tersedia: 'Tersedia', menipis: 'Stok menipis', habis: 'Habis' };
    const stokBadge = { tersedia: 'badge-soft--success', menipis: 'badge-soft--warning', habis: 'badge-soft--danger' };

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

    const paramsHalaman = () => new URLSearchParams(window.location.search);

    const skeleton = (jumlah = 8) => Array.from({ length: jumlah }, () => '<div class="col"><div class="skeleton-card"></div></div>').join('');

    const kartu = (produk, data) => {
        const status = stokStatus(produk.stock);
        const nama = escapeHtml(produk.name);
        const kategori = produk.category ? escapeHtml(produk.category.name) : '-';
        const gambar = produk.image
            ? `<img src="${escapeHtml(data.urls.storage + '/' + produk.image)}" alt="${nama}" class="product-card__img" style="width:100%;height:160px;object-fit:cover;">`
            : '<i class="bi bi-box-seam"></i>';

        let aksi;
        if (status === 'habis') {
            aksi = '<button type="button" class="btn btn-brand btn-sm w-100" disabled><i class="bi bi-cart-plus me-1"></i> Habis</button>';
        } else if (data.is_logged_in) {
            aksi = `<form method="POST" action="${escapeHtml(data.urls.cart_add)}" class="cart-add-form" style="display:inline;" data-product-id="${produk.id}" data-stock="${produk.stock}">
                <input type="hidden" name="_token" value="${data.csrf}">
                <input type="hidden" name="product_id" value="${produk.id}">
                <input type="hidden" name="qty" value="1" class="cart-qty-input">
                <button type="submit" class="btn btn-brand btn-sm w-100 d-flex align-items-center justify-content-center cart-add-btn">
                    <i class="bi bi-cart-plus me-1"></i> Tambah ke Keranjang
                </button>
            </form>`;
        } else {
            aksi = `<button type="button" class="btn btn-brand btn-sm w-100 d-flex align-items-center justify-content-center" onclick="requireLogin('menambahkan item ke keranjang')">
                <i class="bi bi-cart-plus me-1"></i> Tambah ke Keranjang
            </button>`;
        }

        return `<div class="col">
            <div class="product-card ${status === 'habis' ? 'product-card--out' : ''}" style="position:relative;display:flex;flex-direction:column;height:100%;">
                <div class="product-card__image">
                    ${gambar}
                    ${status === 'habis' ? '<span class="product-card__out-badge">Habis</span>' : ''}
                </div>
                <div class="product-card__body d-flex flex-column gap-1 flex-grow-1">
                    <div class="product-card__name">${nama}</div>
                    <div class="product-card__meta">
                        <span class="badge badge-soft badge-soft--neutral">${kategori}</span>
                    </div>
                    <div class="product-card__price">${rupiah(produk.price)}</div>
                    <div class="small mt-auto pt-1">
                        <span class="badge badge-soft ${stokBadge[status]}"><span class="badge-soft__dot"></span>${stokLabel[status]}</span>
                    </div>
                    <div class="mt-2">
                        <a href="${escapeHtml(data.urls.produk + '/' + produk.id)}" class="stretched-link" aria-label="Lihat detail ${nama}"></a>
                        ${aksi}
                    </div>
                </div>
            </div>
        </div>`;
    };

    const renderFilter = (params, data) => {
        const kategoriAktif = params.get('category_id');
        const search = params.get('search') || '';
        const semuaAktif = ! kategoriAktif && ! search;
        const jumlah = data.kategori_counts || {};

        const itemSemua = `<li class="nav-item">
            <a class="nav-link ${semuaAktif ? 'active' : ''}" href="${escapeHtml(marketplace)}" data-filter="semua">
                <i class="bi bi-grid me-1"></i> Semua
                <span class="badge badge-soft badge-soft--neutral badge-soft--count ms-1">${jumlah.semua ?? 0}</span>
            </a>
        </li>`;

        const itemKategori = (data.kategori || []).map((kategori) => {
            const aktif = String(kategoriAktif) === String(kategori.id);
            const href = `${marketplace}?category_id=${encodeURIComponent(kategori.id)}${search ? `&search=${encodeURIComponent(search)}` : ''}`;

            return `<li class="nav-item">
                <a class="nav-link ${aktif ? 'active' : ''}" href="${escapeHtml(href)}" data-filter="${kategori.id}">
                    <i class="bi ${escapeHtml(kategori.icon || '')} me-1"></i>
                    ${escapeHtml(kategori.name)}
                    <span class="badge badge-soft badge-soft--neutral badge-soft--count ms-1">${jumlah[kategori.slug] ?? 0}</span>
                </a>
            </li>`;
        }).join('');

        filterNav.innerHTML = itemSemua + itemKategori;
    };

    const renderGrid = (data) => {
        const produk = data.produk.data || [];

        grid.innerHTML = produk.length
            ? produk.map((item) => kartu(item, data)).join('')
            : '<div class="col-12"><div class="pane text-center py-4"><p class="text-muted-pos">Tidak ada produk ditemukan.</p></div></div>';
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

        pagination.innerHTML = `<ul class="pagination mb-0">${tombolSebelumnya}${tombolHalaman}${tombolBerikutnya}</ul>`;
    };

    const renderGagal = () => {
        grid.innerHTML = '<div class="col-12"><div class="pane text-center py-4"><p class="text-muted-pos">Gagal memuat produk. Muat ulang halaman untuk mencoba lagi.</p></div></div>';
        meta.textContent = '';
        pagination.innerHTML = '';
    };

    const muat = async (params) => {
        const id = ++permintaan;
        grid.innerHTML = skeleton();

        try {
            const respons = await fetch(`${api}?${params.toString()}`, { headers: { Accept: 'application/json' } });

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
            renderPagination(params, data);
        } catch (error) {
            if (id !== permintaan) {
                return;
            }

            console.error('Gagal memuat katalog:', error);
            renderGagal();
        }
    };

    const navigasi = (params) => {
        const query = params.toString();

        window.history.pushState(null, '', query ? `?${query}` : window.location.pathname);
        muat(params);
    };

    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const params = new URLSearchParams();
        const kata = searchInput.value.trim();

        if (kata) {
            params.set('search', kata);
        }

        navigasi(params);
    });

    filterNav.addEventListener('click', (event) => {
        const tautan = event.target.closest('[data-filter]');

        if (! tautan) {
            return;
        }

        event.preventDefault();

        const filter = tautan.dataset.filter;

        if (filter === 'semua') {
            navigasi(new URLSearchParams());

            return;
        }

        const params = new URLSearchParams();
        const kata = searchInput ? searchInput.value.trim() : '';

        params.set('category_id', filter);

        if (kata) {
            params.set('search', kata);
        }

        navigasi(params);
    });

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

    window.addEventListener('popstate', () => {
        const params = paramsHalaman();

        if (searchInput) {
            searchInput.value = params.get('search') || '';
        }

        muat(params);
    });

    muat(paramsHalaman());
}
