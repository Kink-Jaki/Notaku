import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

let chartInstance = null;
let listeningForThemeChange = false;

const readToken = (name, fallback) => {
    const value = window
        .getComputedStyle(document.documentElement)
        .getPropertyValue(name)
        .trim();

    return value || fallback;
};

const chartPalette = () => {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

    return {
        axisText: readToken('--color-text-secondary', isDark ? '#E2E8F0' : '#334155'),
        grid: isDark ? 'rgba(148, 163, 184, 0.16)' : 'rgba(15, 23, 42, 0.08)',
        pointRing: readToken('--color-surface', isDark ? '#1E293B' : '#FFFFFF'),
        tooltipBg: isDark ? '#020617' : '#1E293B',
        tooltipText: readToken('--color-text', isDark ? '#F8FAFC' : '#0F172A'),
    };
};

export function initDashboardChart() {
    const canvas = document.getElementById('salesChart');
    if (!canvas) return;

    chartInstance?.destroy();
    chartInstance = null;

    if (! listeningForThemeChange) {
        listeningForThemeChange = true;
        document.addEventListener('themechange', () => initDashboardChart());
    }

    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const trxData = JSON.parse(canvas.dataset.trx || '[]');
    const omzetData = JSON.parse(canvas.dataset.omzet || '[]');

    const palette = chartPalette();

    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.25)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Omzet (Rp)',
                    data: omzetData,
                    borderColor: '#4F46E5',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4F46E5',
                    pointBorderColor: palette.pointRing,
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    yAxisID: 'y',
                },
                {
                    label: 'Transaksi',
                    data: trxData,
                    borderColor: '#10B981',
                    backgroundColor: 'transparent',
                    borderDash: [5, 5],
                    tension: 0.4,
                    pointBackgroundColor: '#10B981',
                    pointBorderColor: palette.pointRing,
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 800,
                easing: 'easeOutQuart'
            },
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 20,
                        color: palette.axisText,
                        font: { size: 12, weight: '500' }
                    }
                },
                tooltip: {
                    backgroundColor: palette.tooltipBg,
                    titleColor: palette.tooltipText,
                    bodyColor: palette.tooltipText,
                    titleFont: { size: 13, weight: '600' },
                    bodyFont: { size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            if (context.datasetIndex === 0) {
                                return ' Omzet: Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                            return ' Transaksi: ' + context.parsed.y + ' buah';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: palette.axisText, font: { size: 11 } }
                },
                y: {
                    position: 'left',
                    grid: { color: palette.grid },
                    ticks: {
                        color: palette.axisText,
                        font: { size: 11 },
                        callback: function(value) {
                            return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                        }
                    }
                },
                y1: {
                    position: 'right',
                    grid: { display: false },
                    ticks: {
                        color: palette.axisText,
                        font: { size: 11 },
                        stepSize: 10
                    }
                }
            }
        }
    });
}

initDashboardChart();
