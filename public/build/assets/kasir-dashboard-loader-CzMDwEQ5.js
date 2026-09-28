var e={menunggu:`warning`,diproses:`info`,selesai:`success`,ditolak:`danger`},t=`
    <tr>
        <td colspan="7">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-receipt"></i></span>
                <div class="empty-state__title">Belum ada transaksi hari ini</div>
                <div class="empty-state__text">Mulai transaksi baru dari menu Kasir / POS atau tunggu pesanan online masuk.</div>
            </div>
        </td>
    </tr>
`,n=`
    <div class="empty-state">
        <span class="empty-state__icon"><i class="bi bi-check2-circle"></i></span>
        <div class="empty-state__title">Tidak ada pesanan menunggu</div>
        <div class="empty-state__text">Semua pesanan masuk sudah diatasi.</div>
    </div>
`;function r(e){return String(e??``).replace(/[&<>"']/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,'"':`&quot;`,"'":`&#039;`})[e])}function i(e){return Number(e??0).toLocaleString(`id-ID`)}function a(e){return`Rp ${i(e)}`}function o(e){let t=document.getElementById(`kasir-stats`);t.innerHTML=e.map(e=>`
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card stat-card--${r(e.modifier)}">
                <div>
                    <div class="stat-card__label">${r(e.label)}</div>
                    <div class="stat-card__value">${r(e.value)}</div>
                    ${e.delta?`<span class="stat-card__delta ${r(e.delta_modifier??``)}">${r(e.delta)}</span>`:``}
                    ${e.link?`<a href="${r(e.link)}" class="d-block small fw-semibold mt-1">${r(e.link_label??`Lihat Detail`)} </a>`:``}
                </div>
                <div class="stat-card__icon">
                    <i class="bi ${r(e.icon)}"></i>
                </div>
            </div>
        </div>
    `).join(``),t.setAttribute(`aria-busy`,`false`)}function s(e,n){let i=document.getElementById(`kasir-transactions-body`),o=document.getElementById(`kasir-transactions-count`);o.innerHTML=`<span class="badge badge-soft badge-soft--neutral">${e.length} transaksi</span>`,i.innerHTML=e.length===0?t:e.map(e=>`
            <tr>
                <td>
                    <a href="${r(e.url)}" class="fw-semibold">${r(e.id)}</a>
                </td>
                <td class="text-nowrap">${r(e.jam)}</td>
                <td>${r(e.kasir??`-`)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${r(e.jenis_badge)}">${r(e.jenis)}</span>
                </td>
                <td class="fw-semibold text-nowrap">${a(e.total)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${r(n[e.status]??`neutral`)}">
                        ${r(e.status?e.status.charAt(0).toUpperCase()+e.status.slice(1):`-`)}
                    </span>
                </td>
                <td class="text-end">
                    <a href="${r(e.url)}" class="link-secondary" title="Lihat detail transaksi">
                        <i class="bi bi-receipt"></i>
                    </a>
                </td>
            </tr>
        `).join(``),document.getElementById(`kasir-transactions-pane`).setAttribute(`aria-busy`,`false`)}function c(e){let t=document.getElementById(`kasir-pending-orders`),o=document.getElementById(`kasir-pending-count`);o.innerHTML=`<span class="badge badge-soft badge-soft--warning">${e.length}</span>`,t.innerHTML=e.length===0?n:e.map(e=>`
            <div class="order-card">
                <div class="order-card__header">
                    <span class="order-card__id">${r(e.no)}</span>
                    <span class="order-card__time">${r(e.time)}</span>
                </div>
                <div class="order-card__meta">
                    <span class="avatar">${r(String(e.customer??`-`).charAt(0).toUpperCase())}</span>
                    <span class="text-truncate">${r(e.customer)}</span>
                    <span>&middot;</span>
                    <span>${i(e.items)} item</span>
                </div>
                <div class="mt-2">
                    <span class="order-card__total">${a(e.total)}</span>
                </div>
                <div class="order-card__actions">
                    <a href="/kasir/antrian" class="btn btn-brand btn-sm">
                        <i class="bi bi-eye me-1"></i> Tinjau
                    </a>
                </div>
            </div>
        `).join(``),document.getElementById(`kasir-pending-pane`).setAttribute(`aria-busy`,`false`)}function l(){let e=document.getElementById(`kasir-stats`),n=document.getElementById(`kasir-transactions-body`),r=document.getElementById(`kasir-pending-orders`),i=`
        <div class="empty-state">
            <span class="empty-state__icon"><i class="bi bi-exclamation-triangle"></i></span>
            <div class="empty-state__title">Data gagal dimuat</div>
            <div class="empty-state__text">Periksa koneksi lalu coba lagi.</div>
            <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-kasir-dashboard-retry>
                <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
            </button>
        </div>
    `;e.innerHTML=`<div class="col-12">${i}</div>`,n.innerHTML=t,r.innerHTML=i,document.getElementById(`kasir-transactions-count`).innerHTML=``,document.getElementById(`kasir-pending-count`).innerHTML=``,[e,document.getElementById(`kasir-transactions-pane`),document.getElementById(`kasir-pending-pane`)].forEach(e=>e.setAttribute(`aria-busy`,`false`))}async function u(){try{let t=await fetch(`/api/kasir/dashboard`,{headers:{Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`},credentials:`same-origin`});if(!t.ok)throw Error(`HTTP ${t.status}`);let n=(await t.json()).data??{};o(n.stats??[]),s(n.transactions??[],e),c(n.pendingOrders??[])}catch(e){console.error(`Gagal memuat dashboard kasir:`,e),l()}}function d(){document.getElementById(`kasir-stats`)&&(document.addEventListener(`click`,e=>{e.target.closest(`[data-kasir-dashboard-retry]`)&&u()}),u())}document.readyState===`loading`?document.addEventListener(`DOMContentLoaded`,d):d();