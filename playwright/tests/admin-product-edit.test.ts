import { test, expect } from '@playwright/test';

test.describe('Admin Product Edit', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@posapp.test');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/admin/dashboard');
        await page.goto('/admin/produk');
    });

    test('should load product edit page without route parameter errors', async ({ page }) => {
        await page.locator('a[title="Edit produk"]').first().click();
        await expect(page).toHaveURL(/.*\/edit/);
        await expect(page.locator('form')).toBeVisible();
    });

    test('should have form with product data', async ({ page }) => {
        await page.locator('a[title="Edit produk"]').first().click();
        await expect(page.locator('input[name="name"]')).toBeVisible();
        await expect(page.locator('textarea[name="description"]')).toBeVisible();
        await expect(page.locator('input[name="price"]')).toBeVisible();
        await expect(page.locator('input[name="stock"]')).toBeVisible();
    });

    test('should have category select', async ({ page }) => {
        await page.locator('a[title="Edit produk"]').first().click();
        await expect(page.locator('select[name="category_id"]')).toBeVisible();
    });
});
