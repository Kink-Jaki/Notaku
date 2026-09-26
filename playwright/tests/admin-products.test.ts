import { test, expect } from '@playwright/test';

test.describe('Admin Product Management', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@posapp.test');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/admin/dashboard');
    });

    test('should navigate to product index', async ({ page }) => {
        await page.goto('/admin/produk');
        await expect(page).toHaveURL('/admin/produk');
        await expect(page.getByText('Tambah Produk')).toBeVisible();
    });

    test('should navigate to create product page', async ({ page }) => {
        await page.click('text=Tambah Produk');
        await expect(page).toHaveURL('/admin/produk/create');
    });

    test('should edit a product', async ({ page }) => {
        await page.goto('/admin/produk');
        await page.locator('a[title="Edit produk"]').first().click();
        await expect(page).toHaveURL(/.*\/edit/);
    });

    test('should view product details in table', async ({ page }) => {
        await page.goto('/admin/produk');
        const firstProductLink = page.locator('a[title="Edit produk"]').first();
        await expect(firstProductLink).toBeVisible();
    });
});
