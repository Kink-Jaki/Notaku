import { test, expect } from '@playwright/test';

test.describe('Client Katalog Product Views', () => {
    test('should render the customer catalog', async ({ page }) => {
        await page.goto('/katalog');
        await expect(page).toHaveURL('/katalog');
        await expect(page.locator('h1')).toContainText('Katalog Menu');
    });

    test('should filter products by category', async ({ page }) => {
        await page.goto('/katalog');
        await page.locator('a[href*="category_id="]').first().click();
        await expect(page).toHaveURL(/category_id=/);
    });

    test('should search for products', async ({ page }) => {
        await page.goto('/katalog');
        await page.fill('input[name="search"]', 'Nasi');
        await page.getByRole('button', { name: 'Cari' }).click();
        await expect(page).toHaveURL(/search=Nasi/);
    });
});
