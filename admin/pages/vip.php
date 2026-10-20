<?php
// Halaman Pemantauan Pendapatan & Transaksi VIP Riil Database
if (!isSuperAdmin()) {
    header("Location: index.php?page=overview");
    exit;
}

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Data live database
$metrics = getVipFinancialMetrics();
$plans = getVipPlansList();
$statusCounts = getVipStatusCounts();
$allTx = getRecentVipPurchases(100, $filter, $search);
?>

<div class="space-y-8">

    <!-- 1. Revenue Metrics Header (100% Real Database Data) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Hari Ini -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden border border-white/5">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Pendapatan Hari Ini</span>
            <h3 class="text-2xl font-extrabold text-white tracking-tight"><?= formatRupiah($metrics['today_revenue']); ?></h3>
            <p class="text-xs text-emerald-400 mt-2 font-medium flex items-center gap-1">
                <i class="fas fa-check-circle"></i> <?= number_format($metrics['today_paid_sales']); ?> transaksi baru hari ini
            </p>
        </div>

        <!-- Minggu Ini -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden border border-white/5">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Pendapatan Minggu Ini</span>
            <h3 class="text-2xl font-extrabold text-sky-300 tracking-tight"><?= formatRupiah($metrics['week_revenue']); ?></h3>
            <p class="text-xs text-sky-400 mt-2 font-medium flex items-center gap-1">
                <i class="fas fa-calendar-week"></i> Minggu berjalan (Senin-Minggu)
            </p>
        </div>

        <!-- Bulan Ini -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden border border-white/5">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Pendapatan Bulan Ini</span>
            <h3 class="text-2xl font-extrabold text-[#00D0B6] tracking-tight"><?= formatRupiah($metrics['month_revenue']); ?></h3>
            <p class="text-xs text-emerald-400 mt-2 font-medium flex items-center gap-1">
                <i class="fas fa-chart-line"></i> <?= formatTanggalIndo(date('Y-m-01'), false); ?> - sekarang
            </p>
        </div>

        <!-- Total Pendapatan All-Time -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden border border-[#00D0B6]/30">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Akumulasi Seluruh Omset</span>
            <h3 class="text-2xl font-black text-white tracking-tight"><?= formatRupiah($metrics['total_revenue']); ?></h3>
            <p class="text-xs text-amber-400 mt-2 font-medium flex items-center gap-1">
                <i class="fas fa-crown"></i> <?= number_format($metrics['total_paid_sales']); ?> langganan berhasil terjual
            </p>
        </div>
    </div>

    <!-- 2. Paket Berlangganan VIP Cards (7 Paket Riil dari vip_plans) -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-white font-heading">Paket Langganan VIP Aktif (<?= count($plans); ?> Paket)</h2>
                <p class="text-xs text-slate-400">Daftar paket aktif di database yang ditampilkan di bot Telegram & mini app</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <i class="fas fa-check-circle mr-1 text-[10px]"></i> Database Synchronized
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($plans as $p): ?>
            <div class="glass-card p-5 rounded-2xl bg-gradient-to-b <?= $p['color']; ?> border border-white/10 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/10 text-white border border-white/10">
                            <?= $p['badge']; ?>
                        </span>
                        <?php if ((int)$p['sales_count'] > 0): ?>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#00D0B6] text-slate-950">
                            <?= $p['sales_count']; ?> Terjual
                        </span>
                        <?php endif; ?>
                    </div>
                    <h4 class="text-base font-bold text-white"><?= htmlspecialchars($p['name']); ?></h4>
                    <p class="text-xs text-slate-400 mb-4">Akses Penuh Selama <?= (int)$p['duration_days']; ?> Hari</p>
                </div>

                <div class="pt-3 border-t border-white/10 flex items-center justify-between">
                    <div>
                        <span class="text-lg font-black text-[#00D0B6]"><?= formatRupiah($p['price']); ?></span>
                    </div>
                    <div class="text-right">
                        <span class="text-[11px] text-slate-400 block font-mono"><?= (int)$p['sales_count']; ?> transaksi</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 3. Tabel Riwayat Transaksi Masuk (QRIS & VIP) -->
    <div class="glass-panel rounded-2xl overflow-hidden">
        
        <div class="p-6 border-b border-white/5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-white font-heading">Riwayat Transaksi Masuk QRIS</h2>
                    <p class="text-xs text-slate-400">Seluruh riwayat pembayaran paket VIP melalui QRIS payment gateway</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                        <input type="text" id="txSearchInput" placeholder="Cari Ref ID, User, atau ID..." class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-900 border border-white/10 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-[#00D0B6]">
                    </div>
                    <button onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition border border-white/5 flex items-center gap-1.5 whitespace-nowrap">
                        <i class="fas fa-print"></i>
                        <span>Cetak</span>
                    </button>
                </div>
            </div>

            <!-- Filter Status Tab -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                <a href="index.php?page=vip&filter=all" class="px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'all') ? 'bg-[#00D0B6] text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Semua (<?= $statusCounts['all']; ?>)
                </a>
                <a href="index.php?page=vip&filter=paid" class="px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'paid') ? 'bg-emerald-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-check-circle mr-1 text-[10px]"></i> Paid / Sukses (<?= $statusCounts['paid']; ?>)
                </a>
                <a href="index.php?page=vip&filter=pending" class="px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'pending') ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-clock mr-1 text-[10px]"></i> Pending (<?= $statusCounts['pending']; ?>)
                </a>
                <a href="index.php?page=vip&filter=expired" class="px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'expired') ? 'bg-rose-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-times-circle mr-1 text-[10px]"></i> Expired (<?= $statusCounts['expired']; ?>)
                </a>
                <?php if ($statusCounts['failed'] > 0): ?>
                <a href="index.php?page=vip&filter=failed" class="px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'failed') ? 'bg-rose-700 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Gagal (<?= $statusCounts['failed']; ?>)
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Table Body -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm custom-table">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Ref Gateway QRIS</th>
                        <th>Pelanggan</th>
                        <th>Paket Dibeli</th>
                        <th>Nominal Bayar</th>
                        <th>Status</th>
                        <th>Waktu Dibuat</th>
                        <th>Waktu Dibayar</th>
                        <th class="text-right">Rincian</th>
                    </tr>
                </thead>
                <tbody id="txTableBody" class="divide-y divide-white/5">
                    <?php if (empty($allTx)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-12 text-slate-500 text-xs">
                            <i class="fas fa-receipt text-3xl mb-3 block"></i>
                            Tidak ada transaksi yang sesuai dengan filter atau pencarian saat ini.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($allTx as $tx): 
                            $displayName = !empty($tx['first_name']) ? $tx['first_name'] : (!empty($tx['username']) ? '@' . $tx['username'] : 'User ' . $tx['user_id']);
                        ?>
                        <tr class="hover:bg-white/[0.02] transition">
                            <td class="font-mono text-xs text-slate-400">#<?= $tx['id']; ?></td>
                            <td>
                                <span class="font-mono text-xs text-[#00D0B6] font-semibold bg-[#00D0B6]/10 px-2 py-0.5 rounded border border-[#00D0B6]/20">
                                    <?= htmlspecialchars($tx['gateway_ref_id'] ?? 'INV-' . $tx['id']); ?>
                                </span>
                            </td>
                            <td>
                                <div>
                                    <p class="font-semibold text-white text-xs"><?= htmlspecialchars($displayName); ?></p>
                                    <p class="text-[11px] text-slate-400 font-mono">TG: <?= htmlspecialchars($tx['telegram_user_id'] ?? '-'); ?></p>
                                </div>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1 text-xs text-amber-300 font-medium">
                                    <i class="fas fa-crown text-[10px]"></i>
                                    <?= htmlspecialchars($tx['plan_name'] ?? 'Paket VIP'); ?>
                                </span>
                            </td>
                            <td class="font-bold text-white text-xs whitespace-nowrap">
                                <?= formatRupiah($tx['amount']); ?>
                            </td>
                            <td>
                                <?= getStatusBadge($tx['status']); ?>
                            </td>
                            <td class="text-xs text-slate-400 whitespace-nowrap">
                                <?= date('d M Y, H:i', strtotime($tx['created_at'])); ?>
                            </td>
                            <td class="text-xs text-slate-300 whitespace-nowrap">
                                <?= !empty($tx['paid_at']) ? '<span class="text-emerald-400 font-medium">' . date('d M Y, H:i', strtotime($tx['paid_at'])) . '</span>' : '<span class="text-slate-500">-</span>'; ?>
                            </td>
                            <td class="text-right">
                                <button onclick="showToast('Invoice #<?= $tx['id']; ?>: <?= htmlspecialchars($tx['plan_name'] ?? 'VIP'); ?> (<?= formatRupiah($tx['amount']); ?>)', 'info')" class="p-2 rounded-lg bg-slate-800 hover:bg-[#00D0B6]/20 hover:text-[#00D0B6] text-slate-400 transition" title="Lihat Rincian Transaksi">
                                    <i class="fas fa-file-invoice text-xs"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>
