const numberFormat = new Intl.NumberFormat('id-ID');

const CART_STORAGE_KEY = 'pos_cart_v1';
const PROMO_STORAGE_KEY = 'pos_promo_v1';

const rupiah = (value) => `Rp ${numberFormat.format(Math.round(Number(value) || 0))}`;

const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
}[char]));

const isPlainObject = (value) => !! value && typeof value === 'object' && ! Array.isArray(value);

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const setText = (scope, selector, text) => {
    scope.querySelectorAll(selector).forEach((el) => {
        el.textContent = text;
    });
};

const toggleHidden = (scope, selector, hide) => {
    scope.querySelectorAll(selector).forEach((el) => el.classList.toggle('d-none', hide));
};

const readStorage = (key) => {
    try {
        const raw = window.localStorage.getItem(key);

        return raw ? JSON.parse(raw) : null;
    } catch (error) {
        return null;
    }
};

const writeStorage = (key, value) => {
    try {
        window.localStorage.setItem(key, JSON.stringify(value));
    } catch (error) {
        // localStorage bisa tidak tersedia; state tetap hidup di memori halaman.
    }
};

/**
 * Mirror keranjang session di sisi client. Seed selalu datang dari server
 * (`window.__CART_SEED__`) supaya tetap sinkron, localStorage dipakai sebagai
 * cadangan saat seed tidak ter-render.
 */
const CartStore = {
    cart: {},
    promo: null,

    hydrate() {
        const seed = window.__CART_SEED__;

        this.cart = seed
            ? (isPlainObject(seed.cart) ? seed.cart : {})
            : (isPlainObject(readStorage(CART_STORAGE_KEY)) ? readStorage(CART_STORAGE_KEY) : {});

        this.promo = seed
            ? (isPlainObject(seed.promo) ? seed.promo : null)
            : (isPlainObject(readStorage(PROMO_STORAGE_KEY)) ? readStorage(PROMO_STORAGE_KEY) : null);
    },

    persist() {
        writeStorage(CART_STORAGE_KEY, this.cart);
        writeStorage(PROMO_STORAGE_KEY, this.promo);
    },

    setCart(cart) {
        if (isPlainObject(cart)) {
            this.cart = cart;
        }
    },

    setPromo(promo) {
        this.promo = isPlainObject(promo) ? promo : null;
    },

    items() {
        return Object.values(this.cart);
    },

    count() {
        return this.items().reduce((total, item) => total + (Number(item.qty) || 0), 0);
    },

    subtotal() {
        return this.items().reduce(
            (total, item) => total + (Number(item.price) || 0) * (Number(item.qty) || 0),
            0,
        );
    },

    discount() {
        if (! this.promo) {
            return 0;
        }

        return Math.min(Number(this.promo.discount) || 0, this.subtotal());
    },

    total() {
        return this.subtotal() - this.discount();
    },
};

const showError = (message) => {
    window.Swal?.fire({ icon: 'error', title: 'Gagal', text: message || 'Terjadi kesalahan' });
};

const showSuccess = (message) => {
    window.Swal?.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: message,
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
    });
};

const request = async (url, options = {}) => {
    const response = await fetch(url, {
        method: 'POST',
        ...options,
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
            ...(options.headers || {}),
        },
    });

    let data = {};

    try {
        data = await response.json();
    } catch (error) {
        data = {};
    }

    if (! response.ok && ! data.error) {
        data.error = 'Terjadi kesalahan';
    }

    return data;
};

const applyResponse = (data) => {
    if (isPlainObject(data.cart)) {
        CartStore.setCart(data.cart);
    }

    if ('promo' in data) {
        CartStore.setPromo(data.promo ?? null);
    }
};

const renderChrome = () => {
    const count = CartStore.count();

    document.querySelectorAll('[data-cart-badge]').forEach((el) => {
        el.textContent = String(count);
        el.classList.toggle('d-none', count === 0);
    });

    document.querySelectorAll('[data-cart-count-label]').forEach((el) => {
        el.textContent = `${count} item`;
    });

    const list = document.querySelector('[data-cart-list]');

    if (list) {
        list.innerHTML = CartStore.items().map((item) => `
            <div class="cart-row">
                <div class="cart-row__thumb">${escapeHtml(String(item.name ?? '').trim().charAt(0).toUpperCase() || '?')}</div>
                <div class="cart-row__info">
                    <span class="cart-row__name">${escapeHtml(item.name)}</span>
                    <span class="cart-row__meta">${Number(item.qty) || 0} &times; ${rupiah(item.price)}</span>
                </div>
                <span class="cart-row__total">${rupiah((Number(item.price) || 0) * (Number(item.qty) || 0))}</span>
            </div>`).join('');
    }

    document.querySelectorAll('[data-cart-total]').forEach((el) => {
        el.textContent = rupiah(CartStore.subtotal());
    });

    toggleHidden(document, '[data-cart-list-block]', count === 0);
    toggleHidden(document, '[data-cart-empty-block]', count > 0);
};

const renderCartPage = () => {
    const page = document;

    if (! page.querySelector('[data-cart-row], [data-cart-body], [data-cart-empty-state]')) {
        return;
    }

    page.querySelectorAll('[data-cart-row]').forEach((row) => {
        const item = CartStore.cart[row.dataset.cartRow];

        if (! item) {
            row.remove();

            return;
        }

        const qty = row.querySelector('[data-cart-row-qty]');

        if (qty) {
            qty.value = String(item.qty);
        }

        const hiddenQty = row.querySelector('[data-cart-qty-input]');

        if (hiddenQty) {
            hiddenQty.value = String(item.qty);
        }

        const lineTotal = row.querySelector('[data-cart-row-subtotal]');

        if (lineTotal) {
            lineTotal.textContent = rupiah((Number(item.price) || 0) * (Number(item.qty) || 0));
        }

        const decrease = row.querySelector('[data-cart-step="-1"]');

        if (decrease) {
            decrease.disabled = (Number(item.qty) || 0) <= 1;
        }
    });

    const count = CartStore.count();
    const discount = CartStore.discount();

    setText(page, '[data-cart-item-count]', `${count} item`);
    setText(page, '[data-cart-summary-subtotal]', rupiah(CartStore.subtotal()));
    setText(page, '[data-cart-summary-total]', rupiah(CartStore.total()));
    setText(page, '[data-cart-summary-discount]', `\u2212${rupiah(discount)}`);
    setText(page, '[data-cart-checkout-total]', rupiah(CartStore.total()));
    setText(page, '[data-promo-code]', CartStore.promo?.code ?? '');

    toggleHidden(page, '[data-cart-discount-row]', discount <= 0);
    toggleHidden(page, '[data-promo-apply]', !! CartStore.promo);
    toggleHidden(page, '[data-promo-applied]', ! CartStore.promo);
    toggleHidden(page, '[data-cart-empty-state]', count > 0);
    toggleHidden(page, '[data-cart-body]', count === 0);
};

const commit = () => {
    CartStore.persist();
    renderChrome();
    renderCartPage();
    document.dispatchEvent(new CustomEvent('cart:updated', { detail: { cart: CartStore.cart } }));
};

const postUpdate = async (form) => {
    try {
        const data = await request(form.action, { body: new FormData(form) });

        applyResponse(data);
        commit();

        if (! data.success) {
            showError(data.error);
        }

        return data.success === true;
    } catch (error) {
        commit();
        showError('Terjadi kesalahan jaringan');

        return false;
    }
};

document.addEventListener('submit', async (event) => {
    const addForm = event.target.closest('.cart-add-form');

    if (addForm) {
        event.preventDefault();

        const btn = addForm.querySelector('.cart-add-btn') ?? addForm.querySelector('button[type="submit"]');

        if (btn) {
            btn.disabled = true;
        }

        try {
            const data = await request(addForm.action, { body: new FormData(addForm) });

            if (data.success) {
                applyResponse(data);
                commit();
                showSuccess(data.message ?? 'Berhasil ditambahkan ke keranjang!');
            } else {
                showError(data.error);
            }
        } catch (error) {
            showError('Terjadi kesalahan jaringan');
        } finally {
            if (btn) {
                btn.disabled = false;
            }
        }

        return;
    }

    const updateForm = event.target.closest('.cart-update-form, .cart-update-form-mobile');

    if (updateForm) {
        event.preventDefault();
        await postUpdate(updateForm);

        return;
    }

    const promoForm = event.target.closest('.cart-promo-form, .cart-promo-remove-form');

        if (promoForm) {
            event.preventDefault();

            const isRemove = promoForm.classList.contains('cart-promo-remove-form');

            try {
                const data = await request(promoForm.action, { body: new FormData(promoForm) });

                if (data.success) {
                    applyResponse(data);
                    commit();
                    showSuccess(isRemove ? 'Promo dihapus.' : 'Promo diterapkan!');
                } else {
                    showError(data.error);
                }
            } catch (error) {
                showError('Terjadi kesalahan jaringan');
            }
        }
});

document.addEventListener('click', async (event) => {
    const stepBtn = event.target.closest('[data-cart-step]');

    if (stepBtn) {
        const form = stepBtn.closest('.cart-update-form, .cart-update-form-mobile');

        if (! form) {
            return;
        }

        const hiddenQty = form.querySelector('[data-cart-qty-input]');

        if (! hiddenQty) {
            return;
        }

        const next = Math.max((Number(hiddenQty.value) || 1) + Number(stepBtn.dataset.cartStep), 1);

        if (next === Number(hiddenQty.value)) {
            return;
        }

        hiddenQty.value = String(next);
        await postUpdate(form);

        return;
    }

    const removeBtn = event.target.closest('.cart-remove-btn, .cart-remove-btn-mobile');

    if (! removeBtn) {
        return;
    }

    const productId = removeBtn.dataset.productId;
    const productName = removeBtn.dataset.productName ?? 'Item';
    const result = await window.Swal?.fire({
        title: 'Hapus dari keranjang?',
        text: `${productName} akan dihapus.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    });

    if (! result?.isConfirmed) {
        return;
    }

    try {
        const data = await request(window.__CART_ROUTES__?.remove, {
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId }),
        });

        applyResponse(data);
        commit();

        if (data.success) {
            showSuccess(`${productName} dihapus dari keranjang`);
        } else {
            showError(data.error);
        }
    } catch (error) {
        commit();
        showError('Terjadi kesalahan jaringan');
    }
});

export const getCart = () => CartStore.cart;

CartStore.hydrate();
