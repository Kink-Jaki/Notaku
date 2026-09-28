var e=[`lh-tanggal-label`,`lh-badge-penjualan`,`lh-stats`,`lh-metode-count`,`lh-metode-body`,`lh-metode-foot`,`lh-rincian-count`,`lh-rincian-body`,`lh-rincian-foot`,`lh-ringkasan-badge`,`lh-ringkasan-body`],t=[`lh-metode-pane`,`lh-rincian-pane`,`lh-ringkasan-pane`],n={Kasir:`success`,Online:`info`},r=`
    <tr>
        <td colspan="4">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-cash-coin"></i></span>
                <div class="empty-state__title">Belum ada penjualan</div>
                <div class="empty-state__text">Tidak ada transaksi selesai pada filter ini.</div>
            </div>
        </td>
    </tr>
`,i=`
    <tr>
        <td colspan="5">
            <div class="empty-state">
                <span class="empty-state__icon"><i class="bi bi-receipt"></i></span>
                <div class="empty-state__title">Belum ada rincian transaksi</div>
                <div class="empty-state__text">Coba ganti tanggal atau pilih kasir lain.</div>
            </div>
        </td>
    </tr>
`,a=`
    <div class="empty-state">
        <span class="empty-state__icon"><i class="bi bi-exclamation-triangle"></i></span>
        <div class="empty-state__title">Data gagal dimuat</div>
        <div class="empty-state__text">Periksa koneksi lalu coba lagi.</div>
        <button type="button" class="btn btn-outline-primary btn-sm mt-3" data-laporan-harian-retry>
            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang
        </button>
    </div>
`;function o(e){return String(e??``).replace(/[&<>"']/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,'"':`&quot;`,"'":`&#039;`})[e])}function s(){if(!document.getElementById(`lh-stats`))return;let s={},c={},l=document.getElementById(`lh-filter`),u=document.getElementById(`tanggalLaporan`),d=document.getElementById(`kasirFilter`);e.concat(t).forEach(e=>{s[e]=document.getElementById(e)}),e.forEach(e=>{c[e]=s[e].innerHTML});let f=0,p=e=>{let t=e.get(`tanggal`),n=e.get(`kasir_id`);return/^\d{4}-\d{2}-\d{2}$/.test(t||``)||e.delete(`tanggal`),n&&!/^\d+$/.test(n)&&e.delete(`kasir_id`),e},m=()=>p(new URLSearchParams(window.location.search)),h=()=>{let e=new URLSearchParams;return u.value&&e.set(`tanggal`,u.value),d.value&&e.set(`kasir_id`,d.value),p(e)},g=e=>{u.value=e.get(`tanggal`)||u.defaultValue,d.value=e.get(`kasir_id`)||``},_=()=>{e.forEach(e=>{s[e].innerHTML=c[e],s[e].setAttribute(`aria-busy`,`true`)}),t.forEach(e=>s[e].setAttribute(`aria-busy`,`true`))},v=()=>{e.concat(t).forEach(e=>s[e].setAttribute(`aria-busy`,`false`))},y=e=>{s[`lh-tanggal-label`].textContent=e.tanggal_label??``},b=e=>{s[`lh-badge-penjualan`].innerHTML=`
            <span class="badge badge-soft badge-soft--success fs-6 fw-semibold">
                <i class="bi bi-cash-stack me-1"></i> ${o(e.penjualan_harian)} hari ini
            </span>
        `},x=e=>{s[`lh-stats`].innerHTML=e.map(e=>`
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card stat-card--${o(e.modifier)}">
                    <div>
                        <div class="stat-card__label">${o(e.label)}</div>
                        <div class="stat-card__value text-truncate" title="${o(e.value)}">${o(e.value)}</div>
                    </div>
                    <div class="stat-card__icon">
                        <i class="bi ${o(e.icon)}"></i>
                    </div>
                </div>
            </div>
        `).join(``)},S=e=>{s[`lh-metode-count`].innerHTML=`<span class="badge badge-soft badge-soft--neutral">${e.count} metode</span>`,s[`lh-metode-body`].innerHTML=e.rows.length?e.rows.map(e=>`
                <tr>
                    <td><i class="bi ${o(e.icon)} me-2"></i>${o(e.nama)}</td>
                    <td class="text-nowrap">${e.trx} trx</td>
                    <td class="text-end fw-semibold text-nowrap">${o(e.total_formatted)}</td>
                    <td class="text-end">
                        <span class="badge badge-soft badge-soft--${o(e.badge)}">${e.persen}%</span>
                    </td>
                </tr>
            `).join(``):r,s[`lh-metode-foot`].innerHTML=e.rows.length?`<tr class="fw-semibold">
                <td class="border-top">Total</td>
                <td class="border-top text-nowrap">${e.count} metode</td>
                <td class="border-top text-end text-nowrap">${o(e.total)}</td>
                <td class="border-top text-end">100%</td>
            </tr>`:``},C=e=>{s[`lh-rincian-count`].innerHTML=`<span class="badge badge-soft badge-soft--success">${e.count} transaksi</span>`,s[`lh-rincian-body`].innerHTML=e.rows.length?e.rows.map(e=>`
                <tr>
                    <td class="text-nowrap">${o(e.jam)}</td>
                    <td class="font-monospace text-nowrap">${o(e.id)}</td>
                    <td>
                        <span class="badge badge-soft badge-soft--${o(n[e.jenis]??`neutral`)}">
                            ${o(e.jenis)}
                        </span>
                    </td>
                    <td class="text-nowrap">${o(e.metode)}</td>
                    <td class="text-end fw-semibold text-nowrap">${o(e.total_formatted)}</td>
                </tr>
            `).join(``):i,s[`lh-rincian-foot`].innerHTML=e.rows.length?`<tr class="fw-semibold">
                <td class="border-top" colspan="4">Total</td>
                <td class="border-top text-end text-nowrap">${o(e.total)}</td>
            </tr>`:``},w=e=>{s[`lh-ringkasan-badge`].innerHTML=`<span class="badge badge-soft badge-soft--neutral">${o(e.tanggal)}</span>`,s[`lh-ringkasan-body`].innerHTML=e.items.map(e=>`
            <div class="col-sm-12 col-md-4">
                <div class="note-box h-100">
                    <i class="bi ${o(e.icon)} note-box__icon"></i>
                    <span>
                        <span class="d-block small text-muted-pos">${o(e.label)}</span>
                        <span class="fw-semibold text-nowrap">${o(e.value)}</span>
                    </span>
                </div>
            </div>
        `).join(``)},T=e=>{y(e),b(e),x(e.stat_cards??[]),S(e.metode??{count:0,total:`Rp 0`,rows:[]}),C(e.rincian??{count:0,total:`Rp 0`,rows:[]}),w(e.ringkasan??{tanggal:``,items:[]}),v()},E=()=>{s[`lh-tanggal-label`].textContent=`-`,s[`lh-badge-penjualan`].innerHTML=``,s[`lh-stats`].innerHTML=`<div class="col-12">${a}</div>`,s[`lh-metode-count`].innerHTML=``,s[`lh-metode-body`].innerHTML=`<tr><td colspan="4">${a}</td></tr>`,s[`lh-metode-foot`].innerHTML=``,s[`lh-rincian-count`].innerHTML=``,s[`lh-rincian-body`].innerHTML=`<tr><td colspan="5">${a}</td></tr>`,s[`lh-rincian-foot`].innerHTML=``,s[`lh-ringkasan-badge`].innerHTML=``,s[`lh-ringkasan-body`].innerHTML=`<div class="col-12">${a}</div>`,v()},D=async e=>{let t=++f;_();try{let n=await fetch(`/api/kasir/laporan-harian?${e.toString()}`,{headers:{Accept:`application/json`,"X-Requested-With":`XMLHttpRequest`},credentials:`same-origin`});if(!n.ok)throw Error(`HTTP ${n.status}`);let r=await n.json();if(t!==f)return;T(r.data??{})}catch(e){if(t!==f)return;console.error(`Gagal memuat laporan harian:`,e),E()}},O=e=>{let t=e.toString();window.history.pushState(null,``,t?`?${t}`:window.location.pathname),D(e)};l.addEventListener(`submit`,e=>{e.preventDefault(),O(h())}),u.addEventListener(`change`,()=>O(h())),d.addEventListener(`change`,()=>O(h())),document.addEventListener(`click`,e=>{e.target.closest(`[data-laporan-harian-retry]`)&&D(h())}),window.addEventListener(`popstate`,()=>{let e=m();g(e),D(e)}),D(m())}document.readyState===`loading`?document.addEventListener(`DOMContentLoaded`,s):s();