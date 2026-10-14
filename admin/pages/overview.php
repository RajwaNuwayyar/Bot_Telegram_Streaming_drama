<?php
// Overview Page: Ringkasan Metrik, Pendapatan, Pelanggan, dan Transaksi VIP Riil
$stats = getDashboardStats();
$recentTx = getRecentVipPurchases(6);
$trend = getMonthlyRevenueTrend(6);
$userComp = getUserCompositionStats();
?>

<!-- Data Bridge untuk Chart.js -->
<script>
window.dashboardData = {
    revenueLabels: <?= json_encode($trend['labels']); ?>,
    revenueData: <?= json_encode($trend['data']); ?>,
    userLabels: ['Pelanggan Reguler (Free)', 'Pelanggan VIP Aktif', 'Pelanggan Banned'],
    userData: [<?= (int)$userComp['regular_free']; ?>, <?= (int)$userComp['vip_active']; ?>, <?= (int)$userComp['banned']; ?>]
};
</script>

<div class="space-y-8">

    <!-- Top Notice Banner if Demo Mode -->
    <?php if (!$stats['is_live_data']): ?>
    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-300 flex items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-amber-500/20 text-amber-400">
                <i class="fas fa-info-circle text-lg"></i>
            </div>
            <div>
                <p class="text-sm font-semibold">Tampilan Preview (Mode Data Interaktif)</p>
                <p class="text-xs text-amber-400/80">Database MySQL sedang offline atau belum dihubungkan. Dashboard menampilkan sampel visual data pendapatan dan pelanggan. Begitu MySQL aktif, data riil dari database akan otomatis muncul!</p>
            </div>
        </div>
        <a href="index.php?page=settings" class="shrink-0 px-3 py-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-xs font-semibold transition border border-amber-500/30">
            Cek Database
        </a>
    </div>
    <?php endif; ?>

    <!-- 1. Metric Cards Grid (Pendapatan & Pelanggan Masuk Riil) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Card 1: Total Pendapatan -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden group border border-white/5">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-[#00D0B6]/10 rounded-full blur-xl group-hover:bg-[#00D0B6]/20 transition-all"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Pendapatan</span>
                <div class="w-10 h-10 rounded-xl bg-[#00D0B6]/10 text-[#00D0B6] flex items-center justify-center border border-[#00D0B6]/20">
                    <i class="fas fa-wallet text-base"></i>
                </div>
            </div>
            <div class="space-y-1">
                <h3 class="text-2xl font-extrabold text-white tracking-tight"><?= formatRupiah($stats['total_revenue']); ?></h3>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-emerald-400 font-semibold flex items-center gap-1">
                        <i class="fas fa-arrow-trend-up"></i> <?= $stats['revenue_growth']; ?>
                    </span>
                    <span class="text-slate-500">Bulan ini <?= formatRupiah($stats['revenue_this_month']); ?></span>
                </div>
            </div>
        </div>

        <!-- Card 2: Pelanggan Masuk (Total Users) -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden group border border-white/5">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pelanggan Masuk</span>
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center border border-blue-500/20">
                    <i class="fas fa-users text-base"></i>
                </div>
            </div>
            <div class="space-y-1">
                <h3 class="text-2xl font-extrabold text-white tracking-tight"><?= number_format($stats['total_users'], 0, ',', '.'); ?></h3>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-emerald-400 font-semibold flex items-center gap-1">
                        <i class="fas fa-user-plus"></i> <?= $stats['users_growth']; ?>
                    </span>
                    <span class="text-slate-500">Terdaftar di bot</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Pelanggan VIP Aktif -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden group border border-white/5">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/10 rounded-full blur-xl group-hover:bg-amber-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">User VIP Masuk</span>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20">
                    <i class="fas fa-crown text-base"></i>
                </div>
            </div>
            <div class="space-y-1">
                <h3 class="text-2xl font-extrabold text-amber-300 tracking-tight"><?= number_format($stats['active_vip_users'], 0, ',', '.'); ?></h3>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-emerald-400 font-semibold flex items-center gap-1">
                        <i class="fas fa-chart-line"></i> <?= $stats['vip_growth']; ?>
                    </span>
                    <span class="text-slate-500">Langganan aktif</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Transaksi VIP Berhasil -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden group border border-white/5">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-purple-500/10 rounded-full blur-xl group-hover:bg-purple-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Transaksi VIP Selesai</span>
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center border border-purple-500/20">
                    <i class="fas fa-receipt text-base"></i>
                </div>
            </div>
            <div class="space-y-1">
                <h3 class="text-2xl font-extrabold text-white tracking-tight"><?= number_format($stats['total_transactions'], 0, ',', '.'); ?></h3>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-purple-400 font-semibold">QRIS Terverifikasi</span>
                    <span class="text-slate-500">• 100% Otomatis</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Charts Section (Pendapatan & Komposisi Pengguna Riil) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Revenue Trend Line Chart (2 Cols) -->
        <div class="glass-panel p-6 rounded-2xl lg:col-span-2 flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-white font-heading">Grafik Tren Pendapatan VIP</h2>
                    <p class="text-xs text-slate-400">Total akumulasi penjualan paket VIP melalui QRIS 6 bulan terakhir</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-lg text-xs font-medium bg-[#00D0B6]/10 text-[#00D0B6] border border-[#00D0B6]/20">Tahun <?= date('Y'); ?></span>
                </div>
            </div>
            <div class="h-72 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- User Composition Chart (1 Col) -->
        <div class="glass-panel p-6 rounded-2xl flex flex-col justify-between">
            <div class="mb-4">
                <h2 class="text-base font-bold text-white font-heading">Komposisi Pelanggan</h2>
                <p class="text-xs text-slate-400">Perbandingan user Reguler (Free) vs Pelanggan VIP</p>
            </div>
            <div class="h-56 w-full relative">
                <canvas id="userCompositionChart"></canvas>
            </div>
            <div class="pt-4 border-t border-white/5 flex items-center justify-around text-center text-xs">
                <div>
                    <p class="text-slate-400">Konversi VIP</p>
                    <p class="font-bold text-[#00D0B6] text-sm"><?= $userComp['conversion']; ?></p>
                </div>
                <div class="h-6 w-[1px] bg-white/10"></div>
                <div>
                    <p class="text-slate-400">ARPU (Rata-rata)</p>
                    <p class="font-bold text-white text-sm"><?= $userComp['arpu']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Recent VIP Purchases Table (Pelanggan VIP Masuk Terkini) -->
    <div class="glass-panel rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-white font-heading">Transaksi Pelanggan VIP Terbaru</h2>
                <p class="text-xs text-slate-400">Data pembayaran QRIS masuk yang berhasil dikonfirmasi secara real-time</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="index.php?page=vip" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition border border-white/5 flex items-center gap-2">
                    <span>Lihat Semua Transaksi</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Responsive Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm custom-table">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Telegram ID</th>
                        <th>Paket VIP</th>
                        <th>Nominal</th>
                        <th>Metode / Ref ID</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($recentTx)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-500 text-xs">
                            Belum ada riwayat transaksi VIP.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($recentTx as $tx): 
                            $displayName = !empty($tx['first_name']) ? $tx['first_name'] : (!empty($tx['username']) ? $tx['username'] : 'User ' . $tx['user_id']);
                        ?>
                        <tr class="hover:bg-white/[0.02] transition">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center font-bold text-xs text-[#00D0B6] ring-1 ring-white/10">
                                        <?= strtoupper(substr($displayName, 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-white text-xs"><?= htmlspecialchars($displayName); ?></p>
                                        <p class="text-xs text-slate-400">@<?= htmlspecialchars($tx['username'] ?? '-'); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="font-mono text-xs text-slate-300">
                                <?= htmlspecialchars($tx['telegram_user_id'] ?? '-'); ?>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-500/10 text-amber-300 border border-amber-500/20">
                                    <i class="fas fa-crown text-[10px]"></i>
                                    <?= htmlspecialchars($tx['plan_name'] ?? 'Paket VIP'); ?>
                                </span>
                            </td>
                            <td class="font-bold text-white text-xs">
                                <?= formatRupiah($tx['amount']); ?>
                            </td>
                            <td>
                                <div class="text-xs">
                                    <span class="font-semibold text-slate-200"><?= htmlspecialchars($tx['payment_method'] ?? 'QRIS'); ?></span>
                                    <p class="font-mono text-[11px] text-slate-500"><?= htmlspecialchars($tx['gateway_ref_id'] ?? '-'); ?></p>
                                </div>
                            </td>
                            <td>
                                <?= getStatusBadge($tx['status']); ?>
                            </td>
                            <td class="text-xs text-slate-400 whitespace-nowrap">
                                <?= timeAgo($tx['created_at']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. Quick Action Shortcuts Section -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <a href="index.php?page=users" class="glass-card p-5 rounded-2xl flex items-center gap-4 group border border-white/5">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center group-hover:scale-110 transition border border-blue-500/20">
                <i class="fas fa-user-gear text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-sm">Kelola Pelanggan</h4>
                <p class="text-xs text-slate-400">Lihat semua user masuk & status VIP (<?= $stats['total_users']; ?> User)</p>
            </div>
        </a>

        <a href="index.php?page=dramas" class="glass-card p-5 rounded-2xl flex items-center gap-4 group border border-white/5">
            <div class="w-12 h-12 rounded-xl bg-[#00D0B6]/10 text-[#00D0B6] flex items-center justify-center group-hover:scale-110 transition border border-[#00D0B6]/20">
                <i class="fas fa-film text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-sm">Katalog Drama</h4>
                <p class="text-xs text-slate-400">Kelola series, episode, dan poster</p>
            </div>
        </a>

        <a href="index.php?page=affiliate" class="glass-card p-5 rounded-2xl flex items-center gap-4 group border border-white/5">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center group-hover:scale-110 transition border border-purple-500/20">
                <i class="fas fa-money-bill-transfer text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-sm">Penarikan Affiliate</h4>
                <p class="text-xs text-slate-400"><?= $stats['pending_withdrawals']; ?> permintaan menunggu pencairan</p>
            </div>
        </a>
    </div>

</div>
