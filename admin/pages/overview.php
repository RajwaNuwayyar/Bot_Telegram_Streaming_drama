<?php
// Overview Page: Ringkasan Metrik, Pendapatan, Pelanggan, dan Transaksi VIP Riil
$stats = getDashboardStats();
$userComp = getUserCompositionStats();
$adminInfo = getAdminInfo();
$isSuper = isSuperAdmin();

// Data khusus Super Admin (Keuangan & Omset)
$recentTx = $isSuper ? getRecentVipPurchases(6) : [];
$trend = $isSuper ? getMonthlyRevenueTrend(6) : ['labels' => [], 'data' => []];

// Data operasional untuk Staff Admin & Tim Lapangan
$dramaStats = getDramaStats();
$reqStats = getFilmRequestStats();
$recentRequests = !$isSuper ? getFilmRequestsList('all', '') : [];
if (!$isSuper && count($recentRequests) > 6) {
    $recentRequests = array_slice($recentRequests, 0, 6);
}
?>

<!-- Data Bridge untuk Chart.js -->
<script>
window.dashboardData = {
    isSuperAdmin: <?= $isSuper ? 'true' : 'false'; ?>,
    revenueLabels: <?= json_encode($trend['labels']); ?>,
    revenueData: <?= json_encode($trend['data']); ?>,
    userLabels: ['Pelanggan Reguler (Free)', 'Pelanggan VIP Aktif', 'Pelanggan Banned'],
    userData: [<?= (int)$userComp['regular_free']; ?>, <?= (int)$userComp['vip_active']; ?>, <?= (int)$userComp['banned']; ?>],
    requestLabels: ['Menunggu Respon', 'Prioritas', 'Selesai', 'Ditolak'],
    requestData: [<?= (int)$reqStats['pending_count']; ?>, <?= (int)$reqStats['priority_count']; ?>, <?= (int)$reqStats['completed_count']; ?>, <?= (int)$reqStats['rejected_count']; ?>]
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
                <p class="text-xs text-amber-400/80">Database MySQL sedang offline atau belum dihubungkan. Dashboard menampilkan sampel visual data operasional dan pelanggan. Begitu MySQL aktif, data riil dari database akan otomatis muncul!</p>
            </div>
        </div>
        <?php if ($isSuper): ?>
        <a href="index.php?page=settings" class="shrink-0 px-3 py-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-xs font-semibold transition border border-amber-500/30">
            Cek Database
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Welcome & Guidance Banner Khusus Staff Admin -->
    <?php if (!$isSuper): ?>
    <div class="p-5 rounded-2xl bg-gradient-to-r from-sky-500/10 via-slate-900 to-slate-900 border border-sky-500/20 text-sky-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-sky-500/20 text-sky-400 flex items-center justify-center text-xl shadow-lg shadow-sky-500/10">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-white">Dashboard Operasional Staff Admin</h3>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30">Operator</span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">Halo, <strong class="text-slate-200"><?= htmlspecialchars($adminInfo['full_name']); ?></strong>! Fokus tugas Anda adalah mengelola episode drama dan melayani request film dari penonton.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="index.php?page=dramas" class="px-3.5 py-1.5 rounded-xl bg-sky-500 hover:bg-sky-400 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-lg shadow-sky-500/20">
                <i class="fas fa-plus text-[10px]"></i> Upload Episode
            </a>
            <a href="index.php?page=requests" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition border border-white/5 flex items-center gap-1.5">
                <i class="fas fa-inbox text-[10px]"></i> Cek Request
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- 1. Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <?php if ($isSuper): ?>
        <!-- === SUPER ADMIN CARDS: Keuangan & Omset Usaha === -->
        
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

        <?php else: ?>
        <!-- === STAFF ADMIN CARDS: Operasional Konten & Request === -->

        <!-- Card 1: Total Katalog Drama -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden group border border-white/5">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-[#00D0B6]/10 rounded-full blur-xl group-hover:bg-[#00D0B6]/20 transition-all"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Katalog Drama</span>
                <div class="w-10 h-10 rounded-xl bg-[#00D0B6]/10 text-[#00D0B6] flex items-center justify-center border border-[#00D0B6]/20">
                    <i class="fas fa-film text-base"></i>
                </div>
            </div>
            <div class="space-y-1">
                <h3 class="text-2xl font-extrabold text-white tracking-tight"><?= number_format($dramaStats['total_dramas'], 0, ',', '.'); ?> <span class="text-sm font-semibold text-slate-400">Judul</span></h3>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-emerald-400 font-semibold flex items-center gap-1">
                        <i class="fas fa-check-circle"></i> <?= $dramaStats['completed_count']; ?> Tamat
                    </span>
                    <span class="text-slate-500">• <?= $dramaStats['ongoing_count']; ?> Ongoing</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Episode Tersedia -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden group border border-white/5">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-sky-500/10 rounded-full blur-xl group-hover:bg-sky-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Episode Tersedia</span>
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center border border-sky-500/20">
                    <i class="fas fa-video text-base"></i>
                </div>
            </div>
            <div class="space-y-1">
                <h3 class="text-2xl font-extrabold text-white tracking-tight"><?= number_format($dramaStats['total_episodes_uploaded'], 0, ',', '.'); ?> <span class="text-sm font-semibold text-slate-400">Ep</span></h3>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-sky-400 font-semibold flex items-center gap-1">
                        <i class="fab fa-telegram-plane"></i> Tersinkron
                    </span>
                    <span class="text-slate-500">Channel Streaming</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Pelanggan Terdaftar -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden group border border-white/5">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pelanggan Masuk</span>
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center border border-blue-500/20">
                    <i class="fas fa-users text-base"></i>
                </div>
            </div>
            <div class="space-y-1">
                <h3 class="text-2xl font-extrabold text-white tracking-tight"><?= number_format($stats['total_users'], 0, ',', '.'); ?> <span class="text-sm font-semibold text-slate-400">User</span></h3>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-emerald-400 font-semibold flex items-center gap-1">
                        <i class="fas fa-user-plus"></i> <?= $stats['users_growth']; ?>
                    </span>
                    <span class="text-slate-500">Terdaftar</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Request Film Menunggu -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden group border border-white/5">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/10 rounded-full blur-xl group-hover:bg-amber-500/20 transition-all"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Permintaan Film</span>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center border border-amber-500/20">
                    <i class="fas fa-inbox text-base"></i>
                </div>
            </div>
            <div class="space-y-1">
                <h3 class="text-2xl font-extrabold text-amber-300 tracking-tight"><?= number_format($reqStats['pending_count'], 0, ',', '.'); ?> <span class="text-sm font-semibold text-slate-400">Antrean</span></h3>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-rose-400 font-semibold flex items-center gap-1">
                        <i class="fas fa-star"></i> <?= $reqStats['priority_count']; ?> Prioritas VIP
                    </span>
                    <span class="text-slate-500">Perlu diproses</span>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <!-- 2. Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <?php if ($isSuper): ?>
        <!-- === SUPER ADMIN CHARTS: Tren Pendapatan & Komposisi User === -->
        
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

        <?php else: ?>
        <!-- === STAFF ADMIN CHARTS: Aktivitas Permintaan Film & Komposisi Penonton === -->

        <!-- Requests Activity Bar Chart (2 Cols) -->
        <div class="glass-panel p-6 rounded-2xl lg:col-span-2 flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-white font-heading">Aktivitas Permintaan Film Penonton</h2>
                    <p class="text-xs text-slate-400">Sebaran status respon permintaan penonton bot (Menunggu, Prioritas, Selesai, Ditolak)</p>
                </div>
                <a href="index.php?page=requests" class="px-3 py-1 rounded-lg text-xs font-semibold bg-sky-500/10 text-sky-400 border border-sky-500/20 hover:bg-sky-500/20 transition flex items-center gap-1.5">
                    <i class="fas fa-inbox text-[10px]"></i> Kelola Request
                </a>
            </div>
            <div class="h-72 w-full">
                <canvas id="requestsActivityChart"></canvas>
            </div>
        </div>

        <!-- User Composition Chart (1 Col - Tanpa Data Keuangan Sensitif) -->
        <div class="glass-panel p-6 rounded-2xl flex flex-col justify-between">
            <div class="mb-4">
                <h2 class="text-base font-bold text-white font-heading">Komposisi Penonton Bot</h2>
                <p class="text-xs text-slate-400">Distribusi anggota reguler free vs langganan VIP</p>
            </div>
            <div class="h-56 w-full relative">
                <canvas id="userCompositionChart"></canvas>
            </div>
            <div class="pt-4 border-t border-white/5 flex items-center justify-around text-center text-xs">
                <div>
                    <p class="text-slate-400">Total Penonton</p>
                    <p class="font-bold text-white text-sm"><?= number_format($stats['total_users']); ?></p>
                </div>
                <div class="h-6 w-[1px] bg-white/10"></div>
                <div>
                    <p class="text-slate-400">Pelanggan VIP</p>
                    <p class="font-bold text-amber-300 text-sm"><?= number_format($stats['active_vip_users']); ?></p>
                </div>
                <div class="h-6 w-[1px] bg-white/10"></div>
                <div>
                    <p class="text-slate-400">Rasio VIP</p>
                    <p class="font-bold text-[#00D0B6] text-sm"><?= $userComp['conversion']; ?></p>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <!-- 3. Dynamic Recent Table Section -->
    <?php if ($isSuper): ?>
    <!-- Super Admin Table: Transaksi VIP & QRIS Terbaru -->
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

    <?php else: ?>
    <!-- Staff Admin Table: Request Film Penonton Terbaru -->
    <div class="glass-panel rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-white font-heading">Permintaan Film dari Penonton Terbaru</h2>
                <p class="text-xs text-slate-400">Daftar judul drama & film yang diajukan penonton melalui bot Telegram</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="index.php?page=requests" class="px-4 py-2 rounded-xl bg-sky-500 hover:bg-sky-400 text-white text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-sky-500/20">
                    <span>Proses Semua Request</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Responsive Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm custom-table">
                <thead>
                    <tr>
                        <th>Pemohon</th>
                        <th>Telegram ID</th>
                        <th>Judul yang Diminta</th>
                        <th>Platform / Catatan</th>
                        <th>Status</th>
                        <th>Waktu Request</th>
                        <th class="text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($recentRequests)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-500 text-xs">
                            <i class="fas fa-inbox text-2xl mb-2 block opacity-40"></i>
                            Belum ada permintaan film baru dari penonton.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($recentRequests as $req): 
                            $reqUser = !empty($req['first_name']) ? $req['first_name'] : (!empty($req['username']) ? $req['username'] : 'User ' . $req['user_id']);
                            $isPriority = !empty($req['is_priority']);
                        ?>
                        <tr class="hover:bg-white/[0.02] transition">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full <?= $isPriority ? 'bg-rose-500/20 text-rose-400' : 'bg-slate-800 text-sky-400'; ?> flex items-center justify-center font-bold text-xs ring-1 ring-white/10">
                                        <?= strtoupper(substr($reqUser, 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-white text-xs"><?= htmlspecialchars($reqUser); ?></p>
                                        <p class="text-xs text-slate-400">@<?= htmlspecialchars($req['username'] ?? '-'); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="font-mono text-xs text-slate-300">
                                <?= htmlspecialchars($req['telegram_user_id'] ?? '-'); ?>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-white text-xs"><?= htmlspecialchars($req['requested_title']); ?></span>
                                    <?php if ($isPriority): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">VIP</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-xs text-slate-300">
                                    <span><?= htmlspecialchars($req['source_app'] ?? 'Umum'); ?></span>
                                    <?php if (!empty($req['notes'])): ?>
                                        <p class="text-[11px] text-slate-500 truncate max-w-xs"><?= htmlspecialchars($req['notes']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?= getStatusBadge($req['status']); ?>
                            </td>
                            <td class="text-xs text-slate-400 whitespace-nowrap">
                                <?= timeAgo($req['created_at']); ?>
                            </td>
                            <td class="text-right">
                                <a href="index.php?page=requests" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-[#00D0B6]/20 hover:text-[#00D0B6] text-slate-300 text-xs font-semibold transition" title="Tindak Lanjuti di Halaman Request">
                                    <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                                    <span>Respon</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- 4. Quick Action Shortcuts Section -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <a href="index.php?page=users" class="glass-card p-5 rounded-2xl flex items-center gap-4 group border border-white/5">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center group-hover:scale-110 transition border border-blue-500/20">
                <i class="fas fa-users text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-sm">Kelola Pelanggan</h4>
                <p class="text-xs text-slate-400">Lihat semua user masuk &amp; status VIP (<?= $stats['total_users']; ?> User)</p>
            </div>
        </a>

        <a href="index.php?page=dramas" class="glass-card p-5 rounded-2xl flex items-center gap-4 group border border-white/5">
            <div class="w-12 h-12 rounded-xl bg-[#00D0B6]/10 text-[#00D0B6] flex items-center justify-center group-hover:scale-110 transition border border-[#00D0B6]/20">
                <i class="fas fa-film text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-sm">Katalog Drama</h4>
                <p class="text-xs text-slate-400">Upload series, episode, dan link bot (<?= $dramaStats['total_dramas']; ?> Judul)</p>
            </div>
        </a>

        <?php if ($isSuper): ?>
        <a href="index.php?page=affiliate" class="glass-card p-5 rounded-2xl flex items-center gap-4 group border border-white/5">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center group-hover:scale-110 transition border border-purple-500/20">
                <i class="fas fa-money-bill-transfer text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-sm">Penarikan Affiliate</h4>
                <p class="text-xs text-slate-400"><?= $stats['pending_withdrawals']; ?> permintaan menunggu pencairan</p>
            </div>
        </a>
        <?php else: ?>
        <a href="index.php?page=requests" class="glass-card p-5 rounded-2xl flex items-center gap-4 group border border-white/5">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center group-hover:scale-110 transition border border-amber-500/20">
                <i class="fas fa-inbox text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-white text-sm">Request Film Penonton</h4>
                <p class="text-xs text-slate-400"><?= $reqStats['pending_count']; ?> permintaan film menunggu respon</p>
            </div>
        </a>
        <?php endif; ?>
    </div>

</div>
