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
                                <button onclick='openInvoiceModal(<?= json_encode($tx, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' class="p-2 rounded-lg bg-slate-800 hover:bg-[#00D0B6]/20 hover:text-[#00D0B6] text-slate-400 transition" title="Lihat Rincian Faktur & Pembayaran">
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

<!-- Modal Detail Rincian Faktur / Transaksi VIP -->
<div id="viewInvoiceModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-lg w-full p-6 shadow-2xl relative text-xs animate-in fade-in zoom-in-95 duration-150">
        <!-- Close button -->
        <button onclick="closeModal('viewInvoiceModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
            <i class="fas fa-times text-base"></i>
        </button>

        <!-- Header Modal -->
        <div class="flex items-center gap-3.5 mb-5 pb-4 border-b border-white/10">
            <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-lg">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-white text-base font-heading">Faktur Transaksi VIP</h3>
                    <span id="inv-modal-id" class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-800 text-slate-300 border border-white/5">#0</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">Rincian data transaksi langganan dan status gateway pembayaran</p>
            </div>
        </div>

        <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-1">
            <!-- 1. Status & Nominal Summary Card -->
            <div class="p-4 rounded-xl bg-slate-900/80 border border-white/5 flex items-center justify-between">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider block mb-1">Total Tagihan</span>
                    <h2 id="inv-modal-amount" class="text-2xl font-black text-[#00D0B6] tracking-tight">Rp 0</h2>
                    <span id="inv-modal-method" class="text-[11px] text-slate-400 font-medium">Metode: QRIS Midtrans</span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider block mb-1">Status Transaksi</span>
                    <div id="inv-modal-status-badge">
                        <!-- Badge dynamically inserted -->
                    </div>
                </div>
            </div>

            <!-- 2. Data Pelanggan -->
            <div class="p-3.5 rounded-xl bg-slate-900/40 border border-white/5 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-300 flex items-center gap-1.5">
                        <i class="fas fa-user text-slate-500"></i> Informasi Pelanggan
                    </span>
                    <a id="inv-modal-user-link" href="index.php?page=users" target="_blank" class="text-[10px] text-[#00D0B6] hover:underline font-semibold">
                        Buka di Menu Users &rarr;
                    </a>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-1 text-slate-300">
                    <div>
                        <span class="text-[10px] text-slate-500 block">Nama / Profil:</span>
                        <span id="inv-modal-user-name" class="font-bold text-white">-</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-500 block">Telegram ID:</span>
                        <span id="inv-modal-user-tg" class="font-mono text-slate-300">-</span>
                    </div>
                </div>
            </div>

            <!-- 3. Rincian Paket & Layanan -->
            <div class="p-3.5 rounded-xl bg-slate-900/40 border border-white/5 space-y-2">
                <span class="font-bold text-slate-300 flex items-center gap-1.5">
                    <i class="fas fa-crown text-amber-400"></i> Paket Langganan VIP
                </span>
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <div>
                        <span class="text-[10px] text-slate-500 block">Nama Paket:</span>
                        <span id="inv-modal-plan-name" class="font-bold text-amber-300">-</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-500 block">Durasi Langganan:</span>
                        <span id="inv-modal-plan-duration" class="font-semibold text-white">-</span>
                    </div>
                    <div class="col-span-2 pt-1 border-t border-white/5">
                        <span class="text-[10px] text-slate-500 block">Masa Aktif VIP Akun:</span>
                        <span id="inv-modal-vip-period" class="font-medium text-slate-300 text-[11px]">-</span>
                    </div>
                </div>
            </div>

            <!-- 4. Rincian Gateway Pembayaran & Timestamp -->
            <div class="p-3.5 rounded-xl bg-slate-900/40 border border-white/5 space-y-2">
                <span class="font-bold text-slate-300 flex items-center gap-1.5">
                    <i class="fas fa-qrcode text-[#00D0B6]"></i> Detail Gateway QRIS
                </span>
                <div class="space-y-1.5 pt-1 text-slate-300">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] text-slate-500">Order ID / Ref Gateway:</span>
                        <span id="inv-modal-gateway-ref" class="font-mono font-bold text-[#00D0B6] select-all">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] text-slate-500">Waktu Order Dibuat:</span>
                        <span id="inv-modal-created-at" class="text-slate-300">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] text-slate-500">Waktu Pembayaran Sukses:</span>
                        <span id="inv-modal-paid-at" class="text-slate-300 font-semibold">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="pt-4 mt-4 border-t border-white/10 flex items-center justify-between">
            <button type="button" onclick="copyInvoiceSummary()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-semibold transition flex items-center gap-1.5">
                <i class="fas fa-copy text-xs"></i> Salin Rincian
            </button>
            <button type="button" onclick="closeModal('viewInvoiceModal')" class="px-5 py-2 rounded-xl bg-[#00D0B6] hover:bg-[#00D0B6]/90 text-slate-950 font-bold transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
let currentSelectedInvoice = null;

function openInvoiceModal(tx) {
    currentSelectedInvoice = tx;
    if (!tx) return;

    document.getElementById('inv-modal-id').textContent = '#' + tx.id;
    document.getElementById('inv-modal-amount').textContent = 'Rp ' + Number(tx.amount || 0).toLocaleString('id-ID');
    
    const method = (tx.payment_method || 'QRIS').toUpperCase();
    const gateway = tx.gateway_name ? ' (' + tx.gateway_name.toUpperCase() + ')' : '';
    document.getElementById('inv-modal-method').textContent = 'Metode: ' + method + gateway;

    // Status Badge
    const st = (tx.status || 'pending').toLowerCase();
    let badgeHtml = '';
    if (st === 'paid' || st === 'completed' || st === 'active') {
        badgeHtml = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><i class="fas fa-check-circle mr-1"></i> PAID (Lunas)</span>';
    } else if (st === 'pending') {
        badgeHtml = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20"><i class="fas fa-clock mr-1"></i> PENDING (Menunggu)</span>';
    } else {
        badgeHtml = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20"><i class="fas fa-times-circle mr-1"></i> ' + st.toUpperCase() + '</span>';
    }
    document.getElementById('inv-modal-status-badge').innerHTML = badgeHtml;

    // Customer
    const custName = tx.first_name || (tx.username ? '@' + tx.username : 'User #' + tx.user_id);
    document.getElementById('inv-modal-user-name').textContent = custName;
    document.getElementById('inv-modal-user-tg').textContent = tx.telegram_user_id || '-';
    document.getElementById('inv-modal-user-link').href = 'index.php?page=users&search=' + encodeURIComponent(tx.telegram_user_id || tx.user_id);

    // Plan
    document.getElementById('inv-modal-plan-name').textContent = tx.plan_name || 'Paket VIP';
    document.getElementById('inv-modal-plan-duration').textContent = tx.duration_days ? (tx.duration_days + ' Hari') : '-';
    
    if (tx.vip_start_at && tx.vip_end_at) {
        document.getElementById('inv-modal-vip-period').textContent = formatDateTime(tx.vip_start_at) + ' s/d ' + formatDateTime(tx.vip_end_at);
    } else if (st === 'paid') {
        document.getElementById('inv-modal-vip-period').textContent = 'VIP Aktif';
    } else {
        document.getElementById('inv-modal-vip-period').textContent = 'Belum Aktif (Menunggu Pembayaran)';
    }

    // Gateway & Timestamps
    document.getElementById('inv-modal-gateway-ref').textContent = tx.gateway_ref_id || ('INV-' + tx.id);
    document.getElementById('inv-modal-created-at').textContent = formatDateTime(tx.created_at);
    document.getElementById('inv-modal-paid-at').textContent = tx.paid_at ? formatDateTime(tx.paid_at) : 'Belum Dibayar (-)';

    openModal('viewInvoiceModal');
}

function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr.replace(' ', 'T'));
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' +
               d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
    } catch(e) {
        return dateStr;
    }
}

function copyInvoiceSummary() {
    if (!currentSelectedInvoice) return;
    const tx = currentSelectedInvoice;
    const summary = `FAKTUR TRANSAKSI VIP #${tx.id}
Paket: ${tx.plan_name || 'VIP'}
Nominal: Rp ${Number(tx.amount || 0).toLocaleString('id-ID')}
Status: ${(tx.status || 'pending').toUpperCase()}
Ref Order: ${tx.gateway_ref_id || ('INV-' + tx.id)}
Pelanggan: ${tx.first_name || tx.username || tx.user_id} (TG: ${tx.telegram_user_id || '-'})
Waktu Dibuat: ${tx.created_at || '-'}
Waktu Dibayar: ${tx.paid_at || '-'}`;

    navigator.clipboard.writeText(summary).then(() => {
        showToast('Rincian faktur transaksi disalin ke clipboard!', 'success');
    }).catch(() => {
        showToast('Gagal menyalin ke clipboard', 'error');
    });
}
</script>
