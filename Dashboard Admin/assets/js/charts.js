/**
 * Charts Engine for Dashboard Admin Drama Stream
 * Powered by Chart.js 4.x
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Inisialisasi Grafik Tren Pendapatan (Revenue Line Chart)
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        const ctx = revenueCtx.getContext('2d');
        
        // Gradient fill
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(0, 208, 182, 0.45)');
        gradient.addColorStop(1, 'rgba(0, 208, 182, 0.0)');

        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt (Berjalan)'],
                datasets: [{
                    label: 'Pendapatan VIP (Rp)',
                    data: [1850000, 2400000, 3100000, 4250000, 3950000, 4820000],
                    borderColor: '#00D0B6',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
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
                                let val = context.parsed.y;
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
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
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
        new Chart(userCompCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pelanggan Reguler (Free)', 'Pelanggan VIP Aktif', 'Pelanggan Banned'],
                datasets: [{
                    data: [2932, 488, 12],
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
                            padding: 16,
                            usePointStyle: true,
                            pointStyle: 'circle'
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

    // 3. Inisialisasi Grafik Transaksi Mingguan (Weekly Volume Bar Chart)
    const txWeeklyCtx = document.getElementById('weeklyTxChart');
    if (txWeeklyCtx) {
        new Chart(txWeeklyCtx, {
            type: 'bar',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [{
                    label: 'Transaksi VIP',
                    data: [14, 21, 18, 29, 36, 48, 42],
                    backgroundColor: 'rgba(56, 189, 248, 0.8)',
                    borderRadius: 6,
                    hoverBackgroundColor: '#00D0B6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94A3B8' }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#94A3B8', precision: 0 }
                    }
                }
            }
        });
    }
});
