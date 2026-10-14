/**
 * Charts Engine for Dashboard Admin Drama Stream
 * Powered by Chart.js 4.x - Terhubung dengan Data Riil Database
 */

document.addEventListener('DOMContentLoaded', function () {
    const dData = window.dashboardData || {};

    // 1. Inisialisasi Grafik Tren Pendapatan (Revenue Line Chart)
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        const ctx = revenueCtx.getContext('2d');
        
        // Gradient fill
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(0, 208, 182, 0.45)');
        gradient.addColorStop(1, 'rgba(0, 208, 182, 0.0)');

        const labels = dData.revenueLabels || ['Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt (Berjalan)'];
        const values = dData.revenueData || [0, 0, 0, 0, 6000, 57000];

        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan VIP (Rp)',
                    data: values,
                    borderColor: '#00D0B6',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#00D0B6',
                    pointBorderColor: '#0F172A',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        titleColor: '#F8FAFC',
                        bodyColor: '#38BDF8',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                let val = context.parsed.y || 0;
                                return ' Pendapatan: Rp ' + val.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        },
                        ticks: {
                            color: '#94A3B8'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        },
                        ticks: {
                            color: '#94A3B8',
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + ' jt';
                                } else if (value >= 1000) {
                                    return 'Rp ' + (value / 1000).toFixed(0) + ' rb';
                                }
                                return 'Rp ' + value;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Inisialisasi Grafik Komposisi Pelanggan (User Composition Doughnut)
    const userCompCtx = document.getElementById('userCompositionChart');
    if (userCompCtx) {
        const uLabels = dData.userLabels || ['Pelanggan Reguler (Free)', 'Pelanggan VIP Aktif', 'Pelanggan Banned'];
        const uValues = dData.userData || [7, 1, 0];

        new Chart(userCompCtx, {
            type: 'doughnut',
            data: {
                labels: uLabels,
                datasets: [{
                    data: uValues,
                    backgroundColor: [
                        '#3B82F6', // Blue Free
                        '#00D0B6', // Teal VIP
                        '#EF4444'  // Red Banned
                    ],
                    borderColor: '#111827',
                    borderWidth: 3,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#94A3B8',
                            padding: 14,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.parsed || 0;
                                return ` ${label}: ${value.toLocaleString('id-ID')} user`;
                            }
                        }
                    }
                }
            }
        });
    }
});
