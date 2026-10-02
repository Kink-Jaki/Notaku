const ENDPOINT = '/api/kasir/riwayat';
const DEBOUNCE_MS = 300;

let urutanMuat = 0;
let jedaKetik = null;
let statistikServer = null;

const STATUS_BADGE = {
    selesai: 'success',
    dibatalkan: 'danger',
};

const EMPTY_STATE = `
    <tr>
        <td colspan="9">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-inbox"></i></span>
                <div class="empty-state__title">Belum ada transaksi</div>
                <div class="empty-state__text">Tidak ada transaksi pada filter yang dipilih.</div>
            </div>
        </td>
    </tr>
`;

const EMPTY_FILTER_STATE = `
    <tr data-cf-empty hidden>
        <td colspan="9">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-search"></i></span>
                <div class="empty-state__title">Tidak ada transaksi yang cocok</div>
                <div class="empty-state__text">Coba ubah kata kunci atau filter di atas.</div>
            </div>
        </td>
    </tr>
`;

const ERROR_STATE = `
    <div class="empty-state">
        <span class="empty-state__icon"><i class="bi bi-exclamation-triangle"></i></span>
        <div class="empty-state__title">Data gagal dimuat</div>
        <div class="empty-state__text">Periksa koneksi lalu coba lagi.</div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-riwayat-retry>
            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
        </button>
    </div>
`;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&',
        '<': '<',
        '>': '>',
        '"': '"',
        "'": '&#039;',
    })[char]);
}

function formatNumber(value) {
    return Number(value ?? 0).toLocaleString('id-ID');
}

function formatRupiah(value) {
    return `Rp ${formatNumber(value)}`;
}

function renderBadges(badges) {
    const transaksiEl = document.getElementById('riwayat-badge-transaksi');
    const penjualanEl = document.getElementById('riwayat-badge-penjualan');

    if (transaksiEl) transaksiEl.textContent = badges.total_transaksi ?? '—';
    if (penjualanEl) penjualanEl.textContent = badges.total_penjualan ?? '—';
}

function renderStats(stats) {
    const container = document.getElementById('riwayat-stats');

    container.innerHTML = stats.map((stat) => `
        <div class="col-12 col-md-4">
            <div class="stat-card stat-card--${escapeHtml(stat.modifier)}">
                <div>
                    <div class="stat-card__label">${escapeHtml(stat.label)}</div>
                    <div class="stat-card__value">${escapeHtml(stat.value)}</div>
                </div>
                <div class="stat-card__icon">
                    <i class="bi ${escapeHtml(stat.icon)}"></i>
                </div>
            </div>
        </div>
    `).join('');

    container.setAttribute('aria-busy', 'false');
}

function renderRows(rows, statusBadge) {
    const body = document.getElementById('riwayat-body');

    if (rows.length === 0) {
        body.innerHTML = EMPTY_STATE;
    } else {
        body.innerHTML = rows.map((row) => `
            <tr data-cf-row
                data-cf-name="${escapeHtml(`${row.transaction_number} ${row.order_number ?? ''}`)}"
                data-jenis="${escapeHtml(row.jenis)}"
                data-status="${escapeHtml(row.status ?? 'selesai')}"
                data-total="${escapeHtml(row.total)}">
                <td>
                    <a href="${escapeHtml(row.detail_url)}" class="fw-semibold font-monospace">
                        ${escapeHtml(row.transaction_number)}
                    </a>
                    ${row.order_number ? `<div class="small text-muted-pos font-monospace">${escapeHtml(row.order_number)}</div>` : ''}
                </td>
                <td class="text-nowrap">
                    <div>${escapeHtml(row.tanggal)}</div>
                    <div class="small text-muted-pos">${escapeHtml(row.jam)}</div>
                </td>
                <td class="text-nowrap">${escapeHtml(row.kasir ?? '-')}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${escapeHtml(row.jenis_badge)}">
                        ${escapeHtml(row.jenis)}
                    </span>
                </td>
                <td class="text-nowrap">${escapeHtml(row.metode)}</td>
                <td class="text-nowrap">${escapeHtml(row.item)} item</td>
                <td class="fw-semibold text-nowrap">${formatRupiah(row.total)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${escapeHtml(statusBadge[row.status] ?? 'neutral')}">
                        <span class="badge-soft__dot"></span>${escapeHtml(row.status ? row.status.charAt(0).toUpperCase() + row.status.slice(1) : '-')}
                    </span>
                </td>
                <td class="text-end">
                    <a href="${escapeHtml(row.detail_url)}" class="link-secondary" title="Lihat detail transaksi">
                        <i class="bi bi-receipt"></i>
                    </a>
                </td>
            </tr>
        `).join('') + EMPTY_FILTER_STATE;
    }

    body.setAttribute('aria-busy', 'false');
}

function renderPagination(summary, paginationHtml) {
    const info = document.getElementById('riwayat-info');
    const nav = document.getElementById('riwayat-pagination');

    info.textContent = `Menampilkan ${summary.first ?? 0}–${summary.last ?? 0} dari ${summary.total ?? 0} transaksi`;
    nav.innerHTML = paginationHtml ?? '';
}

function renderError() {
    document.getElementById('riwayat-stats').innerHTML = `<div class="col-12">${ERROR_STATE}</div>`;
    document.getElementById('riwayat-body').innerHTML = EMPTY_STATE;
    document.getElementById('riwayat-info').textContent = '';
    document.getElementById('riwayat-pagination').innerHTML = '';

    [document.getElementById('riwayat-stats'), document.getElementById('riwayat-body')]
        .forEach((el) => el?.setAttribute('aria-busy', 'false'));
}

function setLoading(loading) {
    ['riwayat-stats', 'riwayat-body', 'riwayat-pagination', 'riwayat-info']
        .forEach((id) => {
            const el = document.getElementById(id);

            if (el) {
                el.style.opacity = loading ? '0.5' : '';
                el.style.pointerEvents = loading ? 'none' : '';
            }
        });
}

async function loadRiwayat() {
    const id = ++urutanMuat;

    setLoading(true);

    try {
        const url = ENDPOINT + window.location.search;
        const response = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const payload = await response.json();

        if (id !== urutanMuat) {
            return;
        }

        const data = payload.data ?? {};

        statistikServer = {
            badges: data.badges ?? {},
            stat_cards: data.stat_cards ?? [],
        };

        renderBadges(data.badges ?? {});
        renderStats(data.stat_cards ?? []);
        renderRows(data.rows ?? [], STATUS_BADGE);
        renderPagination(data.summary ?? {}, data.pagination ?? '');
        document.dispatchEvent(new CustomEvent('cf:refresh'));
    } catch (error) {
        if (id !== urutanMuat) {
            return;
        }

        console.error('Gagal memuat riwayat transaksi:', error);
        renderError();
    } finally {
        if (id === urutanMuat) {
            setLoading(false);
        }
    }
}

function filterParams() {
    const form = document.getElementById('riwayat-filter');
    const params = new URLSearchParams();

    if (form) {
        new FormData(form).forEach((value, key) => {
            if (value !== '') {
                params.set(key, value);
            }
        });
    }

    params.delete('page');

    return params;
}

function navigasiRiwayat(params) {
    const query = params.toString();

    window.clearTimeout(jedaKetik);
    window.history.pushState({ riwayat: true }, '', query ? `?${query}` : window.location.pathname);
    sinkronFormRiwayat();
    sinkronTombolCepat(params);
    loadRiwayat();
}

function sinkronFormRiwayat() {
    const params = new URLSearchParams(window.location.search);

    document.querySelectorAll('#riwayat-filter [name]').forEach((control) => {
        if (control === document.activeElement) {
            return;
        }

        control.value = params.get(control.name) ?? control.dataset.rtInitial ?? '';
    });
}

function sinkronTombolCepat(params) {
    const dari = params.get('dari');

    document.querySelectorAll('.btn-group[role="group"] a').forEach((tautan) => {
        const dariTautan = new URL(tautan.href, window.location.origin).searchParams.get('dari');

        tautan.classList.toggle('active', Boolean(dari) && dariTautan === dari);
    });
}

function bootKasirRiwayat() {
    if (!document.getElementById('riwayat-body')) {
        return;
    }

    document.querySelectorAll('#riwayat-filter [name]').forEach((control) => {
        control.dataset.rtInitial = control.value;
    });

    document.addEventListener('click', (event) => {
        const retry = event.target.closest('[data-riwayat-retry]');
        if (retry) {
            loadRiwayat();
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('#riwayat-filter');

        if (! form) {
            return;
        }

        event.preventDefault();
        navigasiRiwayat(filterParams());
    }, true);

    document.addEventListener('input', (event) => {
        if (! event.target.matches('#riwayat-filter input[type="date"]')) {
            return;
        }

        window.clearTimeout(jedaKetik);
        jedaKetik = window.setTimeout(() => navigasiRiwayat(filterParams()), DEBOUNCE_MS);
    }, true);

    document.addEventListener('change', (event) => {
        if (! event.target.matches('#riwayat-filter input[type="date"]')) {
            return;
        }

        navigasiRiwayat(filterParams());
    }, true);

    document.addEventListener('click', (event) => {
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const tautan = event.target.closest(
            '.btn-group[role="group"] a[href], #riwayat-pagination a[href], a[data-rt-link]'
        );

        if (! tautan || tautan.getAttribute('href').startsWith('#') || tautan.hasAttribute('data-no-rt')) {
            return;
        }

        event.preventDefault();
        navigasiRiwayat(new URL(tautan.href, window.location.origin).searchParams);
    }, true);

    window.addEventListener('popstate', () => {
        sinkronFormRiwayat();
        sinkronTombolCepat(new URLSearchParams(window.location.search));
        loadRiwayat();
    });

    document.addEventListener('cf:applied', (event) => {
        if (! statistikServer || event.detail?.kunci !== 'riwayat') {
            return;
        }

        if (! event.detail.adaFilter) {
            renderBadges(statistikServer.badges);
            renderStats(statistikServer.stat_cards);

            return;
        }

        const baris = event.detail.baris;
        const jumlah = baris.length;
        const penjualan = baris.reduce((total, row) => total + (Number(row.dataset.total) || 0), 0);

        renderBadges({
            total_transaksi: formatNumber(jumlah),
            total_penjualan: formatRupiah(penjualan),
        });

        renderStats([
            { label: 'Total Transaksi', value: formatNumber(jumlah), icon: 'bi-receipt', modifier: 'primary' },
            { label: 'Total Penjualan', value: formatRupiah(penjualan), icon: 'bi-cash-stack', modifier: 'success' },
            { label: 'Rata-rata Transaksi', value: formatRupiah(jumlah > 0 ? Math.round(penjualan / jumlah) : 0), icon: 'bi-graph-up', modifier: 'info' },
        ]);
    });

    loadRiwayat();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootKasirRiwayat);
} else {
    bootKasirRiwayat();
}