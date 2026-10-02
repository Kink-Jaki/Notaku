import { test, expect } from '@playwright/test';

async function login(page: any, email: string) {
    await page.goto('/login');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL((url: URL) => !url.pathname.startsWith('/login'));
}

const visibleRows = (page: any, selector: string) =>
    page.locator(`${selector}:not(.d-none)`);

test.describe('Client-side search & filter', () => {
    test('admin produk: search & status filter tanpa reload', async ({ page }) => {
        await login(page, 'admin@admin');
        await page.goto('/admin/produk');
        await page.evaluate(() => { (window as any).__mark = 1; });

        const rows = page.locator('table tbody tr[data-cf-row]');
        const total = await rows.count();
        expect(total).toBeGreaterThan(0);

        const nama = (await rows.first().locator('.fw-semibold').first().innerText()).trim();
        const kandidat = nama.match(/[A-Za-z]{4,}/g) ?? [nama];
        const kata = kandidat.sort((a, b) => b.length - a.length)[0].slice(0, 6);

        await page.fill('#cariProduk', kata);
        await page.waitForTimeout(600);

        await expect(page).toHaveURL(/search=/);
        const tersaring = await visibleRows(page, 'table tbody tr[data-cf-row]').count();
        expect(tersaring).toBeGreaterThan(0);
        expect(tersaring).toBeLessThanOrEqual(total);

        for (const teks of await visibleRows(page, 'table tbody tr[data-cf-row]').allInnerTexts()) {
            expect(teks.toLowerCase()).toContain(kata.toLowerCase());
        }

        await page.fill('#cariProduk', '');
        await page.waitForTimeout(600);
        await expect(page).not.toHaveURL(/search=/);
        expect(await visibleRows(page, 'table tbody tr[data-cf-row]').count()).toBe(total);

        await page.selectOption('#filterStokProduk', 'habis');
        await page.waitForTimeout(400);
        await expect(page).toHaveURL(/status=habis/);

        const habis = await visibleRows(page, 'table tbody tr[data-cf-row]').count();
        if (habis > 0) {
            for (const row of await visibleRows(page, 'table tbody tr[data-cf-row]').all()) {
                expect(await row.getAttribute('data-status')).toBe('habis');
            }
        } else {
            await expect(page.locator('td[data-cf-empty], [data-cf-empty]').first()).toBeVisible();
        }

        expect(await page.evaluate(() => (window as any).__mark)).toBe(1);
    });

    test('admin kategori & promo & role-user filter', async ({ page }) => {
        await login(page, 'admin@admin');

        await page.goto('/admin/kategori');
        const katTotal = await page.locator('table tbody tr[data-cf-row]').count();
        expect(katTotal).toBeGreaterThan(0);
        const katNama = (await page.locator('table tbody tr[data-cf-row] .fw-semibold').first().innerText()).trim();
        await page.fill('#cariKategori', katNama.slice(0, 5));
        await page.waitForTimeout(600);
        await expect(page).toHaveURL(/search=/);
        expect(await visibleRows(page, 'table tbody tr[data-cf-row]').count()).toBeGreaterThan(0);

        await page.goto('/admin/promo');
        await page.selectOption('#filterJenisPromo', 'percent');
        await page.waitForTimeout(400);
        await expect(page).toHaveURL(/jenis=percent/);
        for (const row of await visibleRows(page, 'table tbody tr[data-cf-row]').all()) {
            expect(await row.getAttribute('data-jenis')).toBe('percent');
        }

        await page.goto('/admin/user-role');
        await page.selectOption('#filterRole', 'kasir');
        await page.waitForTimeout(400);
        await expect(page).toHaveURL(/role=kasir/);
        const kasirRows = await visibleRows(page, 'table tbody tr[data-cf-row]').count();
        expect(kasirRows).toBeGreaterThan(0);
        for (const row of await visibleRows(page, 'table tbody tr[data-cf-row]').all()) {
            expect(await row.getAttribute('data-role')).toBe('kasir');
        }
    });

    test('pelanggan riwayat: pill status client-side', async ({ page }) => {
        await login(page, 'pelanggan@posapp.test');
        await page.goto('/pesanan-saya');
        await page.evaluate(() => { (window as any).__mark = 1; });

        const total = await page.locator('.order-card[data-cf-row]').count();
        expect(total).toBeGreaterThan(0);

        await page.click('a[data-cf-value="diproses"]');
        await page.waitForTimeout(300);

        await expect(page).toHaveURL(/status=diproses/);
        const card = visibleRows(page, '.order-card[data-cf-row]');
        const jumlah = await card.count();
        expect(jumlah).toBeGreaterThan(0);
        expect(jumlah).toBeLessThanOrEqual(total);

        for (const row of await card.all()) {
            expect(await row.getAttribute('data-status')).toBe('diproses');
        }

        expect(await page.evaluate(() => (window as any).__mark)).toBe(1);

        await page.click('a[data-cf-value=""]');
        await page.waitForTimeout(300);
        await expect(page).not.toHaveURL(/status=/);
        expect(await visibleRows(page, '.order-card[data-cf-row]').count()).toBe(total);
    });

    test('katalog mobile: search & pill kategori client-side', async ({ page }) => {
        await login(page, 'pelanggan@posapp.test');
        await page.goto('/katalog/mobile');
        await page.evaluate(() => { (window as any).__mark = 1; });

        const total = await page.locator('.product-grid-mobile .product-card-mobile[data-cf-row]').count();
        expect(total).toBeGreaterThan(0);

        await page.fill('input[name="search"]', 'a');
        await page.waitForTimeout(600);
        await expect(page).toHaveURL(/search=/);
        const tersaring = await visibleRows(page, '.product-grid-mobile .product-card-mobile[data-cf-row]').count();
        expect(tersaring).toBeGreaterThan(0);
        expect(tersaring).toBeLessThanOrEqual(total);

        await page.fill('input[name="search"]', 'zzzzqqq');
        await page.waitForTimeout(600);
        expect(await visibleRows(page, '.product-grid-mobile .product-card-mobile[data-cf-row]').count()).toBe(0);
        await expect(page.locator('#productGrid [data-cf-empty]')).toBeVisible();

        await page.fill('input[name="search"]', '');
        await page.waitForTimeout(600);

        await page.click('a[data-cf-value]:not([data-cf-value=""])');
        await page.waitForTimeout(400);
        await expect(page).toHaveURL(/category_id=/);

        expect(await page.evaluate(() => (window as any).__mark)).toBe(1);
    });

    test('kasir riwayat: q/jenis client-side, tanggal server-side', async ({ page }) => {
        await login(page, 'kasir@kasir');
        await page.goto('/kasir/riwayat');

        await page.waitForFunction(() => {
            const body = document.getElementById('riwayat-body');
            return body && body.querySelectorAll('tr[data-cf-row]').length > 0;
        }, { timeout: 15000 });

        await page.evaluate(() => { (window as any).__mark = 1; });

        const total = await page.locator('#riwayat-body tr[data-cf-row]').count();
        expect(total).toBeGreaterThan(0);

        const nomor = (await page.locator('#riwayat-body tr[data-cf-row] .font-monospace').first().innerText()).trim();

        await page.fill('input[name="q"]', 'zzzz-tidak-ada');
        await page.waitForTimeout(600);
        await expect(page).toHaveURL(/q=/);
        expect(await visibleRows(page, '#riwayat-body tr[data-cf-row]').count()).toBe(0);
        await expect(page.locator('#riwayat-body [data-cf-empty]')).toBeVisible();
        await expect(page.locator('#riwayat-badge-transaksi')).toHaveText('0');

        await page.fill('input[name="q"]', nomor.slice(0, 6));
        await page.waitForTimeout(600);

        const rows = visibleRows(page, '#riwayat-body tr[data-cf-row]');
        const jumlah = await rows.count();
        expect(jumlah).toBeGreaterThan(0);
        expect(jumlah).toBeLessThanOrEqual(total);
        expect((await rows.first().innerText()).replace(/\s+/g, ' ')).toContain(nomor);

        await expect(page.locator('#riwayat-badge-transaksi')).toHaveText(String(jumlah));

        expect(await page.evaluate(() => (window as any).__mark)).toBe(1);

        await page.fill('input[name="q"]', '');
        await page.waitForTimeout(600);
        expect(await visibleRows(page, '#riwayat-body tr[data-cf-row]').count()).toBe(total);

        await page.selectOption('select[name="jenis"]', 'Online');
        await page.waitForTimeout(400);
        await expect(page).toHaveURL(/jenis=Online/);
        for (const row of await visibleRows(page, '#riwayat-body tr[data-cf-row]').all()) {
            expect(await row.getAttribute('data-jenis')).toBe('Online');
        }

        await page.selectOption('select[name="jenis"]', 'Semua');
        await page.waitForTimeout(400);

        await page.fill('input[name="dari"]', '2020-01-01');
        await page.waitForTimeout(1500);
        await expect(page).toHaveURL(/dari=2020-01-01/);
        expect(await page.locator('#riwayat-body tr').count()).toBeGreaterThan(0);
    });

    test('marketplace & admin: pagination mempertahankan filter', async ({ page }) => {
        await page.goto('/');
        await page.fill('input[name="search"]', 'a');
        await page.waitForTimeout(600);
        await expect(page).toHaveURL(/search=/);
        await expect(page.locator('#katalog-meta')).toContainText('Menampilkan');

        const paginasi = page.locator('#katalog-pagination a[data-page]');

        if (await paginasi.count() > 0) {
            await paginasi.last().click();
            await page.waitForTimeout(500);
            await expect(page).toHaveURL(/page=/);
            await expect(page).toHaveURL(/search=/);
            await page.waitForFunction(() => {
                const grid = document.getElementById('product-grid');

                return !! grid && (grid.querySelectorAll('[data-cf-row]').length > 0 || !! grid.querySelector('.pane'));
            }, null, { timeout: 20000 });
            await expect(page.locator('#katalog-meta')).toContainText('Menampilkan');

            const grid = visibleRows(page, '#product-grid [data-cf-row]');
            const jumlah = await grid.count();

            if (jumlah === 0) {
                await expect(page.locator('#product-grid [data-cf-empty]')).toBeVisible();
            } else {
                for (const teks of await grid.allInnerTexts()) {
                    expect(teks.toLowerCase()).toContain('a');
                }
            }
        }

        await login(page, 'admin@admin');
        await page.goto('/admin/produk');

        await page.fill('#cariProduk', 'a');
        await page.waitForTimeout(600);

        const halaman2 = page.locator('.pagination a[href*="page="]');

        if (await halaman2.count() > 0) {
            await halaman2.first().click();
            await page.waitForTimeout(500);
            await expect(page).toHaveURL(/page=/);
            await expect(page).toHaveURL(/search=/);
            await page.waitForFunction(() => document.querySelectorAll('table tbody tr[data-cf-row]').length > 0
                || !! document.querySelector('table [data-cf-empty]:not([hidden])'), null, { timeout: 20000 });
            await expect(page.locator('[data-cf-summary]')).toContainText('di halaman ini');

            const rows = visibleRows(page, 'table tbody tr[data-cf-row]');
            const jumlah = await rows.count();

            if (jumlah === 0) {
                await expect(page.locator('table [data-cf-empty]')).toBeVisible();
            } else {
                for (const teks of await rows.allInnerTexts()) {
                    expect(teks.toLowerCase()).toContain('a');
                }
            }
        }
    });
});
