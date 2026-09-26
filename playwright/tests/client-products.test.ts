import { test, expect } from '@playwright/test';

test.describe('Marketplace Product Views', () => {
    test('should render the marketplace', async ({ page }) => {
        await page.goto('/');
        await expect(page).toHaveURL('/');
        await expect(page.locator('h1')).toContainText('Marketplace');
    });

    test('should filter products by category', async ({ page }) => {
        await page.goto('/');
        await page.locator('a[href*="category_id="]').first().click();
        await expect(page).toHaveURL(/category_id=/);
    });

    test('should search for products', async ({ page }) => {
        await page.goto('/');
        await page.fill('input[name="search"]', 'Nasi');
        await page.getByRole('button', { name: 'Cari' }).click();
        await expect(page).toHaveURL(/search=Nasi/);
    });
});
