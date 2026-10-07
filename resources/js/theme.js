const THEME_KEY = 'notaku-theme';

const resolveInitialTheme = () => {
    try {
        const saved = window.localStorage.getItem(THEME_KEY);
        if (saved === 'light' || saved === 'dark') {
            return saved;
        }
    } catch (error) {
        // localStorage bisa tidak tersedia; lanjut ke preferensi sistem.
    }

    return window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

const applyTheme = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', String(theme === 'dark'));
        button.setAttribute(
            'aria-label',
            theme === 'dark' ? 'Aktifkan mode terang' : 'Aktifkan mode gelap',
        );
        button.setAttribute(
            'title',
            theme === 'dark' ? 'Mode terang' : 'Mode gelap',
        );
    });

    // Widget berbasis <canvas> (Chart.js) tidak bisa di-style lewat CSS,
    // jadi perlu sinyal eksplisit untuk menggambar ulang dengan palet tema.
    document.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
};

const persistTheme = (theme) => {
    try {
        window.localStorage.setItem(THEME_KEY, theme);
    } catch (error) {
        // Abaikan: tema tetap aktif untuk sesi ini saja.
    }
};

export const initTheme = () => {
    applyTheme(resolveInitialTheme());

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-theme-toggle]');

        if (! toggle) {
            return;
        }

        const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';

        applyTheme(next);
        persistTheme(next);
    });
};

if (! document.documentElement.hasAttribute('data-theme')) {
    document.documentElement.setAttribute('data-theme', resolveInitialTheme());
}

initTheme();
