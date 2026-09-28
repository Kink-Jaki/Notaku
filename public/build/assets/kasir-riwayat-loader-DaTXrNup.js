var e={selesai:`success`,dibatalkan:`danger`},t=`
    <tr>
        <td colspan="9">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-inbox"></i></span>
                <div class="empty-state__title">Belum ada transaksi</div>
                <div class="empty-state__text">Tidak ada transaksi pada filter yang dipilih.</div>
            </div>
        </td>
    </tr>
`,n=`
    <div class="empty-state">
        <span class="empty-state__icon"><i class="bi bi-exclamation-triangle"></i></span>
        <div class="empty-state__title">Data gagal dimuat</div>
        <div class="empty-state__text">Periksa koneksi lalu coba lagi.</div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-riwayat-retry>
            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
        </button>
    </div>
`;function r(e){return String(e??``).replace(/[&<>"']/g,e=>({"&":`&`,"<":`<`,">":`>`,'"':`"`,"'":`&#039;`})[e])}function i(e){return Number(e??0).toLocaleString(`id-ID`)}function a(e){return`Rp ${i(e)}`}function o(e){let t=document.getElementById(`riwayat-badge-transaksi`),n=document.getElementById(`riwayat-badge-penjualan`);t&&(t.textContent=e.total_transaksi??`—`),n&&(n.textContent=e.total_penjualan??`—`)}function s(e){let t=document.getElementById(`riwayat-stats`);t.innerHTML=e.map(e=>`
        <div class="col-12 col-md-4">
            <div class="stat-card stat-card--${r(e.modifier)}">
                <div>
                    <div class="stat-card__label">${r(e.label)}</div>
                    <div class="stat-card__value">${r(e.value)}</div>
                </div>
                <div class="stat-card__icon">
                    <i class="bi ${r(e.icon)}"></i>
                </div>
            </div>
        </div>
    `).join(``),t.setAttribute(`aria-busy`,`false`)}function c(e,n){let i=document.getElementById(`riwayat-body`);i.innerHTML=e.length===0?t:e.map(e=>`
            <tr>
                <td>
                    <a href="${r(e.detail_url)}" class="fw-semibold font-monospace">
                        ${r(e.transaction_number)}
                    </a>
                    ${e.order_number?`<div class="small text-muted-pos font-monospace">${r(e.order_number)}</div>`:``}
                </td>
                <td class="text-nowrap">
                    <div>${r(e.tanggal)}</div>
                    <div class="small text-muted-pos">${r(e.jam)}</div>
                </td>
                <td class="text-nowrap">${r(e.kasir??`-`)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${r(e.jenis_badge)}">
                        ${r(e.jenis)}
                    </span>
                </td>
                <td class="text-nowrap">${r(e.metode)}</td>
                <td class="text-nowrap">${r(e.item)} item</td>
                <td class="fw-semibold text-nowrap">${a(e.total)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${r(n[e.status]??`neutral`)}">
                        <span class="badge-soft__dot"></span>${r(e.status?e.status.charAt(0).toUpperCase()+e.status.slice(1):`-`)}
                    </span>
                </td>
                <td class="text-end">
                    <a href="${r(e.detail_url)}" class="link-secondary" title="Lihat detail transaksi">
                        <i class="bi bi-receipt"></i>
                    </a>
                </td>
            </tr>
        `).join(``),i.setAttribute(`aria-busy`,`false`)}function l(e,t){let n=document.getElementById(`riwayat-info`),r=document.getElementById(`riwayat-pagination`);n.textContent=`Menampilkan ${e.first??0}–${e.last??0} dari ${e.total??0} transaksi`,r.innerHTML=t??``}function u(){document.getElementById(`riwayat-stats`).innerHTML=`<div class="col-12">${n}</div>`,document.getElementById(`riwayat-body`).innerHTML=t,document.getElementById(`riwayat-info`).textContent=``,document.getElementById(`riwayat-pagination`).innerHTML=``,[document.getElementById(`riwayat-stats`),document.getElementById(`riwayat-body`)].forEach(e=>e?.setAttribute(`aria-busy`,`false`))}async function d(){try{let t=`/api/kasir/riwayat`+window.location.search,n=await fetch(t,{headers:{Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`},credentials:`same-origin`});if(!n.ok)throw Error(`HTTP ${n.status}`);let r=(await n.json()).data??{};o(r.badges??{}),s(r.stat_cards??[]),c(r.rows??[],e),l(r.summary??{},r.pagination??``)}catch(e){console.error(`Gagal memuat riwayat transaksi:`,e),u()}}function f(){document.getElementById(`riwayat-body`)&&(document.addEventListener(`click`,e=>{e.target.closest(`[data-riwayat-retry]`)&&d()}),d())}document.readyState===`loading`?document.addEventListener(`DOMContentLoaded`,f):f();