const ENDPOINT = '/api/kasir/riwayat';

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
            <tr>
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
        `).join('');
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

async function loadRiwayat() {
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
        const data = payload.data ?? {};

        renderBadges(data.badges ?? {});
        renderStats(data.stat_cards ?? []);
        renderRows(data.rows ?? [], STATUS_BADGE);
        renderPagination(data.summary ?? {}, data.pagination ?? '');
    } catch (error) {
        console.error('Gagal memuat riwayat transaksi:', error);
        renderError();
    }
}

function bootKasirRiwayat() {
    if (!document.getElementById('riwayat-body')) {
        return;
    }

    document.addEventListener('click', (event) => {
        const retry = event.target.closest('[data-riwayat-retry]');
        if (retry) {
            loadRiwayat();
        }
    });

    loadRiwayat();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootKasirRiwayat);
} else {
    bootKasirRiwayat();
}