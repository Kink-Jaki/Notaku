var e=0,t=null,n=null,r={selesai:`success`,dibatalkan:`danger`},i=`
    <tr>
        <td colspan="9">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-inbox"></i></span>
                <div class="empty-state__title">Belum ada transaksi</div>
                <div class="empty-state__text">Tidak ada transaksi pada filter yang dipilih.</div>
            </div>
        </td>
    </tr>
`,a=`
    <tr data-cf-empty hidden>
        <td colspan="9">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-search"></i></span>
                <div class="empty-state__title">Tidak ada transaksi yang cocok</div>
                <div class="empty-state__text">Coba ubah kata kunci atau filter di atas.</div>
            </div>
        </td>
    </tr>
`,o=`
    <div class="empty-state">
        <span class="empty-state__icon"><i class="bi bi-exclamation-triangle"></i></span>
        <div class="empty-state__title">Data gagal dimuat</div>
        <div class="empty-state__text">Periksa koneksi lalu coba lagi.</div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-riwayat-retry>
            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
        </button>
    </div>
`;function s(e){return String(e??``).replace(/[&<>"']/g,e=>({"&":`&`,"<":`<`,">":`>`,'"':`"`,"'":`&#039;`})[e])}function c(e){return Number(e??0).toLocaleString(`id-ID`)}function l(e){return`Rp ${c(e)}`}function u(e){let t=document.getElementById(`riwayat-badge-transaksi`),n=document.getElementById(`riwayat-badge-penjualan`);t&&(t.textContent=e.total_transaksi??`—`),n&&(n.textContent=e.total_penjualan??`—`)}function d(e){let t=document.getElementById(`riwayat-stats`);t.innerHTML=e.map(e=>`
        <div class="col-12 col-md-4">
            <div class="stat-card stat-card--${s(e.modifier)}">
                <div>
                    <div class="stat-card__label">${s(e.label)}</div>
                    <div class="stat-card__value">${s(e.value)}</div>
                </div>
                <div class="stat-card__icon">
                    <i class="bi ${s(e.icon)}"></i>
                </div>
            </div>
        </div>
    `).join(``),t.setAttribute(`aria-busy`,`false`)}function f(e,t){let n=document.getElementById(`riwayat-body`);n.innerHTML=e.length===0?i:e.map(e=>`
            <tr data-cf-row
                data-cf-name="${s(`${e.transaction_number} ${e.order_number??``}`)}"
                data-jenis="${s(e.jenis)}"
                data-status="${s(e.status??`selesai`)}"
                data-total="${s(e.total)}">
                <td>
                    <a href="${s(e.detail_url)}" class="fw-semibold font-monospace">
                        ${s(e.transaction_number)}
                    </a>
                    ${e.order_number?`<div class="small text-muted-pos font-monospace">${s(e.order_number)}</div>`:``}
                </td>
                <td class="text-nowrap">
                    <div>${s(e.tanggal)}</div>
                    <div class="small text-muted-pos">${s(e.jam)}</div>
                </td>
                <td class="text-nowrap">${s(e.kasir??`-`)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${s(e.jenis_badge)}">
                        ${s(e.jenis)}
                    </span>
                </td>
                <td class="text-nowrap">${s(e.metode)}</td>
                <td class="text-nowrap">${s(e.item)} item</td>
                <td class="fw-semibold text-nowrap">${l(e.total)}</td>
                <td>
                    <span class="badge badge-soft badge-soft--${s(t[e.status]??`neutral`)}">
                        <span class="badge-soft__dot"></span>${s(e.status?e.status.charAt(0).toUpperCase()+e.status.slice(1):`-`)}
                    </span>
                </td>
                <td class="text-end">
                    <a href="${s(e.detail_url)}" class="link-secondary" title="Lihat detail transaksi">
                        <i class="bi bi-receipt"></i>
                    </a>
                </td>
            </tr>
        `).join(``)+a,n.setAttribute(`aria-busy`,`false`)}function p(e,t){let n=document.getElementById(`riwayat-info`),r=document.getElementById(`riwayat-pagination`);n.textContent=`Menampilkan ${e.first??0}–${e.last??0} dari ${e.total??0} transaksi`,r.innerHTML=t??``}function m(){document.getElementById(`riwayat-stats`).innerHTML=`<div class="col-12">${o}</div>`,document.getElementById(`riwayat-body`).innerHTML=i,document.getElementById(`riwayat-info`).textContent=``,document.getElementById(`riwayat-pagination`).innerHTML=``,[document.getElementById(`riwayat-stats`),document.getElementById(`riwayat-body`)].forEach(e=>e?.setAttribute(`aria-busy`,`false`))}function h(e){[`riwayat-stats`,`riwayat-body`,`riwayat-pagination`,`riwayat-info`].forEach(t=>{let n=document.getElementById(t);n&&(n.style.opacity=e?`0.5`:``,n.style.pointerEvents=e?`none`:``)})}async function g(){let t=++e;h(!0);try{let i=`/api/kasir/riwayat`+window.location.search,a=await fetch(i,{headers:{Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`},credentials:`same-origin`});if(!a.ok)throw Error(`HTTP ${a.status}`);let o=await a.json();if(t!==e)return;let s=o.data??{};n={badges:s.badges??{},stat_cards:s.stat_cards??[]},u(s.badges??{}),d(s.stat_cards??[]),f(s.rows??[],r),p(s.summary??{},s.pagination??``),document.dispatchEvent(new CustomEvent(`cf:refresh`))}catch(n){if(t!==e)return;console.error(`Gagal memuat riwayat transaksi:`,n),m()}finally{t===e&&h(!1)}}function _(){let e=document.getElementById(`riwayat-filter`),t=new URLSearchParams;return e&&new FormData(e).forEach((e,n)=>{e!==``&&t.set(n,e)}),t.delete(`page`),t}function v(e){let n=e.toString();window.clearTimeout(t),window.history.pushState({riwayat:!0},``,n?`?${n}`:window.location.pathname),y(),b(e),g()}function y(){let e=new URLSearchParams(window.location.search);document.querySelectorAll(`#riwayat-filter [name]`).forEach(t=>{t!==document.activeElement&&(t.value=e.get(t.name)??t.dataset.rtInitial??``)})}function b(e){let t=e.get(`dari`);document.querySelectorAll(`.btn-group[role="group"] a`).forEach(e=>{let n=new URL(e.href,window.location.origin).searchParams.get(`dari`);e.classList.toggle(`active`,!!t&&n===t)})}function x(){document.getElementById(`riwayat-body`)&&(document.querySelectorAll(`#riwayat-filter [name]`).forEach(e=>{e.dataset.rtInitial=e.value}),document.addEventListener(`click`,e=>{e.target.closest(`[data-riwayat-retry]`)&&g()}),document.addEventListener(`submit`,e=>{e.target.closest(`#riwayat-filter`)&&(e.preventDefault(),v(_()))},!0),document.addEventListener(`input`,e=>{e.target.matches(`#riwayat-filter input[type="date"]`)&&(window.clearTimeout(t),t=window.setTimeout(()=>v(_()),300))},!0),document.addEventListener(`change`,e=>{e.target.matches(`#riwayat-filter input[type="date"]`)&&v(_())},!0),document.addEventListener(`click`,e=>{if(e.button!==0||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey)return;let t=e.target.closest(`.btn-group[role="group"] a[href], #riwayat-pagination a[href], a[data-rt-link]`);!t||t.getAttribute(`href`).startsWith(`#`)||t.hasAttribute(`data-no-rt`)||(e.preventDefault(),v(new URL(t.href,window.location.origin).searchParams))},!0),window.addEventListener(`popstate`,()=>{y(),b(new URLSearchParams(window.location.search)),g()}),document.addEventListener(`cf:applied`,e=>{if(!n||e.detail?.kunci!==`riwayat`)return;if(!e.detail.adaFilter){u(n.badges),d(n.stat_cards);return}let t=e.detail.baris,r=t.length,i=t.reduce((e,t)=>e+(Number(t.dataset.total)||0),0);u({total_transaksi:c(r),total_penjualan:l(i)}),d([{label:`Total Transaksi`,value:c(r),icon:`bi-receipt`,modifier:`primary`},{label:`Total Penjualan`,value:l(i),icon:`bi-cash-stack`,modifier:`success`},{label:`Rata-rata Transaksi`,value:l(r>0?Math.round(i/r):0),icon:`bi-graph-up`,modifier:`info`}])}),g())}document.readyState===`loading`?document.addEventListener(`DOMContentLoaded`,x):x();