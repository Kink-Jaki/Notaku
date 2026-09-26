import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: '.',
    timeout: 60000,
    expect: {
        timeout: 10000,
    },
    fullyParallel: false,
    workers: 1,
    reporter: [
        ['list'],
        ['html'],
    ],
    use: {
        baseURL: 'http://localhost:8000',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        headless: true,
    },
    projects: [
        {
            name: 'Chrome',
            use: {
                ...devices['Desktop Chrome'],
                browserName: 'chromium',
                channel: 'chrome',
                headless: true,
            },
        },
    ],
});