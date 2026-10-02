import NProgress from 'nprogress';

const DEBOUNCE_MS = 300;
const HEADERS = { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' };
const INITIAL_PARAMS = new URLSearchParams(window.location.search);

let urutan = 0;
let kontroler = null;
let jedaKetik = null;

const wadahHasil = () => document.querySelector('[data-rt-results]');

const paramForm = (form) => {
    const params = new URLSearchParams();

    new FormData(form).forEach((nilai, kunci) => {
        if (nilai !== '') {
            params.set(kunci, nilai);
        }
    });

    params.delete('page');

    const query = params.toString();
    const aksi = form.getAttribute('action') || window.location.pathname;

    return query ? `${aksi}?${query}` : aksi;
};

const sinkronForm = (search) => {
    const params = new URLSearchParams(search);

    document.querySelectorAll('form[data-realtime] [name]').forEach((kontrol) => {
        if (kontrol === document.activeElement) {
            return;
        }

        const nilai = params.get(kontrol.name);
        const awal = INITIAL_PARAMS.has(kontrol.name) ? '' : (kontrol.dataset.rtInitial ?? '');

        if (kontrol.type === 'checkbox') {
            kontrol.checked = nilai !== null;
        } else if (kontrol.type === 'radio') {
            kontrol.checked = nilai !== null && kontrol.value === nilai;
        } else {
            kontrol.value = nilai ?? awal;
        }
    });
};

const sinkronTanggal = (search) => {
    const elemen = document.querySelectorAll('[data-rt-date]');

    if (! elemen.length) {
        return;
    }

    const params = new URLSearchParams(search);
    const nilai = params.get('tanggal') || new Date().toISOString().slice(0, 10);
    const tanggal = new Date(`${nilai}T00:00:00`);

    if (Number.isNaN(tanggal.getTime())) {
        return;
    }

    const label = tanggal.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });

    elemen.forEach((elem) => {
        elem.textContent = label;
    });
};

const NAMA_BULAN = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

const sinkronBulan = (search) => {
    const elemen = document.querySelectorAll('[data-rt-bulan]');

    if (! elemen.length) {
        return;
    }

    const params = new URLSearchParams(search);
    const sekarang = new Date();
    const bulan = Number(params.get('bulan') ?? INITIAL_PARAMS.get('bulan') ?? sekarang.getMonth() + 1);
    const tahun = Number(params.get('tahun') ?? INITIAL_PARAMS.get('tahun') ?? sekarang.getFullYear());
    const label = `${NAMA_BULAN[bulan - 1] ?? ''} ${tahun}`;

    elemen.forEach((elem) => {
        elem.textContent = label;
    });
};

const sinkronTautan = (search) => {
    document.querySelectorAll('a[data-rt-href]').forEach((tautan) => {
        const tujuan = new URL(tautan.getAttribute('href'), window.location.origin);

        tautan.setAttribute('href', tujuan.pathname + search);
    });
};

const muat = async (url, dorong = true) => {
    const wadah = wadahHasil();

    if (! wadah) {
        return;
    }

    if (kontroler) {
        kontroler.abort();
    }

    const id = ++urutan;
    kontroler = new AbortController();

    NProgress.start();
    wadah.style.opacity = '0.5';
    wadah.style.pointerEvents = 'none';

    try {
        const respons = await fetch(url, { headers: HEADERS, signal: kontroler.signal });

        if (! respons.ok) {
            throw new Error(`HTTP ${respons.status}`);
        }

        const html = await respons.text();

        if (id !== urutan) {
            return;
        }

        wadah.innerHTML = html;

        const tujuan = new URL(url, window.location.origin);

        if (dorong) {
            window.history.pushState({ realtime: true }, '', tujuan.pathname + tujuan.search);
        }

        sinkronForm(tujuan.search);
        sinkronTanggal(tujuan.search);
        sinkronBulan(tujuan.search);
        sinkronTautan(tujuan.search);
        document.dispatchEvent(new CustomEvent('realtime:updated'));
    } catch (error) {
        if (error.name !== 'AbortError') {
            console.error('Realtime filter gagal:', error);
        }
    } finally {
        if (id === urutan) {
            wadah.style.opacity = '';
            wadah.style.pointerEvents = '';
            NProgress.done();
        }
    }
};

const jadwalkan = (form) => {
    window.clearTimeout(jedaKetik);
    jedaKetik = window.setTimeout(() => muat(paramForm(form)), DEBOUNCE_MS);
};

const tangkapSubmit = (event) => {
    const form = event.target.closest('form[data-realtime]');

    if (! form || ! wadahHasil()) {
        return;
    }

    event.preventDefault();
    window.clearTimeout(jedaKetik);
    muat(paramForm(form));
};

const tangkapKetik = (event) => {
    const form = event.target.closest?.('form[data-realtime]');

    if (! form || ! wadahHasil() || event.target.matches('select')) {
        return;
    }

    jadwalkan(form);
};

const tangkapUbah = (event) => {
    const kontrol = event.target;

    if (! kontrol.matches('select, input[type="date"], input[type="checkbox"], input[type="radio"]')) {
        return;
    }

    const form = kontrol.closest('form[data-realtime]');

    if (! form || ! wadahHasil()) {
        return;
    }

    window.clearTimeout(jedaKetik);
    muat(paramForm(form));
};

const layakDitangkap = (tautan) => {
    if (! tautan.hasAttribute('href') || tautan.hasAttribute('download') || tautan.hasAttribute('data-no-rt')) {
        return false;
    }

    if (tautan.target === '_blank' || tautan.getAttribute('href').startsWith('#')) {
        return false;
    }

    return tautan.hasAttribute('data-rt-link')
        || tautan.matches('.pagination a[href]')
        || Boolean(tautan.closest('[data-rt-nav]'));
};

const tangkapKlik = (event) => {
    if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || ! wadahHasil()) {
        return;
    }

    const tautan = event.target.closest('a[href]');

    if (! tautan || ! layakDitangkap(tautan)) {
        return;
    }

    const tujuan = new URL(tautan.href, window.location.origin);

    if (tujuan.origin !== window.location.origin || tujuan.pathname !== window.location.pathname) {
        return;
    }

    event.preventDefault();
    window.clearTimeout(jedaKetik);
    muat(tujuan.pathname + tujuan.search);
};

const simpanNilaiAwal = () => {
    document.querySelectorAll('form[data-realtime] [name]').forEach((kontrol) => {
        kontrol.dataset.rtInitial = kontrol.value;
    });
};

const init = () => {
    if (! wadahHasil()) {
        return;
    }

    simpanNilaiAwal();

    document.addEventListener('submit', tangkapSubmit, true);
    document.addEventListener('input', tangkapKetik, true);
    document.addEventListener('change', tangkapUbah, true);
    document.addEventListener('click', tangkapKlik, true);

    window.addEventListener('popstate', () => {
        window.clearTimeout(jedaKetik);
        muat(window.location.pathname + window.location.search, false);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
