import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export function initDashboardChart() {
    const canvas = document.getElementById('salesChart');
    if (!canvas) return;

    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const trxData = JSON.parse(canvas.dataset.trx || '[]');
    const omzetData = JSON.parse(canvas.dataset.omzet || '[]');

    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.25)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

    new Chart(ctx, {
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
                    pointBorderColor: '#ffffff',
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
                    pointBorderColor: '#ffffff',
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
                        font: { size: 12, weight: '500' }
                    }
                },
                tooltip: {
                    backgroundColor: '#1E293B',
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
                    ticks: { font: { size: 11 } }
                },
                y: {
                    position: 'left',
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: {
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
                        font: { size: 11 },
                        stepSize: 10
                    }
                }
            }
        }
    });
}

initDashboardChart();
