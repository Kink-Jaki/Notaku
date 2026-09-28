import { test, expect } from '@playwright/test';

test.describe('Kasir Laporan Harian', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'kasir@kasir');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/kasir/dashboard');
    });

    test('loads laporan harian data and date filter works', async ({ page }) => {
        await page.goto('/kasir/laporan-harian');
        await page.waitForLoadState('networkidle');

        // data visible - check stat cards exist
        await expect(page.locator('.stat-card, [class*="stat-card"]').first()).toBeVisible();

        // date input present
        const dateInput = page.locator('#tanggalLaporan, input[type="date"]').first();
        await expect(dateInput).toBeVisible();

        // change date
        const originalUrl = page.url();
        const newDate = '2026-09-25';
        await dateInput.fill(newDate);
        await dateInput.press('Tab'); // trigger change

        // wait for URL to update
        await page.waitForURL(/tanggal=2026-09-25/, { timeout: 10000 });
        expect(page.url()).toContain('tanggal=2026-09-25');

        // data still present
        await expect(page.locator('.stat-card, [class*="stat-card"]').first()).toBeVisible();

        // screenshot
        await page.screenshot({ path: 'test-results/laporan-harian-loaded.png', fullPage: true });
    });
});