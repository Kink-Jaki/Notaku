// Placeholder skeleton untuk halaman yang dirender server.
//
// Halaman server sudah utuh saat HTML tiba, jadi skeleton langsung tampil di
// markup hanya akan berkedip sia-sia pada respons cepat. Karena itu overlay
// disembunyikan di HTML lalu dimunculkan JS HANYA bila halaman belum selesai
// dalam AMBANG_MS — respons cepat tidak pernah melihatnya.

const AMBANG_MS = 400;
const BATAS_MS = 8000;

export const initPageSkeleton = () => {
    const overlay = document.querySelector('[data-page-skeleton]');

    if (! overlay || window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    let selesai = false;

    const sembunyikan = () => {
        if (selesai) {
            return;
        }

        selesai = true;
        window.clearTimeout(timerTampil);
        window.clearTimeout(timerBatas);
        overlay.hidden = true;
    };

    const timerTampil = window.setTimeout(() => {
        if (! selesai) {
            overlay.hidden = false;
        }
    }, AMBANG_MS);

    // Jaring pengaman: overlay tidak boleh menggantung kalau 'load' tidak
    // pernah datang (mis. request gambar pihak ketiga yang menggantung).
    const timerBatas = window.setTimeout(sembunyikan, BATAS_MS);

    if (document.readyState === 'complete') {
        sembunyikan();

        return;
    }

    window.addEventListener('load', sembunyikan, { once: true });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPageSkeleton);
} else {
    initPageSkeleton();
}