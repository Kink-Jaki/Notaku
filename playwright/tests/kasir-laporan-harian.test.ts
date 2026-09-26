import { test, expect } from '@playwright/test';

test.describe('Kasir Laporan Harian skeleton loading', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'kasir@posapp.test');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForURL('**/kasir/dashboard');
    });

    test('shows skeleton then loads data, date filter triggers refetch without reload', async ({ page }) => {
        await page.goto('/kasir/laporan-harian');
        await page.waitForLoadState('networkidle');

        // skeleton present initially
        const statsSkeleton = page.locator('#lh-stats .skeleton');
        await expect(statsSkeleton.first()).toBeVisible();

        // wait for data to render (skeleton replaced)
        await page.waitForFunction(() => {
            const stats = document.getElementById('lh-stats');
            return stats && !stats.querySelector('.skeleton');
        }, { timeout: 10000 });

        // data visible
        await expect(page.locator('#lh-stats .stat-card')).toHaveCount(4);

        // date label filled
        const label = page.locator('#lh-tanggal-label');
        await expect(label).not.toHaveText(/skeleton/i);

        // change date
        const dateInput = page.locator('#tanggalLaporan');
        const originalUrl = page.url();
        const newDate = '2026-09-25';
        await dateInput.fill(newDate);
        await dateInput.press('Tab'); // trigger change

        // wait for refetch (aria-busy toggled)
        await page.waitForFunction(() => {
            const stats = document.getElementById('lh-stats');
            return stats && stats.getAttribute('aria-busy') === 'true';
        }, { timeout: 5000 });

        // wait for load complete
        await page.waitForFunction(() => {
            const stats = document.getElementById('lh-stats');
            return stats && stats.getAttribute('aria-busy') === 'false';
        }, { timeout: 10000 });

        // URL updated without reload
        expect(page.url()).toContain('tanggal=2026-09-25');
        expect(page.url()).not.toContain('kasir.laporan-harian');

        // data still present
        await expect(page.locator('#lh-stats .stat-card')).toHaveCount(4);

        // screenshot
        await page.screenshot({ path: 'test-results/laporan-harian-loaded.png', fullPage: true });
    });
});