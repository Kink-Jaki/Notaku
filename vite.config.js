import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/bootstrap.css',
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/dashboard-chart.js',
                'resources/js/kasir-dashboard-loader.js',
                'resources/js/kasir-riwayat-loader.js',
                'resources/js/kasir-antrian-loader.js',
                'resources/js/laporan-harian-loader.js',
                'resources/js/admin-dashboard-loader.js',
            ],
            refresh: true,
        }),
    ],
});