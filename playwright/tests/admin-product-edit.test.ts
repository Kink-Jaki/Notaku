import { test, expect } from '@playwright/test';

test.describe('Admin Product Edit', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@admin');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/admin/dashboard');
        await page.goto('/admin/produk');
    });

    test('should open edit modal without route parameter errors', async ({ page }) => {
        await page.locator('button[title="Edit produk"]').first().click();
        await expect(page.locator('#modalProduk')).toBeVisible();
        await expect(page.locator('#formProduk')).toBeVisible();
    });

    test('should have form with product data', async ({ page }) => {
        await page.locator('button[title="Edit produk"]').first().click();
        await expect(page.locator('#modalProduk')).toBeVisible();
        await expect(page.locator('input[name="name"]')).toBeVisible();
        await expect(page.locator('textarea[name="description"]')).toBeVisible();
        await expect(page.locator('input[name="price"]')).toBeVisible();
        await expect(page.locator('input[name="stock"]')).toBeVisible();
    });

    test('should have category select in modal', async ({ page }) => {
        await page.locator('button[title="Edit produk"]').first().click();
        await expect(page.locator('#modalProduk')).toBeVisible();
        await expect(page.locator('#produkCategory')).toBeVisible();
    });
});
