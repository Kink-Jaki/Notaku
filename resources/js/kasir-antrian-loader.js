const ENDPOINT = '/api/kasir/antrian';

const STATUS_BADGE = {
    menunggu: 'warning',
    diproses: 'info',
    disetujui: 'success',
    selesai: 'success',
    ditolak: 'danger',
};

const COMPLETE_MODAL_TEMPLATE = `
<div id="antrian-complete-{{id}}" class="modal fade" tabindex="-1" aria-labelledby="antrian-complete-{{id}}-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{complete_url}}" class="complete-form">
            <input type="hidden" name="_token" value="{{csrf}}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="antrian-complete-{{id}}-label">Tandai Selesai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted-pos mb-3">
                        Pesanan <strong>{{order_number}}</strong> dari <strong class="text-body">{{customer_name}}</strong>
                        sudah diproses. Tandai sebagai selesai?
                    </p>
                    <div class="note-box note-box--info">
                        <i class="bi bi-info-circle note-box__icon"></i>
                        <span>Setelah ditandai selesai, pesanan tidak akan muncul di antrian lagi dan pelanggan akan menerima notifikasi.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Tandai Selesai</button>
                </div>
            </div>
        </form>
</div>
`;

const EMPTY_PENDING = `
    <tr>
        <td colspan="6">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-check2-circle"></i></span>
                <div class="empty-state__title">Tidak ada pesanan menunggu</div>
                <div class="empty-state__text">Semua pesanan masuk sudah diatasi.</div>
            </div>
        </td>
    </tr>
`;

const EMPTY_HANDLED = `
    <tr>
        <td colspan="6">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-clock-history"></i></span>
                <div class="empty-state__title">Belum ada riwayat penanganan</div>
                <div class="empty-state__text">Tidak ada pesanan yang sudah disetujui atau ditolak pada kategori ini.</div>
            </div>
        </td>
    </tr>
`;

const ERROR_STATE = `
    <div class="empty-state">
        <span class="empty-state__icon"><i class="bi bi-exclamation-triangle"></i></span>
        <div class="empty-state__title">Data gagal dimuat</div>
        <div class="empty-state__text">Periksa koneksi lalu coba lagi.</div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-antrian-retry>
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

function renderTabs(tabs) {
    const container = document.getElementById('antrian-tabs');
    const currentStatus = new URLSearchParams(window.location.search).get('status') ?? 'semua';

    container.innerHTML = tabs.map((tab) => `
        <li class="nav-item flex-shrink-0 me-1">
            <a class="nav-link ${tab.key === currentStatus ? 'active' : ''}" href="${escapeHtml(tab.url)}">
                ${escapeHtml(tab.label)}
                <span class="badge rounded-pill ms-1 ${tab.key === currentStatus ? 'bg-white text-primary' : 'bg-body-secondary text-body'}">
                    ${escapeHtml(tab.count)}
                </span>
            </a>
        </li>
    `).join('');

    container.setAttribute('aria-busy', 'false');
}

function renderPending(orders, csrf) {
    const body = document.getElementById('antrian-pending-body');
    const countEl = document.getElementById('antrian-pending-count');
    const badgeEl = document.getElementById('antrian-menunggu-badge');

    if (badgeEl) {
        badgeEl.innerHTML = `<i class="bi bi-hourglass-split me-1"></i> ${orders.length} pesanan menunggu`;
    }
    if (countEl) countEl.textContent = orders.length;

    if (! body) {
        return;
    }

    if (orders.length === 0) {
        body.innerHTML = EMPTY_PENDING;
    } else {
        body.innerHTML = orders.map((order) => `
            <tr>
                <td>
                    <span class="fw-semibold font-monospace">${escapeHtml(order.order_number)}</span>
                    <div class="small text-muted-pos"><span class="badge badge-soft badge-soft--warning"><span class="badge-soft__dot"></span>Menunggu</span></div>
                </td>
                <td>
                    <span class="avatar">${escapeHtml(String(order.customer_name ?? '-').charAt(0).toUpperCase())}</span>
                    <span class="text-truncate ms-2">${escapeHtml(order.customer_name)}</span>
                    <div class="small text-muted-pos">${escapeHtml(order.payment_method_label)}</div>
                    <div class="small text-muted-pos">Pengiriman: ${escapeHtml(order.delivery_type_label ?? '—')}</div>
                </td>
                <td class="text-nowrap">Dipesan ${escapeHtml(order.waktu)}</td>
                <td class="text-nowrap">${escapeHtml(order.item)} item</td>
                <td class="fw-semibold text-nowrap">${formatRupiah(order.total)}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-detail" data-order-id="${escapeHtml(order.id)}">
                        <i class="bi bi-eye me-1"></i> Detail
                    </button>
                    <button type="button" class="btn btn-success btn-sm btn-approve" data-order-id="${escapeHtml(order.id)}">
                        <i class="bi bi-check-lg me-1"></i> Approve
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-reject" data-order-id="${escapeHtml(order.id)}">
                        <i class="bi bi-x-lg me-1"></i> Tolak
                    </button>
                </td>
            </tr>
        `).join('');
    }

    body.setAttribute('aria-busy', 'false');

    if (document.getElementById('antrian-modals')) {
        renderModals(orders, csrf);
    }
}

function renderHandled(rows) {
    const body = document.getElementById('antrian-handled-body');
    const countEl = document.getElementById('antrian-handled-count');

    if (countEl) countEl.textContent = rows.length;

    if (! body) {
        return;
    }

    if (rows.length === 0) {
        body.innerHTML = EMPTY_HANDLED;
    } else {
body.innerHTML = rows.map((row) => `
            <tr>
                <td>
                    ${row.trx ? `<a href="${escapeHtml(row.trx_url)}" class="fw-semibold">${escapeHtml(row.trx)}</a><div class="small text-muted-pos">${escapeHtml(row.no)}</div>` : `<span class="fw-semibold text-muted-pos">${escapeHtml(row.no)}</span>`}
                </td>
                <td>${escapeHtml(row.pelanggan)}</td>
                <td class="text-nowrap">${escapeHtml(row.waktu)}</td>
                <td class="text-nowrap">${escapeHtml(row.item)} item</td>
                <td class="fw-semibold text-nowrap">${formatRupiah(row.total)}</td>
                <td>
                    ${row.keputusan === 'disetujui'
                        ? `<span class="badge badge-soft badge-soft--success"><span class="badge-soft__dot"></span>Disetujui</span>`
                        : `<span class="badge badge-soft badge-soft--danger"><span class="badge-soft__dot"></span>Ditolak</span>
                           ${row.alasan ? `<div class="decision-note text-truncate mt-1" title="${escapeHtml(row.alasan)}">${escapeHtml(row.alasan)}</div>` : ''}`}
                </td>
                <td class="text-end">
                    ${row.complete_url ? `
                        <button type="button" class="btn btn-success btn-sm btn-complete" data-order-id="${escapeHtml(row.no)}">
                            <i class="bi bi-check-lg me-1"></i> Selesai
                        </button>
                    ` : ''}
                </td>
            </tr>
        `).join('');
    }

    body.setAttribute('aria-busy', 'false');
}

function renderModals(orders, csrf) {
    const container = document.getElementById('antrian-modals');
    const modalsHtml = orders.map((order) => `
        ${renderDetailModal(order)}
        ${renderApproveModal(order, csrf)}
        ${renderRejectModal(order, csrf)}
        ${order.complete_url ? renderCompleteModal(order, csrf) : ''}
    `).join('');

    container.innerHTML = modalsHtml;
}

function renderDetailModal(order) {
    const itemsHtml = order.items.map((item) => `
        <tr>
            <td>${escapeHtml(item.product_name)}</td>
            <td class="text-center">${escapeHtml(item.qty)}</td>
            <td class="text-end text-nowrap">${formatRupiah(item.price)}</td>
            <td class="text-end text-nowrap fw-semibold">${formatRupiah(item.price * item.qty)}</td>
        </tr>
    `).join('');

    return `
        <div id="antrian-detail-${order.id}" class="modal fade" tabindex="-1" aria-labelledby="antrian-detail-${order.id}-label" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="antrian-detail-${order.id}-label">Detail Pesanan ${escapeHtml(order.order_number)}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <span class="badge badge-soft badge-soft--warning"><span class="badge-soft__dot"></span>Menunggu Konfirmasi</span>
                            <span class="small text-muted-pos"><i class="bi bi-clock me-1"></i>Dipesan ${escapeHtml(order.waktu)}</span>
                        </div>
                        <div class="order-detail-list mb-4">
                            <div class="order-detail-list__row"><span class="order-detail-list__label">Pelanggan</span><span class="order-detail-list__value">${escapeHtml(order.customer_name)}</span></div>
                            <div class="order-detail-list__row"><span class="order-detail-list__label">No. WA</span><span class="order-detail-list__value">${escapeHtml(order.customer_phone ?? '—')}</span></div>
                            <div class="order-detail-list__row"><span class="order-detail-list__label">Pengiriman</span><span class="order-detail-list__value">${escapeHtml(order.delivery_type_label ?? '—')}</span></div>
                            ${order.address ? `<div class="order-detail-list__row"><span class="order-detail-list__label">Alamat</span><span class="order-detail-list__value">${escapeHtml(order.address)}</span></div>` : ''}
                            <div class="order-detail-list__row"><span class="order-detail-list__label">Metode Pembayaran</span><span class="order-detail-list__value">${escapeHtml(order.payment_method_label)}</span></div>
                            <div class="order-detail-list__row"><span class="order-detail-list__label">Catatan</span><span class="order-detail-list__value">${escapeHtml(order.note ?? '—')}</span></div>
                        </div>
                        <div class="table-wrap mb-4">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Harga</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>${itemsHtml}</tbody>
                                </table>
                            </div>
                        </div>
                        <div class="order-summary">
                            <div class="order-summary__row"><span>Subtotal (${escapeHtml(order.item)} item)</span><span>${formatRupiah(order.subtotal)}</span></div>
                            ${order.promo_code ? `<div class="order-summary__row"><span>Diskon — ${escapeHtml(order.promo_code)}</span><span class="order-summary__disc">−${formatRupiah(order.discount)}</span></div>` : ''}
                            <div class="order-summary__row order-summary__row--total"><span>Total</span><span>${formatRupiah(order.total)}</span></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger btn-reject" data-order-id="${escapeHtml(order.id)}">
                            <i class="bi bi-x-lg me-1"></i> Tolak
                        </button>
                        <button type="button" class="btn btn-success btn-approve" data-order-id="${escapeHtml(order.id)}">
                            <i class="bi bi-check-lg me-1"></i> Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderApproveModal(order, csrf) {
    return `
        <div id="antrian-approve-${order.id}" class="modal fade" tabindex="-1" aria-labelledby="antrian-approve-${order.id}-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="${escapeHtml(order.approve_url)}" class="approve-form">
                    <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="antrian-approve-${order.id}-label">Konfirmasi Pesanan ${escapeHtml(order.order_number)}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted-pos mb-3">
                                Pesanan ${escapeHtml(order.order_number)} dari <strong class="text-body">${escapeHtml(order.customer_name)}</strong>
                                (${formatRupiah(order.total)}) akan menjadi transaksi resmi. Pilih metode pembayaran.
                            </p>
                            <label class="form-label d-block">Metode Pembayaran</label>
                            <div class="d-flex flex-wrap gap-3 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="metodeTunai-${order.id}" value="tunai" checked>
                                    <label class="form-check-label" for="metodeTunai-${order.id}"><i class="bi bi-cash me-1"></i> Tunai</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="metodeQris-${order.id}" value="qris">
                                    <label class="form-check-label" for="metodeQris-${order.id}"><i class="bi bi-qr-code me-1"></i> QRIS</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="metodeWallet-${order.id}" value="ewallet">
                                    <label class="form-check-label" for="metodeWallet-${order.id}"><i class="bi bi-wallet2 me-1"></i> E-Wallet</label>
                                </div>
                            </div>
                            <div class="note-box note-box--info">
                                <i class="bi bi-info-circle note-box__icon"></i>
                                <span>Setelah disetujui, pesanan menjadi <strong>transaksi resmi</strong> dan <strong>stok berkurang</strong> sesuai item pada pesanan.</span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batalkan</button>
                            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Setujui & Buat Transaksi</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `;
}

function renderRejectModal(order, csrf) {
    return `
        <div id="antrian-reject-${order.id}" class="modal fade" tabindex="-1" aria-labelledby="antrian-reject-${order.id}-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="${escapeHtml(order.reject_url)}" class="reject-form">
                    <input type="hidden" name="_token" value="${escapeHtml(csrf)}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="antrian-reject-${order.id}-label">Tolak Pesanan ${escapeHtml(order.order_number)}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted-pos mb-3">
                                Pesanan ${escapeHtml(order.order_number)} dari <strong class="text-body">${escapeHtml(order.customer_name)}</strong>
                                akan ditandai <strong class="text-danger">Ditolak</strong> dan pelanggan akan menerima pemberitahuan beserta alasannya.
                            </p>
                            <label class="form-label" for="alasan-${order.id}">Alasan Penolakan</label>
                            <textarea class="form-control" id="alasan-${order.id}" name="reason" rows="3" placeholder="Contoh: produk habis, alamat di luar jangkauan" required></textarea>
                            <div class="note-box note-box--warning mt-3">
                                <i class="bi bi-exclamation-triangle note-box__icon"></i>
                                <span>Penolakan bersifat permanen. Pastikan alasan jelas agar pelanggan dapat mengikuti perkembangan pesanannya.</span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batalkan</button>
                            <button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i> Tolak Pesanan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `;
}

function renderCompleteModal(order, csrf) {
    return COMPLETE_MODAL_TEMPLATE
        .replace('{{id}}', order.id)
        .replace('{{complete_url}}', order.complete_url)
        .replace('{{csrf}}', csrf)
        .replace('{{order_number}}', order.order_number)
        .replace('{{customer_name}}', order.customer_name);
}

function applyStatusLayout(status) {
    const pendingSection = document.getElementById('antrian-pending-section');
    const handledSection = document.getElementById('antrian-handled-section');

    if (pendingSection) {
        pendingSection.classList.toggle('d-none', ! ['semua', 'menunggu'].includes(status));
    }

    if (handledSection) {
        handledSection.classList.toggle('d-none', ! ['semua', 'diproses', 'ditolak'].includes(status));
    }
}

function renderError() {
    const errorHtml = ERROR_STATE;

    const pendingBody = document.getElementById('antrian-pending-body');
    if (pendingBody) pendingBody.innerHTML = EMPTY_PENDING;

    const handledBody = document.getElementById('antrian-handled-body');
    if (handledBody) handledBody.innerHTML = EMPTY_HANDLED;

    const pendingCount = document.getElementById('antrian-pending-count');
    if (pendingCount) pendingCount.textContent = '';

    const handledCount = document.getElementById('antrian-handled-count');
    if (handledCount) handledCount.textContent = '';

    const menungguBadge = document.getElementById('antrian-menunggu-badge');
    if (menungguBadge) menungguBadge.innerHTML = `<i class="bi bi-hourglass-split me-1"></i> — pesanan menunggu`;

    const tabs = document.getElementById('antrian-tabs');
    if (tabs) tabs.innerHTML = errorHtml;

    [tabs, document.getElementById('antrian-pending-section'), document.getElementById('antrian-handled-section')]
        .forEach((el) => el?.setAttribute('aria-busy', 'false'));
}

function showModal(id) {
    const target = document.getElementById(id);
    if (!target || !window.bootstrap) return;

    const open = document.querySelector('.modal.show');
    if (open && open !== target) {
        const instance = window.bootstrap.Modal.getInstance(open);
        if (instance) {
            open.addEventListener('hidden.bs.modal', () => {
                window.bootstrap.Modal.getOrCreateInstance(target).show();
            }, { once: true });
            instance.hide();
            return;
        }
    }
    window.bootstrap.Modal.getOrCreateInstance(target).show();
}

let urutanMuat = 0;

function setLoading(loading) {
    ['antrian-tabs', 'antrian-pending-section', 'antrian-handled-section']
        .forEach((id) => {
            const el = document.getElementById(id);

            if (el) {
                el.style.opacity = loading ? '0.5' : '';
                el.style.pointerEvents = loading ? 'none' : '';
            }
        });
}

async function loadAntrian() {
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
        const csrf = document.getElementById('antrian-modals')?.dataset.csrf ?? '';

        renderTabs(data.tabs ?? []);
        applyStatusLayout(data.status ?? 'semua');
        renderPending(data.pending ?? [], csrf);
        renderHandled(data.handled ?? []);
    } catch (error) {
        if (id !== urutanMuat) {
            return;
        }

        console.error('Gagal memuat antrian pesanan:', error);
        renderError();
    } finally {
        if (id === urutanMuat) {
            setLoading(false);
        }
    }
}

function bootKasirAntrian() {
    if (!document.getElementById('antrian-tabs')) return;

    document.addEventListener('click', (event) => {
        const retry = event.target.closest('[data-antrian-retry]');
        if (retry) { loadAntrian(); return; }

        const detailBtn = event.target.closest('.btn-detail');
        if (detailBtn) { showModal(`antrian-detail-${detailBtn.dataset.orderId}`); return; }

        const approveBtn = event.target.closest('.btn-approve');
        if (approveBtn) { showModal(`antrian-approve-${approveBtn.dataset.orderId}`); return; }

        const rejectBtn = event.target.closest('.btn-reject');
        if (rejectBtn) { showModal(`antrian-reject-${rejectBtn.dataset.orderId}`); return; }

        const completeBtn = event.target.closest('.btn-complete');
        if (completeBtn) { showModal(`antrian-complete-${completeBtn.dataset.orderId}`); return; }
    });

    document.addEventListener('click', (event) => {
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const tautan = event.target.closest('#antrian-tabs a[href]');

        if (! tautan) {
            return;
        }

        event.preventDefault();

        const tujuan = new URL(tautan.href, window.location.origin);

        window.history.pushState({ antrian: true }, '', tujuan.pathname + tujuan.search);
        loadAntrian();
    }, true);

    window.addEventListener('popstate', () => {
        loadAntrian();
    });

    document.addEventListener('submit', (event) => {
        const rejectForm = event.target.closest('form.reject-form');
        if (rejectForm) {
            event.preventDefault();
            if (typeof Swal === 'undefined') { form.submit(); return; }
            Swal.fire({
                title: 'Tolak pesanan ini?',
                text: 'Pesanan akan ditandai ditolak dan pelanggan menerima pemberitahuan beserta alasannya.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, tolak!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return;
        }

        const completeForm = event.target.closest('form.complete-form');
        if (completeForm) {
            event.preventDefault();
            if (typeof Swal === 'undefined') { completeForm.submit(); return; }
            Swal.fire({
                title: 'Tandai pesanan selesai?',
                text: 'Pesanan akan ditandai selesai dan tidak muncul di antrian lagi.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, selesai!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    completeForm.submit();
                }
            });
        }
    });

    loadAntrian();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootKasirAntrian);
} else {
    bootKasirAntrian();
}