const ENDPOINT = '/api/kasir/dashboard';

const STATUS_BADGE = {
    menunggu: 'warning',
    diproses: 'info',
    selesai: 'success',
    ditolak: 'danger',
};

const EMPTY_TRANSACTIONS = `
    <tr>
        <td colspan="7">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-receipt"></i></span>
                <div class="empty-state__title">Belum ada transaksi hari ini</div>
                <div class="empty-state__text">Mulai transaksi baru dari menu Kasir / POS atau tunggu pesanan online masuk.</div>
            </div>
        </td>
    </tr>
`;

const EMPTY_PENDING_ORDERS = `
    <div class="empty-state">
        <span class="empty-state__icon"><i class="bi bi-check2-circle"></i></span>
        <div class="empty-state__title">Tidak ada pesanan menunggu</div>
        <div class="empty-state__text">Semua pesanan masuk sudah diatasi.</div>
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

function formatNumber(value) {
    return Number(value ?? 0).toLocaleString('id-ID');
}

function formatRupiah(value) {
    return `Rp ${formatNumber(value)}`;
}

function renderStats(stats) {
    const container = document.getElementById('kasir-stats');

    container.innerHTML = stats.map((stat) => `
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card stat-card--${escapeHtml(stat.modifier)}">
                <div>
                    <div class="stat-card__label">${escapeHtml(stat.label)}</div>
                    <div class="stat-card__value">${escapeHtml(stat.value)}</div>
                    ${stat.delta ? `<span class="stat-card__delta ${escapeHtml(stat.delta_modifier ?? '')}">${escapeHtml(stat.delta)}</span>` : ''}
                    ${stat.link ? `<a href="${escapeHtml(stat.link)}" class="d-block small fw-semibold mt-1">${escapeHtml(stat.link_label ?? 'Lihat Detail')} </a>` : ''}
                </div>
                <div class="stat-card__icon">
                    <i class="bi ${escapeHtml(stat.icon)}"></i>
                </div>
            </div>
        </div>
    `).join('');

    container.setAttribute('aria-busy', 'false');
}

function renderTransactions(transactions, statusBadge) {
    const body = document.getElementById('kasir-transactions-body');
    const count = document.getElementById('kasir-transactions-count');

    count.innerHTML = `<span class="badge badge-soft badge-soft--neutral">${transactions.length} transaksi</span>`;

    if (transactions.length === 0) {
        body.innerHTML = EMPTY_TRANSACTIONS;
    } else {
        body.innerHTML = transactions.map((transaction) => `
            <tr>
                <td>
                    <a href="${escapeHtml(transaction.url)}" class="fw-semibold">${escapeHtml(transaction.id)}</a>
                </td>
                <td class="text-nowrap">${escapeHtml(transaction.jam)}</td>
                <td>${escapeHtml(transaction.kasir ?? '-')}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${escapeHtml(transaction.jenis_badge)}">${escapeHtml(transaction.jenis)}</span>
                </td>
                <td class="fw-semibold text-nowrap">${formatRupiah(transaction.total)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${escapeHtml(statusBadge[transaction.status] ?? 'neutral')}">
                        ${escapeHtml(transaction.status ? transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1) : '-')}
                    </span>
                </td>
                <td class="text-end">
                    <a href="${escapeHtml(transaction.url)}" class="link-secondary" title="Lihat detail transaksi">
                        <i class="bi bi-receipt"></i>
                    </a>
                </td>
            </tr>
        `).join('');
    }

    document.getElementById('kasir-transactions-pane').setAttribute('aria-busy', 'false');
}

function renderPendingOrders(pendingOrders) {
    const container = document.getElementById('kasir-pending-orders');
    const count = document.getElementById('kasir-pending-count');

    count.innerHTML = `<span class="badge badge-soft badge-soft--warning">${pendingOrders.length}</span>`;

    if (pendingOrders.length === 0) {
        container.innerHTML = EMPTY_PENDING_ORDERS;
    } else {
        container.innerHTML = pendingOrders.map((order) => `
            <div class="order-card">
                <div class="order-card__header">
                    <span class="order-card__id">${escapeHtml(order.no)}</span>
                    <span class="order-card__time">${escapeHtml(order.time)}</span>
                </div>
                <div class="order-card__meta">
                    <span class="avatar">${escapeHtml(String(order.customer ?? '-').charAt(0).toUpperCase())}</span>
                    <span class="text-truncate">${escapeHtml(order.customer)}</span>
                    <span>&middot;</span>
                    <span>${formatNumber(order.items)} item</span>
                </div>
                <div class="mt-2">
                    <span class="order-card__total">${formatRupiah(order.total)}</span>
                </div>
                <div class="order-card__actions">
                    <a href="/kasir/antrian" class="btn btn-brand btn-sm">
                        <i class="bi bi-eye me-1"></i> Tinjau
                    </a>
                </div>
            </div>
        `).join('');
    }

    document.getElementById('kasir-pending-pane').setAttribute('aria-busy', 'false');
}

function renderError() {
    const stats = document.getElementById('kasir-stats');
    const body = document.getElementById('kasir-transactions-body');
    const pending = document.getElementById('kasir-pending-orders');
    const errorState = `
        <div class="empty-state">
            <span class="empty-state__icon"><i class="bi bi-exclamation-triangle"></i></span>
            <div class="empty-state__title">Data gagal dimuat</div>
            <div class="empty-state__text">Periksa koneksi lalu coba lagi.</div>
            <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-kasir-dashboard-retry>
                <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
            </button>
        </div>
    `;

    stats.innerHTML = `<div class="col-12">${errorState}</div>`;
    body.innerHTML = EMPTY_TRANSACTIONS;
    pending.innerHTML = errorState;
    document.getElementById('kasir-transactions-count').innerHTML = '';
    document.getElementById('kasir-pending-count').innerHTML = '';

    [stats, document.getElementById('kasir-transactions-pane'), document.getElementById('kasir-pending-pane')]
        .forEach((element) => element.setAttribute('aria-busy', 'false'));
}

async function loadKasirDashboard() {
    try {
        const response = await fetch(ENDPOINT, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const payload = await response.json();
        const data = payload.data ?? {};

        renderStats(data.stats ?? []);
        renderTransactions(data.transactions ?? [], STATUS_BADGE);
        renderPendingOrders(data.pendingOrders ?? []);
    } catch (error) {
        console.error('Gagal memuat dashboard kasir:', error);
        renderError();
    }
}

function bootKasirDashboard() {
    if (! document.getElementById('kasir-stats')) {
        return;
    }

    document.addEventListener('click', (event) => {
        const retry = event.target.closest('[data-kasir-dashboard-retry]');
        if (retry) {
            loadKasirDashboard();
        }
    });

    loadKasirDashboard();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootKasirDashboard);
} else {
    bootKasirDashboard();
}
