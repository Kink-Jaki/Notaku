import{initDashboardChart as e}from"./dashboard-chart-CpZ534_Z.js";var t=`/api/admin/dashboard`,n=`
    <div class="empty-state py-3">
        <div class="empty-state__icon"><i class="bi bi-check-circle"></i></div>
        <div class="empty-state__title">Semua stok aman</div>
        <div class="empty-state__text">Semua produk memiliki stok yang cukup.</div>
    </div>
`,r=`
    <tr>
        <td colspan="6">
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-receipt"></i></div>
                <div class="empty-state__title">Belum ada transaksi</div>
                <div class="empty-state__text">Transaksi akan muncul di sini setelah ada pembelian.</div>
            </div>
        </td>
    </tr>
`;function i(e){return String(e??``).replace(/[&<>"']/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,'"':`&quot;`,"'":`&#039;`})[e])}function a(e){return Number(e??0).toLocaleString(`id-ID`)}function o(e){return`Rp ${a(e)}`}function s(e){let t=document.getElementById(`admin-stats`);t.innerHTML=e.map(e=>`
        <div class="col">
            <div class="stat-card stat-card--${i(e.modifier)}">
                <div>
                    <div class="stat-card__label">
                        ${i(e.label)}
                        ${e.hint?`<span class="text-muted-pos fw-normal small">${i(e.hint)}</span>`:``}
                    </div>
                    <div class="stat-card__value">
                        ${i(e.value)}
                        ${e.badge?`<span class="badge badge-soft badge-soft--${i(e.badge)} badge-soft__dot badge-soft--count ms-1">!</span>`:``}
                    </div>
                </div>
                <div class="stat-card__icon">
                    <i class="bi ${i(e.icon)}"></i>
                </div>
            </div>
        </div>
    `).join(``),t.setAttribute(`aria-busy`,`false`)}function c(t){let n=document.getElementById(`admin-sales-chart`);n.innerHTML=`
        <canvas id="salesChart"
            data-labels="${i(JSON.stringify(t.labels??[]))}"
            data-trx="${i(JSON.stringify(t.transactions??[]))}"
            data-omzet="${i(JSON.stringify(t.omzet??[]))}">
        </canvas>
    `,e(),n.setAttribute(`aria-busy`,`false`)}function l(e){let t=document.getElementById(`admin-stocks`),r=document.getElementById(`admin-stocks-count`);r.innerHTML=`<span class="badge badge-soft badge-soft--danger">${e.length} produk</span>`,t.innerHTML=e.length===0?n:e.map(e=>`
            <div class="d-flex justify-content-between align-items-center gap-2">
                <span class="small fw-semibold text-truncate">${i(e.name)}</span>
                <span class="badge badge-soft badge-soft--${i(e.modifier)} text-nowrap">${a(e.stock)} sisa</span>
            </div>
        `).join(``),document.getElementById(`admin-stocks-pane`).setAttribute(`aria-busy`,`false`)}function u(e){let t=document.getElementById(`admin-transactions-body`),n=document.getElementById(`admin-transactions-count`);n.innerHTML=`<span class="badge badge-soft badge-soft--info">${e.length} transaksi</span>`,t.innerHTML=e.length===0?r:e.map(e=>`
            <tr>
                <td class="fw-semibold text-nowrap">${i(e.number)}</td>
                <td class="text-nowrap">${i(e.time)}</td>
                <td>${i(e.cashier)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${i(e.type_modifier)}">${i(e.type)}</span>
                </td>
                <td class="fw-semibold text-nowrap">${o(e.total)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${e.status===`selesai`?`success`:`neutral`}">
                        ${i(e.status?e.status.charAt(0).toUpperCase()+e.status.slice(1):`-`)}
                    </span>
                </td>
            </tr>
        `).join(``),document.getElementById(`admin-transactions-pane`).setAttribute(`aria-busy`,`false`)}function d(e){let t={products:e.products,users:e.users,promos:e.promos};document.querySelectorAll(`[data-summary-key]`).forEach(e=>{e.textContent=a(t[e.dataset.summaryKey])})}function f(){let e=`
        <div class="empty-state">
            <div class="empty-state__icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="empty-state__title">Data gagal dimuat</div>
            <div class="empty-state__text">Periksa koneksi lalu coba lagi.</div>
            <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-admin-dashboard-retry>
                <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
            </button>
        </div>
    `,t=document.getElementById(`admin-stats`),n=document.getElementById(`admin-sales-chart`),i=document.getElementById(`admin-stocks`),a=document.getElementById(`admin-transactions-body`);t.innerHTML=`<div class="col-12">${e}</div>`,n.innerHTML=e,i.innerHTML=e,a.innerHTML=r,document.getElementById(`admin-stocks-count`).innerHTML=``,document.getElementById(`admin-transactions-count`).innerHTML=``,document.querySelectorAll(`[data-summary-key]`).forEach(e=>{e.textContent=`—`}),[t,n,document.getElementById(`admin-stocks-pane`),document.getElementById(`admin-transactions-pane`)].forEach(e=>e.setAttribute(`aria-busy`,`false`))}async function p(){try{let e=await fetch(t,{headers:{Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`},credentials:`same-origin`});if(!e.ok)throw Error(`HTTP ${e.status}`);let n=(await e.json()).data??{};s(n.stats??[]),c(n.chart??{}),l(n.stocks??[]),u(n.recentTransactions??[]),d(n.summary??{})}catch(e){console.error(`Gagal memuat dashboard admin:`,e),f()}}function m(){document.getElementById(`admin-stats`)&&(document.addEventListener(`click`,e=>{e.target.closest(`[data-admin-dashboard-retry]`)&&p()}),p())}document.readyState===`loading`?document.addEventListener(`DOMContentLoaded`,m):m();