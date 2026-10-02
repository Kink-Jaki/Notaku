import { initDashboardChart } from './dashboard-chart';

const ENDPOINT = '/api/admin/dashboard';

const EMPTY_STOCKS = `
    <div class="empty-state py-3">
        <div class="empty-state__icon"><i class="bi bi-check-circle"></i></div>
        <div class="empty-state__title">Semua stok aman</div>
        <div class="empty-state__text">Semua produk memiliki stok yang cukup.</div>
    </div>
`;

const EMPTY_TOP_SPENDERS = `
    <div class="col-12">
        <div class="empty-state py-3">
            <div class="empty-state__icon"><i class="bi bi-people"></i></div>
            <div class="empty-state__title">Belum ada top spender</div>
            <div class="empty-state__text">Top spender akan muncul setelah ada pesanan yang selesai.</div>
        </div>
    </div>
`;

const EMPTY_TRANSACTIONS = `
    <tr>
        <td colspan="6">
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-receipt"></i></div>
                <div class="empty-state__title">Belum ada transaksi</div>
                <div class="empty-state__text">Transaksi akan muncul di sini setelah ada pembelian.</div>
            </div>
        </td>
    </tr>
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
    const container = document.getElementById('admin-stats');

    container.innerHTML = stats.map((stat) => `
        <div class="col">
            <div class="stat-card stat-card--${escapeHtml(stat.modifier)}">
                <div>
                    <div class="stat-card__label">
                        ${escapeHtml(stat.label)}
                        ${stat.hint ? `<span class="text-muted-pos fw-normal small">${escapeHtml(stat.hint)}</span>` : ''}
                    </div>
                    <div class="stat-card__value">
                        ${escapeHtml(stat.value)}
                        ${stat.badge ? `<span class="badge badge-soft badge-soft--${escapeHtml(stat.badge)} badge-soft__dot badge-soft--count ms-1">!</span>` : ''}
                    </div>
                </div>
                <div class="stat-card__icon">
                    <i class="bi ${escapeHtml(stat.icon)}"></i>
                </div>
            </div>
        </div>
    `).join('');

    container.setAttribute('aria-busy', 'false');
}

function renderChart(chart) {
    const container = document.getElementById('admin-sales-chart');

    container.innerHTML = `
        <canvas id="salesChart"
            data-labels="${escapeHtml(JSON.stringify(chart.labels ?? []))}"
            data-trx="${escapeHtml(JSON.stringify(chart.transactions ?? []))}"
            data-omzet="${escapeHtml(JSON.stringify(chart.omzet ?? []))}">
        </canvas>
    `;

    initDashboardChart();
    container.setAttribute('aria-busy', 'false');
}

function renderStocks(stocks) {
    const container = document.getElementById('admin-stocks');
    const count = document.getElementById('admin-stocks-count');

    count.innerHTML = `<span class="badge badge-soft badge-soft--danger">${stocks.length} produk</span>`;

    if (stocks.length === 0) {
        container.innerHTML = EMPTY_STOCKS;
    } else {
        container.innerHTML = stocks.map((stock) => `
            <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="small fw-semibold text-truncate">${escapeHtml(stock.name)}</span>
                <span class="badge badge-soft badge-soft--${escapeHtml(stock.modifier)} text-nowrap">${formatNumber(stock.stock)} sisa</span>
            </div>
        `).join('');
    }

    document.getElementById('admin-stocks-pane').setAttribute('aria-busy', 'false');
}

function renderTopSpenders(spenders) {
    const container = document.getElementById('admin-top-spenders');
    const count = document.getElementById('admin-top-spenders-count');

    count.innerHTML = `<span class="badge badge-soft badge-soft--warning">${spenders.length} pelanggan</span>`;

    if (spenders.length === 0) {
        container.innerHTML = EMPTY_TOP_SPENDERS;
    } else {
        container.innerHTML = spenders.map((spender) => `
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge badge-soft badge-soft--${escapeHtml(spender.modifier)} text-nowrap fw-semibold">
                        Tier ${escapeHtml(spender.tier)} &middot; #${formatNumber(spender.rank)}
                    </span>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="small fw-semibold text-truncate">${escapeHtml(spender.name)}</div>
                        <div class="small text-muted-pos">${formatNumber(spender.orders)} pesanan</div>
                    </div>
                    <span class="small fw-semibold text-nowrap">${formatRupiah(spender.total)}</span>
                </div>
            </div>
        `).join('');
    }

    document.getElementById('admin-top-spenders-pane').setAttribute('aria-busy', 'false');
}

function renderTransactions(transactions) {
    const body = document.getElementById('admin-transactions-body');
    const count = document.getElementById('admin-transactions-count');

    count.innerHTML = `<span class="badge badge-soft badge-soft--info">${transactions.length} transaksi</span>`;

    if (transactions.length === 0) {
        body.innerHTML = EMPTY_TRANSACTIONS;
    } else {
        body.innerHTML = transactions.map((transaction) => `
            <tr>
                <td class="fw-semibold text-nowrap">${escapeHtml(transaction.number)}</td>
                <td class="text-nowrap">${escapeHtml(transaction.time)}</td>
                <td>${escapeHtml(transaction.cashier)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${escapeHtml(transaction.type_modifier)}">${escapeHtml(transaction.type)}</span>
                </td>
                <td class="fw-semibold text-nowrap">${formatRupiah(transaction.total)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${transaction.status === 'selesai' ? 'success' : 'neutral'}">
                        ${escapeHtml(transaction.status ? transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1) : '-')}
                    </span>
                </td>
            </tr>
        `).join('');
    }

    document.getElementById('admin-transactions-pane').setAttribute('aria-busy', 'false');
}

function renderSummary(summary) {
    const values = {
        products: summary.products,
        users: summary.users,
        promos: summary.promos,
    };

    document.querySelectorAll('[data-summary-key]').forEach((element) => {
        element.textContent = formatNumber(values[element.dataset.summaryKey]);
    });
}

function renderError() {
    const errorState = `
        <div class="empty-state">
            <div class="empty-state__icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="empty-state__title">Data gagal dimuat</div>
            <div class="empty-state__text">Periksa koneksi lalu coba lagi.</div>
            <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-admin-dashboard-retry>
                <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
            </button>
        </div>
    `;

    const stats = document.getElementById('admin-stats');
    const chart = document.getElementById('admin-sales-chart');
    const stocks = document.getElementById('admin-stocks');
    const topSpenders = document.getElementById('admin-top-spenders');
    const transactions = document.getElementById('admin-transactions-body');

    stats.innerHTML = `<div class="col-12">${errorState}</div>`;
    chart.innerHTML = errorState;
    stocks.innerHTML = errorState;
    topSpenders.innerHTML = `<div class="col-12">${errorState}</div>`;
    transactions.innerHTML = EMPTY_TRANSACTIONS;

    document.getElementById('admin-stocks-count').innerHTML = '';
    document.getElementById('admin-top-spenders-count').innerHTML = '';
    document.getElementById('admin-transactions-count').innerHTML = '';
    document.querySelectorAll('[data-summary-key]').forEach((element) => {
        element.textContent = '—';
    });

    [stats, chart, document.getElementById('admin-stocks-pane'), document.getElementById('admin-top-spenders-pane'), document.getElementById('admin-transactions-pane')]
        .forEach((element) => element.setAttribute('aria-busy', 'false'));
}

async function loadAdminDashboard() {
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
        renderChart(data.chart ?? {});
        renderStocks(data.stocks ?? []);
        renderTopSpenders(data.topSpenders ?? []);
        renderTransactions(data.recentTransactions ?? []);
        renderSummary(data.summary ?? {});
    } catch (error) {
        console.error('Gagal memuat dashboard admin:', error);
        renderError();
    }
}

function bootAdminDashboard() {
    if (! document.getElementById('admin-stats')) {
        return;
    }

    document.addEventListener('click', (event) => {
        const retry = event.target.closest('[data-admin-dashboard-retry]');
        if (retry) {
            loadAdminDashboard();
        }
    });

    loadAdminDashboard();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootAdminDashboard);
} else {
    bootAdminDashboard();
}
