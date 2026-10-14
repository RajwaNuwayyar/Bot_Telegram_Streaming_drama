<?php
// Halaman Pemantauan Pendapatan & Transaksi VIP
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$allTx = getRecentVipPurchases(50);

if ($filter !== 'all') {
    $allTx = array_filter($allTx, fn($t) => strtolower($t['status']) === strtolower($filter));
}

// Hitung metrik pendapatan
$totalRevenue = 18750000;
$todayRevenue = 245000;
$weekRevenue = 1680000;
$monthRevenue = 4820000;

// Paket VIP
$plans = [
    [
        'id' => 1,
        'name' => 'VIP 7 Hari',
        'badge' => 'Trial',
        'duration_days' => 7,
        'price' => 12000,
        'sales_count' => 184,
        'color' => 'from-blue-500/20 to-cyan-500/10'
    ],
    [
        'id' => 2,
        'name' => 'VIP 30 Hari',
        'badge' => 'Paling Populer',
        'duration_days' => 30,
        'price' => 35000,
        'sales_count' => 312,
        'color' => 'from-[#00D0B6]/25 to-teal-500/10'
    ],
    [
        'id' => 3,
        'name' => 'VIP 90 Hari',
        'badge' => 'Hemat 25%',
        'duration_days' => 90,
        'price' => 89000,
        'sales_count' => 76,
        'color' => 'from-purple-500/20 to-indigo-500/10'
    ],
    [
        'id' => 4,
        'name' => 'VIP 1 Tahun',
        'badge' => 'Ultra VIP',
        'duration_days' => 365,
        'price' => 249000,
        'sales_count' => 40,
        'color' => 'from-amber-500/20 to-yellow-500/10'
    ]
];
?>

<div class="space-y-8">

    <!-- 1. Revenue Metrics Header -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Hari Ini -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Pendapatan Hari Ini</span>
            <h3 class="text-2xl font-extrabold text-white tracking-tight"><?= formatRupiah($todayRevenue); ?></h3>
            <p class="text-xs text-emerald-400 mt-2 font-medium flex items-center gap-1">
                <i class="fas fa-check-circle"></i> 7 transaksi baru
            </p>
        </div>

        <!-- Minggu Ini -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Pendapatan Minggu Ini</span>
            <h3 class="text-2xl font-extrabold text-sky-300 tracking-tight"><?= formatRupiah($weekRevenue); ?></h3>
            <p class="text-xs text-sky-400 mt-2 font-medium flex items-center gap-1">
                <i class="fas fa-arrow-trend-up"></i> +12% dibanding minggu lalu
            </p>
        </div>

        <!-- Bulan Ini -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Pendapatan Bulan Ini</span>
            <h3 class="text-2xl font-extrabold text-[#00D0B6] tracking-tight"><?= formatRupiah($monthRevenue); ?></h3>
            <p class="text-xs text-emerald-400 mt-2 font-medium flex items-center gap-1">
                <i class="fas fa-arrow-trend-up"></i> +18.4% pertumbuhan
            </p>
        </div>

        <!-- Total Pendapatan -->
        <div class="glass-card p-5 rounded-2xl relative overflow-hidden border-[#00D0B6]/30">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Akumulasi Seluruh Pendapatan</span>
            <h3 class="text-2xl font-black text-white tracking-tight"><?= formatRupiah($totalRevenue); ?></h3>
            <p class="text-xs text-amber-400 mt-2 font-medium flex items-center gap-1">
                <i class="fas fa-crown"></i> 612 langganan terjual
            </p>
        </div>
    </div>

    <!-- 2. Paket Berlangganan VIP Cards -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-white font-heading">Paket Langganan VIP Aktif</h2>
                <p class="text-xs text-slate-400">Paket VIP yang saat ini tampil di Mini App & Bot Telegram</p>
            </div>
            <button onclick="showToast('Fitur edit harga paket VIP')" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 transition border border-white/5 flex items-center gap-2">
                <i class="fas fa-cog"></i>
                <span>Atur Harga Paket</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($plans as $p): ?>
            <div class="glass-card p-5 rounded-2xl bg-gradient-to-b <?= $p['color']; ?> border border-white/10 relative">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/10 text-white border border-white/10 inline-block mb-3">
                    <?= $p['badge']; ?>
                </span>
                <h4 class="text-base font-bold text-white"><?= $p['name']; ?></h4>
                <p class="text-xs text-slate-400 mb-4">Durasi <?= $p['duration_days']; ?> Hari Akses Penuh</p>
                <div class="pt-3 border-t border-white/10 flex items-center justify-between">
                    <span class="text-lg font-black text-[#00D0B6]"><?= formatRupiah($p['price']); ?></span>
                    <span class="text-xs text-slate-400 font-medium"><?= $p['sales_count']; ?> Terjual</span>
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
                    <p class="text-xs text-slate-400">Seluruh transaksi pembelian paket VIP dari mini app</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative w-64">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                        <input type="text" id="txSearchInput" placeholder="Cari Ref ID atau Nama..." class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-900 border border-white/10 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-[#00D0B6]">
                    </div>
                    <button onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition border border-white/5 flex items-center gap-1.5">
                        <i class="fas fa-file-export"></i>
                        <span>Export</span>
                    </button>
                </div>
            </div>

            <!-- Filter Status Tab -->
            <div class="flex items-center gap-2 text-xs">
                <a href="index.php?page=vip&filter=all" class="px-3 py-1.5 rounded-lg font-medium transition <?= ($filter === 'all') ? 'bg-[#00D0B6] text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Semua Status
                </a>
                <a href="index.php?page=vip&filter=paid" class="px-3 py-1.5 rounded-lg font-medium transition <?= ($filter === 'paid') ? 'bg-emerald-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Paid (Sukses)
                </a>
                <a href="index.php?page=vip&filter=pending" class="px-3 py-1.5 rounded-lg font-medium transition <?= ($filter === 'pending') ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Pending
                </a>
                <a href="index.php?page=vip&filter=expired" class="px-3 py-1.5 rounded-lg font-medium transition <?= ($filter === 'expired') ? 'bg-rose-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Expired
                </a>
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
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Dibayar Pada</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="txTableBody" class="divide-y divide-white/5">
                    <?php if (empty($allTx)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-10 text-slate-500">
                            Tidak ada transaksi dengan status ini.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($allTx as $tx): ?>
                        <tr class="hover:bg-white/[0.02] transition">
                            <td class="font-mono text-xs text-slate-400">#<?= $tx['id']; ?></td>
                            <td>
                                <span class="font-mono text-xs text-[#00D0B6] font-semibold bg-[#00D0B6]/10 px-2 py-0.5 rounded border border-[#00D0B6]/20">
                                    <?= htmlspecialchars($tx['gateway_ref_id'] ?? '-'); ?>
                                </span>
                            </td>
                            <td>
                                <div>
                                    <p class="font-semibold text-white"><?= htmlspecialchars($tx['first_name'] ?? 'User'); ?></p>
                                    <p class="text-xs text-slate-400 font-mono"><?= htmlspecialchars($tx['telegram_user_id']); ?></p>
                                </div>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1 text-xs text-amber-300 font-medium">
                                    <i class="fas fa-crown text-[10px]"></i>
                                    <?= htmlspecialchars($tx['plan_name'] ?? 'Paket VIP'); ?>
                                </span>
                            </td>
                            <td class="font-bold text-white">
                                <?= formatRupiah($tx['amount']); ?>
                            </td>
                            <td>
                                <?= getStatusBadge($tx['status']); ?>
                            </td>
                            <td class="text-xs text-slate-400">
                                <?= date('d/m/Y H:i', strtotime($tx['created_at'])); ?>
                            </td>
                            <td class="text-xs text-slate-400">
                                <?= !empty($tx['paid_at']) ? date('d/m/Y H:i', strtotime($tx['paid_at'])) : '-'; ?>
                            </td>
                            <td class="text-right">
                                <button onclick="showToast('Data struk transaksi #<?= $tx['id']; ?> tercatat', 'info')" class="p-2 rounded-lg bg-slate-800 hover:bg-[#00D0B6]/20 hover:text-[#00D0B6] text-slate-400 transition" title="Lihat Rincian">
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
