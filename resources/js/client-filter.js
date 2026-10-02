const DEBOUNCE_MS = 250;
const SEMUA_NILAI = ['', '0', 'semua', 'all'];

const norm = (nilai) => String(nilai ?? '').toLowerCase().replace(/\s+/g, ' ').trim();

const semuaNilai = (nilai) => nilai === null || nilai === undefined || SEMUA_NILAI.includes(String(nilai).toLowerCase());

function bangunGrup() {
    const peta = new Map();

    document.querySelectorAll('[data-cf]').forEach((elemen) => {
        const kunci = elemen.getAttribute('data-cf') || 'default';

        if (!peta.has(kunci)) {
            peta.set(kunci, []);
        }

        peta.get(kunci).push(elemen);
    });

    return peta;
}

function pasangGrup(kunci, zona) {
    const cari = zona.flatMap((z) => Array.from(z.querySelectorAll('[data-cf-search]')));
    const pilih = zona.flatMap((z) => Array.from(z.querySelectorAll('select[data-cf-field]')));
    const daftarTautan = () => zona.flatMap((z) => Array.from(z.querySelectorAll('a[data-cf-field]')));
    const kontrolCari = cari[0] ?? null;
    const kunciPilih = new Set(pilih.map((sel) => sel.dataset.cfField));

    cari.forEach((input) => {
        input.dataset.cfInitial = input.value;
    });
    pilih.forEach((sel) => {
        sel.dataset.cfInitial = sel.value;
    });

    const state = { cari: '', field: {} };

    const baca = () => {
        state.cari = norm(kontrolCari?.value);
        state.field = {};

        pilih.forEach((sel) => {
            state.field[sel.dataset.cfField] = sel.value;
        });

        daftarTautan().forEach((tautan) => {
            const field = tautan.dataset.cfField;

            if (kunciPilih.has(field)) {
                return;
            }

            if (tautan.classList.contains('active')) {
                state.field[field] = tautan.dataset.cfValue ?? '';
            }
        });
    };

    const daftarBaris = () => zona.flatMap((z) => Array.from(z.querySelectorAll('[data-cf-row]')));

    const terapkan = () => {
        const baris = daftarBaris();
        const terlihat = [];

        baris.forEach((row) => {
            const teks = row.dataset.cfName !== undefined ? row.dataset.cfName : row.textContent;
            const cocokCari = state.cari === '' || norm(teks).includes(state.cari);
            const cocokField = Object.entries(state.field)
                .every(([field, nilai]) => semuaNilai(nilai) || row.dataset[field] === nilai);
            const sesuai = cocokCari && cocokField;

            row.classList.toggle('d-none', ! sesuai);

            if (sesuai) {
                terlihat.push(row);
            }
        });

        const adaFilter = state.cari !== ''
            || Object.entries(state.field).some(([, nilai]) => ! semuaNilai(nilai));

        zona.forEach((z) => {
            z.querySelectorAll('[data-cf-empty]').forEach((elemen) => {
                elemen.hidden = ! (baris.length > 0 && terlihat.length === 0);
            });

            z.querySelectorAll('[data-cf-summary]').forEach((elemen) => {
                const format = elemen.dataset.cfSummary;
                const teks = elemen.textContent;

                if (elemen.dataset.cfTerakhirDitulis !== teks) {
                    elemen.dataset.cfSummaryIdle = teks;
                }

                if (adaFilter && format) {
                    const hasil = format.replace('{n}', terlihat.length);

                    elemen.dataset.cfTerakhirDitulis = hasil;
                    elemen.textContent = hasil;
                } else {
                    elemen.textContent = elemen.dataset.cfSummaryIdle ?? teks;
                    delete elemen.dataset.cfTerakhirDitulis;
                }
            });
        });

        document.dispatchEvent(new CustomEvent('cf:applied', {
            detail: { kunci, baris: terlihat, adaFilter },
        }));
    };

    const namaParam = () => {
        const nama = [];

        if (kontrolCari?.name) {
            nama.push(kontrolCari.name);
        }

        pilih.forEach((sel) => {
            nama.push(sel.name || sel.dataset.cfField);
        });

        daftarTautan().forEach((tautan) => {
            if (! kunciPilih.has(tautan.dataset.cfField)) {
                nama.push(tautan.dataset.cfField);
            }
        });

        return [...new Set(nama)];
    };

    const isiParamFilter = (params) => {
        namaParam().forEach((nama) => {
            params.delete(nama);
        });

        if (kontrolCari?.name && kontrolCari.value.trim() !== '') {
            params.set(kontrolCari.name, kontrolCari.value.trim());
        }

        pilih.forEach((sel) => {
            if (! semuaNilai(sel.value)) {
                params.set(sel.name || sel.dataset.cfField, sel.value);
            }
        });

        daftarTautan().forEach((tautan) => {
            const field = tautan.dataset.cfField;

            if (kunciPilih.has(field) || ! tautan.classList.contains('active')) {
                return;
            }

            if (! semuaNilai(tautan.dataset.cfValue)) {
                params.set(field, tautan.dataset.cfValue);
            }
        });

        return params;
    };

    const sinkronURL = (dorong) => {
        const tujuan = new URL(window.location.href);

        isiParamFilter(tujuan.searchParams);

        const path = tujuan.pathname + (tujuan.searchParams.toString() ? `?${tujuan.searchParams}` : '');

        if (dorong) {
            window.history.pushState({ cf: true }, '', path);
        } else {
            window.history.replaceState({ cf: true }, '', path);
        }
    };

    const sinkronTautanHalaman = () => {
        zona.forEach((z) => {
            z.querySelectorAll('.pagination a[href]').forEach((tautan) => {
                const tujuan = new URL(tautan.getAttribute('href'), window.location.origin);

                isiParamFilter(tujuan.searchParams);
                tautan.setAttribute('href', tujuan.pathname + tujuan.search);
            });
        });
    };

    const segarkan = () => {
        baca();
        terapkan();
        sinkronTautanHalaman();
    };

    const seedingDariURL = () => {
        const params = new URLSearchParams(window.location.search);

        if (kontrolCari && kontrolCari !== document.activeElement) {
            kontrolCari.value = params.get(kontrolCari.name) ?? '';
        }

        pilih.forEach((sel) => {
            if (sel !== document.activeElement) {
                sel.value = params.get(sel.name) ?? '';
            }
        });

        daftarTautan().forEach((tautan) => {
            if (kunciPilih.has(tautan.dataset.cfField)) {
                return;
            }

            const nilai = params.get(tautan.dataset.cfField) ?? '';
            tautan.classList.toggle('active', nilai === (tautan.dataset.cfValue ?? ''));
        });

        segarkan();
    };

    let jedaKetik = null;

    cari.forEach((input) => {
        input.addEventListener('input', () => {
            window.clearTimeout(jedaKetik);
            jedaKetik = window.setTimeout(() => {
                segarkan();
                sinkronURL(false);
            }, DEBOUNCE_MS);
        });
    });

    pilih.forEach((sel) => {
        sel.addEventListener('change', () => {
            segarkan();
            sinkronURL(true);
        });
    });

    const form = (kontrolCari ?? pilih[0])?.closest('form');

    form?.addEventListener('submit', (event) => {
        if (form.hasAttribute('data-cf-fetch-submit')) {
            return;
        }

        event.preventDefault();
        window.clearTimeout(jedaKetik);
        segarkan();
        sinkronURL(true);
    });

    zona.forEach((z) => {
        z.addEventListener('click', (event) => {
            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            const tautanAktif = event.target.closest('a[data-cf-field]');

            if (! tautanAktif || ! zona.some((zz) => zz.contains(tautanAktif))) {
                return;
            }

            event.preventDefault();
            window.clearTimeout(jedaKetik);

            const field = tautanAktif.dataset.cfField;

            daftarTautan()
                .filter((item) => item.dataset.cfField === field)
                .forEach((item) => item.classList.toggle('active', item === tautanAktif));

            segarkan();
            sinkronURL(true);
        }, true);
    });

    window.addEventListener('popstate', seedingDariURL);

    document.addEventListener('realtime:updated', seedingDariURL);
    document.addEventListener('cf:refresh', seedingDariURL);

    segarkan();
}

function inisialisasiClientFilter() {
    bangunGrup().forEach((zona, kunci) => pasangGrup(kunci, zona));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inisialisasiClientFilter);
} else {
    inisialisasiClientFilter();
}
