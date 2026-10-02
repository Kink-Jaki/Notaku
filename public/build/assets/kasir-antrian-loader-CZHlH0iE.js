var e=`
    <tr>
        <td colspan="6">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-check2-circle"></i></span>
                <div class="empty-state__title">Tidak ada pesanan menunggu</div>
                <div class="empty-state__text">Semua pesanan masuk sudah diatasi.</div>
            </div>
        </td>
    </tr>
`,t=`
    <tr>
        <td colspan="6">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-clock-history"></i></span>
                <div class="empty-state__title">Belum ada riwayat penanganan</div>
                <div class="empty-state__text">Tidak ada pesanan yang sudah disetujui atau ditolak pada kategori ini.</div>
            </div>
        </td>
    </tr>
`;function n(e){return String(e??``).replace(/[&<>"']/g,e=>({"&":`&`,"<":`<`,">":`>`,'"':`"`,"'":`&#039;`})[e])}function r(e){return Number(e??0).toLocaleString(`id-ID`)}function i(e){return`Rp ${r(e)}`}function a(e){let t=document.getElementById(`antrian-tabs`),r=new URLSearchParams(window.location.search).get(`status`)??`semua`;t.innerHTML=e.map(e=>`
        <li class="nav-item flex-shrink-0 me-1">
            <a class="nav-link ${e.key===r?`active`:``}" href="${n(e.url)}">
                ${n(e.label)}
                <span class="badge rounded-pill ms-1 ${e.key===r?`bg-white text-primary`:`bg-body-secondary text-body`}">
                    ${n(e.count)}
                </span>
            </a>
        </li>
    `).join(``),t.setAttribute(`aria-busy`,`false`)}function o(t,r){let a=document.getElementById(`antrian-pending-body`),o=document.getElementById(`antrian-pending-count`),s=document.getElementById(`antrian-menunggu-badge`);s&&(s.innerHTML=`<i class="bi bi-hourglass-split me-1"></i> ${t.length} pesanan menunggu`),o&&(o.textContent=t.length),a&&(a.innerHTML=t.length===0?e:t.map(e=>`
            <tr>
                <td>
                    <span class="fw-semibold font-monospace">${n(e.order_number)}</span>
                    <div class="small text-muted-pos"><span class="badge badge-soft badge-soft--warning"><span class="badge-soft__dot"></span>Menunggu</span></div>
                </td>
                <td>
                    <span class="avatar">${n(String(e.customer_name??`-`).charAt(0).toUpperCase())}</span>
                    <span class="text-truncate ms-2">${n(e.customer_name)}</span>
                    <div class="small text-muted-pos">${n(e.payment_method_label)}</div>
                    <div class="small text-muted-pos">Pengiriman: ${n(e.delivery_type_label??`—`)}</div>
                </td>
                <td class="text-nowrap">Dipesan ${n(e.waktu)}</td>
                <td class="text-nowrap">${n(e.item)} item</td>
                <td class="fw-semibold text-nowrap">${i(e.total)}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-detail" data-order-id="${n(e.id)}">
                        <i class="bi bi-eye me-1"></i> Detail
                    </button>
                    <button type="button" class="btn btn-success btn-sm btn-approve" data-order-id="${n(e.id)}">
                        <i class="bi bi-check-lg me-1"></i> Approve
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-reject" data-order-id="${n(e.id)}">
                        <i class="bi bi-x-lg me-1"></i> Tolak
                    </button>
                </td>
            </tr>
        `).join(``),a.setAttribute(`aria-busy`,`false`),document.getElementById(`antrian-modals`)&&c(t,r))}function s(e){let r=document.getElementById(`antrian-handled-body`),a=document.getElementById(`antrian-handled-count`);a&&(a.textContent=e.length),r&&(r.innerHTML=e.length===0?t:e.map(e=>`
            <tr>
                <td>
                    ${e.trx?`<a href="${n(e.trx_url)}" class="fw-semibold">${n(e.trx)}</a><div class="small text-muted-pos">${n(e.no)}</div>`:`<span class="fw-semibold text-muted-pos">${n(e.no)}</span>`}
                </td>
                <td>${n(e.pelanggan)}</td>
                <td class="text-nowrap">${n(e.waktu)}</td>
                <td class="text-nowrap">${n(e.item)} item</td>
                <td class="fw-semibold text-nowrap">${i(e.total)}</td>
                <td>
                    ${e.keputusan===`disetujui`?`<span class="badge badge-soft badge-soft--success"><span class="badge-soft__dot"></span>Disetujui</span>`:`<span class="badge badge-soft badge-soft--danger"><span class="badge-soft__dot"></span>Ditolak</span>
                           ${e.alasan?`<div class="decision-note text-truncate mt-1" title="${n(e.alasan)}">${n(e.alasan)}</div>`:``}`}
                </td>
                <td class="text-end">
                    ${e.complete_url?`
                        <button type="button" class="btn btn-success btn-sm btn-complete" data-order-id="${n(e.no)}">
                            <i class="bi bi-check-lg me-1"></i> Selesai
                        </button>
                    `:``}
                </td>
            </tr>
        `).join(``),r.setAttribute(`aria-busy`,`false`))}function c(e,t){let n=document.getElementById(`antrian-modals`);n.innerHTML=e.map(e=>`
        ${l(e)}
        ${u(e,t)}
        ${d(e,t)}
        ${e.complete_url?f(e,t):``}
    `).join(``)}function l(e){let t=e.items.map(e=>`
        <tr>
            <td>${n(e.product_name)}</td>
            <td class="text-center">${n(e.qty)}</td>
            <td class="text-end text-nowrap">${i(e.price)}</td>
            <td class="text-end text-nowrap fw-semibold">${i(e.price*e.qty)}</td>
        </tr>
    `).join(``);return`
        <div id="antrian-detail-${e.id}" class="modal fade" tabindex="-1" aria-labelledby="antrian-detail-${e.id}-label" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="antrian-detail-${e.id}-label">Detail Pesanan ${n(e.order_number)}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <span class="badge badge-soft badge-soft--warning"><span class="badge-soft__dot"></span>Menunggu Konfirmasi</span>
                            <span class="small text-muted-pos"><i class="bi bi-clock me-1"></i>Dipesan ${n(e.waktu)}</span>
                        </div>
                        <div class="order-detail-list mb-4">
                            <div class="order-detail-list__row"><span class="order-detail-list__label">Pelanggan</span><span class="order-detail-list__value">${n(e.customer_name)}</span></div>
                            <div class="order-detail-list__row"><span class="order-detail-list__label">No. WA</span><span class="order-detail-list__value">${n(e.customer_phone??`—`)}</span></div>
                            <div class="order-detail-list__row"><span class="order-detail-list__label">Pengiriman</span><span class="order-detail-list__value">${n(e.delivery_type_label??`—`)}</span></div>
                            ${e.address?`<div class="order-detail-list__row"><span class="order-detail-list__label">Alamat</span><span class="order-detail-list__value">${n(e.address)}</span></div>`:``}
                            <div class="order-detail-list__row"><span class="order-detail-list__label">Metode Pembayaran</span><span class="order-detail-list__value">${n(e.payment_method_label)}</span></div>
                            <div class="order-detail-list__row"><span class="order-detail-list__label">Catatan</span><span class="order-detail-list__value">${n(e.note??`—`)}</span></div>
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
                                    <tbody>${t}</tbody>
                                </table>
                            </div>
                        </div>
                        <div class="order-summary">
                            <div class="order-summary__row"><span>Subtotal (${n(e.item)} item)</span><span>${i(e.subtotal)}</span></div>
                            ${e.promo_code?`<div class="order-summary__row"><span>Diskon — ${n(e.promo_code)}</span><span class="order-summary__disc">−${i(e.discount)}</span></div>`:``}
                            <div class="order-summary__row order-summary__row--total"><span>Total</span><span>${i(e.total)}</span></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger btn-reject" data-order-id="${n(e.id)}">
                            <i class="bi bi-x-lg me-1"></i> Tolak
                        </button>
                        <button type="button" class="btn btn-success btn-approve" data-order-id="${n(e.id)}">
                            <i class="bi bi-check-lg me-1"></i> Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `}function u(e,t){return`
        <div id="antrian-approve-${e.id}" class="modal fade" tabindex="-1" aria-labelledby="antrian-approve-${e.id}-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="${n(e.approve_url)}" class="approve-form">
                    <input type="hidden" name="_token" value="${n(t)}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="antrian-approve-${e.id}-label">Konfirmasi Pesanan ${n(e.order_number)}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted-pos mb-3">
                                Pesanan ${n(e.order_number)} dari <strong class="text-body">${n(e.customer_name)}</strong>
                                (${i(e.total)}) akan menjadi transaksi resmi. Pilih metode pembayaran.
                            </p>
                            <label class="form-label d-block">Metode Pembayaran</label>
                            <div class="d-flex flex-wrap gap-3 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="metodeTunai-${e.id}" value="tunai" checked>
                                    <label class="form-check-label" for="metodeTunai-${e.id}"><i class="bi bi-cash me-1"></i> Tunai</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="metodeQris-${e.id}" value="qris">
                                    <label class="form-check-label" for="metodeQris-${e.id}"><i class="bi bi-qr-code me-1"></i> QRIS</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="metodeWallet-${e.id}" value="ewallet">
                                    <label class="form-check-label" for="metodeWallet-${e.id}"><i class="bi bi-wallet2 me-1"></i> E-Wallet</label>
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
    `}function d(e,t){return`
        <div id="antrian-reject-${e.id}" class="modal fade" tabindex="-1" aria-labelledby="antrian-reject-${e.id}-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="${n(e.reject_url)}" class="reject-form">
                    <input type="hidden" name="_token" value="${n(t)}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="antrian-reject-${e.id}-label">Tolak Pesanan ${n(e.order_number)}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted-pos mb-3">
                                Pesanan ${n(e.order_number)} dari <strong class="text-body">${n(e.customer_name)}</strong>
                                akan ditandai <strong class="text-danger">Ditolak</strong> dan pelanggan akan menerima pemberitahuan beserta alasannya.
                            </p>
                            <label class="form-label" for="alasan-${e.id}">Alasan Penolakan</label>
                            <textarea class="form-control" id="alasan-${e.id}" name="reason" rows="3" placeholder="Contoh: produk habis, alamat di luar jangkauan" required></textarea>
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
    `}function f(e,t){return`
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
`.replace(`{{id}}`,e.id).replace(`{{complete_url}}`,e.complete_url).replace(`{{csrf}}`,t).replace(`{{order_number}}`,e.order_number).replace(`{{customer_name}}`,e.customer_name)}function p(e){let t=document.getElementById(`antrian-pending-section`),n=document.getElementById(`antrian-handled-section`);t&&t.classList.toggle(`d-none`,![`semua`,`menunggu`].includes(e)),n&&n.classList.toggle(`d-none`,![`semua`,`diproses`,`ditolak`].includes(e))}function m(){let n=document.getElementById(`antrian-pending-body`);n&&(n.innerHTML=e);let r=document.getElementById(`antrian-handled-body`);r&&(r.innerHTML=t);let i=document.getElementById(`antrian-pending-count`);i&&(i.textContent=``);let a=document.getElementById(`antrian-handled-count`);a&&(a.textContent=``);let o=document.getElementById(`antrian-menunggu-badge`);o&&(o.innerHTML=`<i class="bi bi-hourglass-split me-1"></i> — pesanan menunggu`);let s=document.getElementById(`antrian-tabs`);s&&(s.innerHTML=`
    <div class="empty-state">
        <span class="empty-state__icon"><i class="bi bi-exclamation-triangle"></i></span>
        <div class="empty-state__title">Data gagal dimuat</div>
        <div class="empty-state__text">Periksa koneksi lalu coba lagi.</div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-antrian-retry>
            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
        </button>
    </div>
`),[s,document.getElementById(`antrian-pending-section`),document.getElementById(`antrian-handled-section`)].forEach(e=>e?.setAttribute(`aria-busy`,`false`))}function h(e){let t=document.getElementById(e);if(!t||!window.bootstrap)return;let n=document.querySelector(`.modal.show`);if(n&&n!==t){let e=window.bootstrap.Modal.getInstance(n);if(e){n.addEventListener(`hidden.bs.modal`,()=>{window.bootstrap.Modal.getOrCreateInstance(t).show()},{once:!0}),e.hide();return}}window.bootstrap.Modal.getOrCreateInstance(t).show()}var g=0;function _(e){[`antrian-tabs`,`antrian-pending-section`,`antrian-handled-section`].forEach(t=>{let n=document.getElementById(t);n&&(n.style.opacity=e?`0.5`:``,n.style.pointerEvents=e?`none`:``)})}async function v(){let e=++g;_(!0);try{let t=`/api/kasir/antrian`+window.location.search,n=await fetch(t,{headers:{Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`},credentials:`same-origin`});if(!n.ok)throw Error(`HTTP ${n.status}`);let r=await n.json();if(e!==g)return;let i=r.data??{},c=document.getElementById(`antrian-modals`)?.dataset.csrf??``;a(i.tabs??[]),p(i.status??`semua`),o(i.pending??[],c),s(i.handled??[])}catch(t){if(e!==g)return;console.error(`Gagal memuat antrian pesanan:`,t),m()}finally{e===g&&_(!1)}}function y(){document.getElementById(`antrian-tabs`)&&(document.addEventListener(`click`,e=>{if(e.target.closest(`[data-antrian-retry]`)){v();return}let t=e.target.closest(`.btn-detail`);if(t){h(`antrian-detail-${t.dataset.orderId}`);return}let n=e.target.closest(`.btn-approve`);if(n){h(`antrian-approve-${n.dataset.orderId}`);return}let r=e.target.closest(`.btn-reject`);if(r){h(`antrian-reject-${r.dataset.orderId}`);return}let i=e.target.closest(`.btn-complete`);if(i){h(`antrian-complete-${i.dataset.orderId}`);return}}),document.addEventListener(`click`,e=>{if(e.button!==0||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey)return;let t=e.target.closest(`#antrian-tabs a[href]`);if(!t)return;e.preventDefault();let n=new URL(t.href,window.location.origin);window.history.pushState({antrian:!0},``,n.pathname+n.search),v()},!0),window.addEventListener(`popstate`,()=>{v()}),document.addEventListener(`submit`,e=>{if(e.target.closest(`form.reject-form`)){if(e.preventDefault(),typeof Swal>`u`){form.submit();return}Swal.fire({title:`Tolak pesanan ini?`,text:`Pesanan akan ditandai ditolak dan pelanggan menerima pemberitahuan beserta alasannya.`,icon:`warning`,showCancelButton:!0,confirmButtonText:`Ya, tolak!`,cancelButtonText:`Batal`,reverseButtons:!0}).then(e=>{e.isConfirmed&&form.submit()});return}let t=e.target.closest(`form.complete-form`);if(t){if(e.preventDefault(),typeof Swal>`u`){t.submit();return}Swal.fire({title:`Tandai pesanan selesai?`,text:`Pesanan akan ditandai selesai dan tidak muncul di antrian lagi.`,icon:`question`,showCancelButton:!0,confirmButtonText:`Ya, selesai!`,cancelButtonText:`Batal`,reverseButtons:!0}).then(e=>{e.isConfirmed&&t.submit()})}}),v())}document.readyState===`loading`?document.addEventListener(`DOMContentLoaded`,y):y();