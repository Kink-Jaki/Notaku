import{initDashboardChart as e}from"./dashboard-chart-DqYHqzQs.js";var t=`/api/admin/dashboard`,n=`
    <div class="empty-state py-3">
        <div class="empty-state__icon"><i class="bi bi-check-circle"></i></div>
        <div class="empty-state__title">Semua stok aman</div>
        <div class="empty-state__text">Semua produk memiliki stok yang cukup.</div>
    </div>
`,r=`
    <div class="col-12">
        <div class="empty-state py-3">
            <div class="empty-state__icon"><i class="bi bi-people"></i></div>
            <div class="empty-state__title">Belum ada top spender</div>
            <div class="empty-state__text">Top spender akan muncul setelah ada pesanan yang selesai.</div>
        </div>
    </div>
`,i=`
    <tr>
        <td colspan="6">
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-receipt"></i></div>
                <div class="empty-state__title">Belum ada transaksi</div>
                <div class="empty-state__text">Transaksi akan muncul di sini setelah ada pembelian.</div>
            </div>
        </td>
    </tr>
`;function a(e){return String(e??``).replace(/[&<>"']/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,'"':`&quot;`,"'":`&#039;`})[e])}function o(e){return Number(e??0).toLocaleString(`id-ID`)}function s(e){return`Rp ${o(e)}`}function c(e){let t=document.getElementById(`admin-stats`);t.innerHTML=e.map(e=>`
        <div class="col">
            <div class="stat-card stat-card--${a(e.modifier)}">
                <div>
                    <div class="stat-card__label">
                        ${a(e.label)}
                        ${e.hint?`<span class="text-muted-pos fw-normal small">${a(e.hint)}</span>`:``}
                    </div>
                    <div class="stat-card__value">
                        ${a(e.value)}
                        ${e.badge?`<span class="badge badge-soft badge-soft--${a(e.badge)} badge-soft__dot badge-soft--count ms-1">!</span>`:``}
                    </div>
                </div>
                <div class="stat-card__icon">
                    <i class="bi ${a(e.icon)}"></i>
                </div>
            </div>
        </div>
    `).join(``),t.setAttribute(`aria-busy`,`false`)}function l(t){let n=document.getElementById(`admin-sales-chart`);n.innerHTML=`
        <canvas id="salesChart"
            data-labels="${a(JSON.stringify(t.labels??[]))}"
            data-trx="${a(JSON.stringify(t.transactions??[]))}"
            data-omzet="${a(JSON.stringify(t.omzet??[]))}">
        </canvas>
    `,e(),n.setAttribute(`aria-busy`,`false`)}function u(e){let t=document.getElementById(`admin-stocks`),r=document.getElementById(`admin-stocks-count`);r.innerHTML=`<span class="badge badge-soft badge-soft--danger">${e.length} produk</span>`,t.innerHTML=e.length===0?n:e.map(e=>`
            <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="small fw-semibold text-truncate">${a(e.name)}</span>
                <span class="badge badge-soft badge-soft--${a(e.modifier)} text-nowrap">${o(e.stock)} sisa</span>
            </div>
        `).join(``),document.getElementById(`admin-stocks-pane`).setAttribute(`aria-busy`,`false`)}function d(e){let t=document.getElementById(`admin-top-spenders`),n=document.getElementById(`admin-top-spenders-count`);n.innerHTML=`<span class="badge badge-soft badge-soft--warning">${e.length} pelanggan</span>`,t.innerHTML=e.length===0?r:e.map(e=>`
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge badge-soft badge-soft--${a(e.modifier)} text-nowrap fw-semibold">
                        Tier ${a(e.tier)} &middot; #${o(e.rank)}
                    </span>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="small fw-semibold text-truncate">${a(e.name)}</div>
                        <div class="small text-muted-pos">${o(e.orders)} pesanan</div>
                    </div>
                    <span class="small fw-semibold text-nowrap">${s(e.total)}</span>
                </div>
            </div>
        `).join(``),document.getElementById(`admin-top-spenders-pane`).setAttribute(`aria-busy`,`false`)}function f(e){let t=document.getElementById(`admin-transactions-body`),n=document.getElementById(`admin-transactions-count`);n.innerHTML=`<span class="badge badge-soft badge-soft--info">${e.length} transaksi</span>`,t.innerHTML=e.length===0?i:e.map(e=>`
            <tr>
                <td class="fw-semibold text-nowrap">${a(e.number)}</td>
                <td class="text-nowrap">${a(e.time)}</td>
                <td>${a(e.cashier)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${a(e.type_modifier)}">${a(e.type)}</span>
                </td>
                <td class="fw-semibold text-nowrap">${s(e.total)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${e.status===`selesai`?`success`:`neutral`}">
                        ${a(e.status?e.status.charAt(0).toUpperCase()+e.status.slice(1):`-`)}
                    </span>
                </td>
            </tr>
        `).join(``),document.getElementById(`admin-transactions-pane`).setAttribute(`aria-busy`,`false`)}function p(e){let t={products:e.products,users:e.users,promos:e.promos};document.querySelectorAll(`[data-summary-key]`).forEach(e=>{e.textContent=o(t[e.dataset.summaryKey])})}function m(){let e=`
        <div class="empty-state">
            <div class="empty-state__icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="empty-state__title">Data gagal dimuat</div>
            <div class="empty-state__text">Periksa koneksi lalu coba lagi.</div>
            <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-admin-dashboard-retry>
                <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
            </button>
        </div>
    `,t=document.getElementById(`admin-stats`),n=document.getElementById(`admin-sales-chart`),r=document.getElementById(`admin-stocks`),a=document.getElementById(`admin-top-spenders`),o=document.getElementById(`admin-transactions-body`);t.innerHTML=`<div class="col-12">${e}</div>`,n.innerHTML=e,r.innerHTML=e,a.innerHTML=`<div class="col-12">${e}</div>`,o.innerHTML=i,document.getElementById(`admin-stocks-count`).innerHTML=``,document.getElementById(`admin-top-spenders-count`).innerHTML=``,document.getElementById(`admin-transactions-count`).innerHTML=``,document.querySelectorAll(`[data-summary-key]`).forEach(e=>{e.textContent=`—`}),[t,n,document.getElementById(`admin-stocks-pane`),document.getElementById(`admin-top-spenders-pane`),document.getElementById(`admin-transactions-pane`)].forEach(e=>e.setAttribute(`aria-busy`,`false`))}async function h(){try{let e=await fetch(t,{headers:{Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`},credentials:`same-origin`});if(!e.ok)throw Error(`HTTP ${e.status}`);let n=(await e.json()).data??{};c(n.stats??[]),l(n.chart??{}),u(n.stocks??[]),d(n.topSpenders??[]),f(n.recentTransactions??[]),p(n.summary??{})}catch(e){console.error(`Gagal memuat dashboard admin:`,e),m()}}function g(){document.getElementById(`admin-stats`)&&(document.addEventListener(`click`,e=>{e.target.closest(`[data-admin-dashboard-retry]`)&&h()}),h())}document.readyState===`loading`?document.addEventListener(`DOMContentLoaded`,g):g();