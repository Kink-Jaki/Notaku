const ENDPOINT = '/api/kasir/laporan-harian';

const SKELETON_IDS = [
    'lh-tanggal-label',
    'lh-badge-penjualan',
    'lh-stats',
    'lh-metode-count',
    'lh-metode-body',
    'lh-metode-foot',
    'lh-rincian-count',
    'lh-rincian-body',
    'lh-rincian-foot',
    'lh-ringkasan-badge',
    'lh-ringkasan-body',
];

const PANE_IDS = ['lh-metode-pane', 'lh-rincian-pane', 'lh-ringkasan-pane'];

const JENIS_BADGE = {
    Kasir: 'success',
    Online: 'info',
};

const EMPTY_METODE = `
    <tr>
        <td colspan="4">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-cash-coin"></i></span>
                <div class="empty-state__title">Belum ada penjualan</div>
                <div class="empty-state__text">Tidak ada transaksi selesai pada filter ini.</div>
            </div>
        </td>
    </tr>
`;

const EMPTY_RINCIAN = `
    <tr>
        <td colspan="5">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-receipt"></i></span>
                <div class="empty-state__title">Belum ada rincian transaksi</div>
                <div class="empty-state__text">Coba ganti tanggal atau pilih kasir lain.</div>
            </div>
        </td>
    </tr>
`;

const ERROR_STATE = `
    <div class="empty-state">
        <span class="empty-state__icon"><i class="bi bi-exclamation-triangle"></i></span>
        <div class="empty-state__title">Data gagal dimuat</div>
        <div class="empty-state__text">Periksa koneksi lalu coba lagi.</div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-laporan-harian-retry>
            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
        </button>
    </div>
`;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[char]);
}

function bootLaporanHarian() {
    const stats = document.getElementById('lh-stats');

    if (! stats) {
        return;
    }

    const kontainer = {};
    const kerangka = {};
    const filter = document.getElementById('lh-filter');
    const inputTanggal = document.getElementById('tanggalLaporan');
    const inputKasir = document.getElementById('kasirFilter');

    SKELETON_IDS.concat(PANE_IDS).forEach((id) => {
        kontainer[id] = document.getElementById(id);
    });

    SKELETON_IDS.forEach((id) => {
        kerangka[id] = kontainer[id].innerHTML;
    });

    let permintaan = 0;

    const bersihkan = (params) => {
        const tanggal = params.get('tanggal');
        const kasir = params.get('kasir_id');

        if (! /^\d{4}-\d{2}-\d{2}$/.test(tanggal || '')) {
            params.delete('tanggal');
        }

        if (kasir && ! /^\d+$/.test(kasir)) {
            params.delete('kasir_id');
        }

        return params;
    };

    const paramsHalaman = () => bersihkan(new URLSearchParams(window.location.search));

    const paramsFilter = () => {
        const params = new URLSearchParams();

        if (inputTanggal.value) {
            params.set('tanggal', inputTanggal.value);
        }

        if (inputKasir.value) {
            params.set('kasir_id', inputKasir.value);
        }

        return bersihkan(params);
    };

    const sinkronFilter = (params) => {
        inputTanggal.value = params.get('tanggal') || inputTanggal.defaultValue;
        inputKasir.value = params.get('kasir_id') || '';
    };

    const tampilkanSkeleton = () => {
        SKELETON_IDS.forEach((id) => {
            kontainer[id].innerHTML = kerangka[id];
            kontainer[id].setAttribute('aria-busy', 'true');
        });

        PANE_IDS.forEach((id) => kontainer[id].setAttribute('aria-busy', 'true'));
    };

    const selesaiMemuat = () => {
        SKELETON_IDS.concat(PANE_IDS).forEach((id) => kontainer[id].setAttribute('aria-busy', 'false'));
    };

    const renderTanggalLabel = (data) => {
        kontainer['lh-tanggal-label'].textContent = data.tanggal_label ?? '';
    };

    const renderBadgePenjualan = (data) => {
        kontainer['lh-badge-penjualan'].innerHTML = `
            <span class="badge badge-soft badge-soft--success fs-6 fw-semibold">
                <i class="bi bi-cash-stack me-1"></i> ${escapeHtml(data.penjualan_harian)} hari ini
            </span>
        `;
    };

    const renderStats = (stats) => {
        kontainer['lh-stats'].innerHTML = stats.map((stat) => `
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card stat-card--${escapeHtml(stat.modifier)}">
                    <div>
                        <div class="stat-card__label">${escapeHtml(stat.label)}</div>
                        <div class="stat-card__value text-truncate" title="${escapeHtml(stat.value)}">${escapeHtml(stat.value)}</div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="bi ${escapeHtml(stat.icon)}"></i>
                    </div>
                </div>
            </div>
        `).join('');
    };

    const renderMetode = (metode) => {
        kontainer['lh-metode-count'].innerHTML =
            `<span class="badge badge-soft badge-soft--neutral">${metode.count} metode</span>`;

        kontainer['lh-metode-body'].innerHTML = metode.rows.length
            ? metode.rows.map((row) => `
                <tr>
                    <td><i class="bi ${escapeHtml(row.icon)} me-2"></i>${escapeHtml(row.nama)}</td>
                    <td class="text-nowrap">${row.trx} trx</td>
                    <td class="text-end fw-semibold text-nowrap">${escapeHtml(row.total_formatted)}</td>
                    <td class="text-end">
                        <span class="badge badge-soft badge-soft--${escapeHtml(row.badge)}">${row.persen}%</span>
                    </td>
                </tr>
            `).join('')
            : EMPTY_METODE;

        kontainer['lh-metode-foot'].innerHTML = metode.rows.length
            ? `<tr class="fw-semibold">
                <td class="border-top">Total</td>
                <td class="border-top text-nowrap">${metode.count} metode</td>
                <td class="border-top text-end text-nowrap">${escapeHtml(metode.total)}</td>
                <td class="border-top text-end">100%</td>
            </tr>`
            : '';
    };

    const renderRincian = (rincian) => {
        kontainer['lh-rincian-count'].innerHTML =
            `<span class="badge badge-soft badge-soft--success">${rincian.count} transaksi</span>`;

        kontainer['lh-rincian-body'].innerHTML = rincian.rows.length
            ? rincian.rows.map((row) => `
                <tr>
                    <td class="text-nowrap">${escapeHtml(row.jam)}</td>
                    <td class="font-monospace text-nowrap">${escapeHtml(row.id)}</td>
                    <td>
                        <span class="badge badge-soft badge-soft--${escapeHtml(JENIS_BADGE[row.jenis] ?? 'neutral')}">
                            ${escapeHtml(row.jenis)}
                        </span>
                    </td>
                    <td class="text-nowrap">${escapeHtml(row.metode)}</td>
                    <td class="text-end fw-semibold text-nowrap">${escapeHtml(row.total_formatted)}</td>
                </tr>
            `).join('')
            : EMPTY_RINCIAN;

        kontainer['lh-rincian-foot'].innerHTML = rincian.rows.length
            ? `<tr class="fw-semibold">
                <td class="border-top" colspan="4">Total</td>
                <td class="border-top text-end text-nowrap">${escapeHtml(rincian.total)}</td>
            </tr>`
            : '';
    };

    const renderRingkasan = (ringkasan) => {
        kontainer['lh-ringkasan-badge'].innerHTML =
            `<span class="badge badge-soft badge-soft--neutral">${escapeHtml(ringkasan.tanggal)}</span>`;

        kontainer['lh-ringkasan-body'].innerHTML = ringkasan.items.map((item) => `
            <div class="col-sm-12 col-md-4">
                <div class="note-box h-100">
                    <i class="bi ${escapeHtml(item.icon)} note-box__icon"></i>
                    <span>
                        <span class="d-block small text-muted-pos">${escapeHtml(item.label)}</span>
                        <span class="fw-semibold text-nowrap">${escapeHtml(item.value)}</span>
                    </span>
                </div>
            </div>
        `).join('');
    };

    const render = (data) => {
        renderTanggalLabel(data);
        renderBadgePenjualan(data);
        renderStats(data.stat_cards ?? []);
        renderMetode(data.metode ?? { count: 0, total: 'Rp 0', rows: [] });
        renderRincian(data.rincian ?? { count: 0, total: 'Rp 0', rows: [] });
        renderRingkasan(data.ringkasan ?? { tanggal: '', items: [] });
        selesaiMemuat();
    };

    const renderError = () => {
        kontainer['lh-tanggal-label'].textContent = '-';
        kontainer['lh-badge-penjualan'].innerHTML = '';
        kontainer['lh-stats'].innerHTML = `<div class="col-12">${ERROR_STATE}</div>`;
        kontainer['lh-metode-count'].innerHTML = '';
        kontainer['lh-metode-body'].innerHTML = `<tr><td colspan="4">${ERROR_STATE}</td></tr>`;
        kontainer['lh-metode-foot'].innerHTML = '';
        kontainer['lh-rincian-count'].innerHTML = '';
        kontainer['lh-rincian-body'].innerHTML = `<tr><td colspan="5">${ERROR_STATE}</td></tr>`;
        kontainer['lh-rincian-foot'].innerHTML = '';
        kontainer['lh-ringkasan-badge'].innerHTML = '';
        kontainer['lh-ringkasan-body'].innerHTML = `<div class="col-12">${ERROR_STATE}</div>`;
        selesaiMemuat();
    };

    const muat = async (params) => {
        const id = ++permintaan;

        tampilkanSkeleton();

        try {
            const respons = await fetch(`${ENDPOINT}?${params.toString()}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            if (! respons.ok) {
                throw new Error(`HTTP ${respons.status}`);
            }

            const payload = await respons.json();

            if (id !== permintaan) {
                return;
            }

            render(payload.data ?? {});
        } catch (error) {
            if (id !== permintaan) {
                return;
            }

            console.error('Gagal memuat laporan harian:', error);
            renderError();
        }
    };

    const navigasi = (params) => {
        const query = params.toString();

        window.history.pushState(null, '', query ? `?${query}` : window.location.pathname);
        muat(params);
    };

    filter.addEventListener('submit', (event) => {
        event.preventDefault();
        navigasi(paramsFilter());
    });

    inputTanggal.addEventListener('change', () => navigasi(paramsFilter()));
    inputKasir.addEventListener('change', () => navigasi(paramsFilter()));

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-laporan-harian-retry]')) {
            muat(paramsFilter());
        }
    });

    window.addEventListener('popstate', () => {
        const params = paramsHalaman();

        sinkronFilter(params);
        muat(params);
    });

    muat(paramsHalaman());
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootLaporanHarian);
} else {
    bootLaporanHarian();
}
