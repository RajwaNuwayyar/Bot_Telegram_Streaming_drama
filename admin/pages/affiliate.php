<?php
// Halaman Program Affiliate & Verifikasi Penarikan Saldo Riil
$actionResult = null;

// Tangani aksi persetujuan / penolakan dari admin
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['wd_id'], $_POST['wd_action'])) {
    $wdId = (int)$_POST['wd_id'];
    $wdAction = trim($_POST['wd_action']);
    $adminNotes = trim($_POST['admin_notes'] ?? '');
    $actionResult = processAffiliateWithdrawal($wdId, $wdAction, $adminNotes);
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$affStats = getAffiliateStats();
$withdrawals = getAffiliateWithdrawalsList($filter, $search);

// Hitung cepat untuk badge tab
$allWithdrawalsRaw = getAffiliateWithdrawalsList('all');
$pendingCount = count(array_filter($allWithdrawalsRaw, fn($w) => in_array(strtolower($w['status']), ['pending', 'diproses'])));
$completedCount = count(array_filter($allWithdrawalsRaw, fn($w) => in_array(strtolower($w['status']), ['completed', 'terkirim'])));
$rejectedCount = count(array_filter($allWithdrawalsRaw, fn($w) => in_array(strtolower($w['status']), ['rejected', 'ditolak'])));
?>

<div class="space-y-6">

    <!-- Notifikasi Aksi -->
    <?php if ($actionResult): ?>
    <div class="p-4 rounded-xl <?= $actionResult['success'] ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-300' : 'bg-rose-500/10 border border-rose-500/20 text-rose-300'; ?> flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas <?= $actionResult['success'] ? 'fa-check-circle text-emerald-400' : 'fa-exclamation-circle text-rose-400'; ?> text-base"></i>
            <span class="text-xs font-semibold"><?= htmlspecialchars($actionResult['message']); ?></span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-xs opacity-70 hover:opacity-100">✕</button>
    </div>
    <?php endif; ?>

    <!-- Header Stats (100% Real Database Data) -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <!-- 1. Menunggu Pencairan -->
        <div class="glass-card p-4 rounded-xl border border-amber-500/20 relative overflow-hidden">
            <span class="text-xs text-slate-400 block mb-1">Menunggu Pencairan</span>
            <span class="text-2xl font-bold text-amber-400"><?= number_format($affStats['pending_count']); ?> Permintaan</span>
            <p class="text-[11px] text-slate-500 mt-1"><?= ($affStats['pending_count'] > 0) ? 'Perlu ditransfer segera' : 'Semua transaksi sudah diproses'; ?></p>
        </div>

        <!-- 2. Total Komisi Ditarik -->
        <div class="glass-card p-4 rounded-xl border border-emerald-500/20 relative overflow-hidden">
            <span class="text-xs text-slate-400 block mb-1">Total Komisi Ditarik</span>
            <span class="text-2xl font-bold text-emerald-400"><?= formatRupiah($affStats['withdrawn_total']); ?></span>
            <p class="text-[11px] text-slate-500 mt-1"><?= number_format($affStats['withdrawn_count']); ?> transaksi berhasil ditransfer</p>
        </div>

        <!-- 3. Saldo Koin Beredar -->
        <div class="glass-card p-4 rounded-xl border border-sky-500/20 relative overflow-hidden">
            <span class="text-xs text-slate-400 block mb-1">Saldo Koin Beredar</span>
            <span class="text-2xl font-bold text-sky-400"><?= number_format($affStats['circulating_coins']); ?> Koin</span>
            <p class="text-[11px] text-slate-500 mt-1">Konversi 1 Koin = Rp 1</p>
        </div>

        <!-- 4. Total Mitra Affiliate -->
        <div class="glass-card p-4 rounded-xl border border-white/10 relative overflow-hidden">
            <span class="text-xs text-slate-400 block mb-1">Mitra Affiliate</span>
            <span class="text-2xl font-bold text-white"><?= number_format($affStats['affiliates_count']); ?> Pengguna</span>
            <p class="text-[11px] text-slate-500 mt-1">Memiliki saldo / status affiliate</p>
        </div>
    </div>

    <!-- Container Table Permintaan Penarikan -->
    <div class="glass-panel rounded-2xl overflow-hidden">
        
        <!-- Toolbar & Filter Tabs -->
        <div class="p-6 border-b border-white/5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-white font-heading">Daftar Permintaan Penarikan Komisi</h2>
                    <p class="text-xs text-slate-400">Verifikasi dan catat pencairan komisi mitra affiliate ke DANA atau rekening bank</p>
                </div>

                <!-- Live Search Box -->
                <div class="relative w-full sm:w-72">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text" id="txSearchInput" placeholder="Cari ID, Nama, No HP..." class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-900 border border-white/10 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-[#00D0B6] transition">
                </div>
            </div>

            <!-- Tab Filters -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                <a href="index.php?page=affiliate&filter=all" class="px-3.5 py-1.5 rounded-lg font-medium transition <?= ($filter === 'all') ? 'bg-[#00D0B6] text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Semua (<?= count($allWithdrawalsRaw); ?>)
                </a>
                <a href="index.php?page=affiliate&filter=diproses" class="px-3.5 py-1.5 rounded-lg font-medium transition <?= ($filter === 'diproses' || $filter === 'pending') ? 'bg-amber-400 text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-clock mr-1 text-[10px]"></i> Menunggu Transfer (<?= $pendingCount; ?>)
                </a>
                <a href="index.php?page=affiliate&filter=terkirim" class="px-3.5 py-1.5 rounded-lg font-medium transition <?= ($filter === 'terkirim' || $filter === 'completed') ? 'bg-emerald-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-check-circle mr-1 text-[10px]"></i> Terkirim (<?= $completedCount; ?>)
                </a>
                <a href="index.php?page=affiliate&filter=ditolak" class="px-3.5 py-1.5 rounded-lg font-medium transition <?= ($filter === 'ditolak' || $filter === 'rejected') ? 'bg-rose-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-times-circle mr-1 text-[10px]"></i> Ditolak (<?= $rejectedCount; ?>)
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm custom-table">
                <thead>
                    <tr>
                        <th>ID Penarikan</th>
                        <th>Pelanggan Affiliate</th>
                        <th>Tujuan Pencairan</th>
                        <th>Nomor Rekening / HP</th>
                        <th>Atas Nama</th>
                        <th>Koin Ditarik</th>
                        <th>Fee Admin</th>
                        <th>Bersih Ditransfer</th>
                        <th>Status</th>
                        <th>Waktu Request</th>
                        <th class="text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody id="txTableBody" class="divide-y divide-white/5">
                    <?php if (empty($withdrawals)): ?>
                    <tr>
                        <td colspan="11" class="text-center py-12 text-slate-500 text-xs">
                            <i class="fas fa-wallet text-3xl mb-3 block"></i>
                            Tidak ada data permohonan penarikan pada kategori ini.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($withdrawals as $w): 
                            $isPending = in_array(strtolower($w['status'] ?? ''), ['pending', 'diproses']);
                            $displayName = !empty($w['first_name']) ? $w['first_name'] : (!empty($w['username']) ? $w['username'] : 'User ' . $w['user_id']);
                            $feePercent = rtrim(rtrim((string)($w['fee_percent'] ?? '0'), '0'), '.');
                        ?>
                        <tr class="hover:bg-white/[0.02] transition">
                            <!-- ID Penarikan (Format Bot: #WD_ID) -->
                            <td class="font-mono text-xs">
                                <span class="px-2.5 py-1 rounded-md bg-slate-800 text-[#00D0B6] font-bold border border-[#00D0B6]/20">
                                    #WD_<?= htmlspecialchars($w['id']); ?>
                                </span>
                            </td>

                            <!-- Pelanggan -->
                            <td>
                                <div>
                                    <p class="font-semibold text-white text-xs"><?= htmlspecialchars($displayName); ?></p>
                                    <p class="text-[11px] text-slate-400 font-mono">TG: <?= htmlspecialchars($w['telegram_user_id'] ?? '-'); ?></p>
                                </div>
                            </td>

                            <!-- E-Wallet / Bank -->
                            <td>
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-800 text-sky-400 border border-white/5">
                                    <?= htmlspecialchars($w['bank_or_ewallet_name'] ?? 'DANA'); ?>
                                </span>
                            </td>

                            <!-- Nomor Rekening / HP -->
                            <td class="font-mono text-xs text-slate-200">
                                <?= htmlspecialchars($w['account_number']); ?>
                            </td>

                            <!-- Atas Nama -->
                            <td class="text-xs font-medium text-slate-300">
                                <?= htmlspecialchars($w['account_holder_name']); ?>
                            </td>

                            <!-- Nominal Koin -->
                            <td class="font-semibold text-amber-300 text-xs whitespace-nowrap">
                                <?= number_format($w['amount_coin']); ?> Koin
                            </td>

                            <!-- Fee Admin -->
                            <td class="text-xs text-rose-400 whitespace-nowrap">
                                - <?= formatRupiah($w['fee_amount']); ?>
                                <span class="text-[10px] text-slate-500 block">(<?= $feePercent; ?>%)</span>
                            </td>

                            <!-- Jumlah Bersih Cair -->
                            <td class="font-bold text-emerald-400 text-xs whitespace-nowrap">
                                <?= formatRupiah($w['amount_after_fee']); ?>
                            </td>

                            <!-- Status Badge -->
                            <td>
                                <?= getStatusBadge($w['status']); ?>
                            </td>

                            <!-- Waktu Pengajuan -->
                            <td class="text-xs text-slate-400 whitespace-nowrap">
                                <?= date('d M Y, H:i', strtotime($w['requested_at'])); ?>
                            </td>

                            <!-- Tombol Aksi Verifikasi Nyata -->
                            <td class="text-right">
                                <?php if ($isPending): ?>
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Tombol Setujui -->
                                    <button onclick="confirmApprove(<?= $w['id']; ?>, '<?= formatRupiah($w['amount_after_fee']); ?>', '<?= htmlspecialchars($displayName); ?>')" class="px-2.5 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold transition flex items-center gap-1 shadow-md shadow-emerald-500/20" title="Tandai Sudah Ditransfer">
                                        <i class="fas fa-check text-[10px]"></i>
                                        <span>Setujui</span>
                                    </button>

                                    <!-- Tombol Tolak -->
                                    <button onclick="confirmReject(<?= $w['id']; ?>, <?= $w['amount_coin']; ?>, '<?= htmlspecialchars($displayName); ?>')" class="px-2.5 py-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500 text-rose-300 hover:text-white text-xs font-semibold transition border border-rose-500/30" title="Tolak & Kembalikan Saldo Koin">
                                        <span>Tolak</span>
                                    </button>
                                </div>
                                <?php else: ?>
                                    <span class="text-xs text-slate-500">
                                        <?= strtolower($w['status']) === 'terkirim' ? '<i class="fas fa-check text-emerald-400 mr-1"></i>Selesai' : '<i class="fas fa-ban text-rose-400 mr-1"></i>Ditolak'; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Konfirmasi Setujui Transfer -->
<div id="approveModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-sm w-full p-6 shadow-2xl relative">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i class="fas fa-check-circle text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-sm">Verifikasi Transfer Komisi</h3>
                <p class="text-xs text-slate-400" id="approveWdLabel">#WD_0</p>
            </div>
        </div>
        
        <p class="text-xs text-slate-300 mb-4" id="approveWdText">
            Pastikan Anda telah mentransfer dana ke nomor tujuan. Status akan diubah menjadi <strong>Terkirim</strong>.
        </p>

        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="wd_id" id="approveWdId" value="">
            <input type="hidden" name="wd_action" value="approve">

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Tambahan (Opsional)</label>
                <input type="text" name="admin_notes" placeholder="Contoh: Ditransfer via DANA Admin" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-white/10 text-xs text-white focus:outline-none focus:border-emerald-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('approveModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold shadow-lg shadow-emerald-500/20">Konfirmasi Terkirim</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Tolak Penarikan (Refund Koin) -->
<div id="rejectModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-sm w-full p-6 shadow-2xl relative">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-sm">Tolak Permintaan Penarikan</h3>
                <p class="text-xs text-slate-400" id="rejectWdLabel">#WD_0</p>
            </div>
        </div>
        
        <p class="text-xs text-slate-300 mb-4" id="rejectWdText">
            Saldo koin akan otomatis dikembalikan ke akun pengguna.
        </p>

        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="wd_id" id="rejectWdId" value="">
            <input type="hidden" name="wd_action" value="reject">

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Alasan Penolakan *</label>
                <input type="text" name="admin_notes" required placeholder="Contoh: Nomor DANA salah / tidak aktif" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-white/10 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('rejectModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold shadow-lg shadow-rose-500/20">Tolak & Refund Koin</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmApprove(id, amountFormatted, userName) {
    document.getElementById('approveWdId').value = id;
    document.getElementById('approveWdLabel').innerText = '#WD_' + id + ' • ' + userName;
    document.getElementById('approveWdText').innerHTML = 'Pastikan Anda telah mentransfer sebesar <strong>' + amountFormatted + '</strong> ke nomor tujuan pelanggan. Klik tombol di bawah untuk menetapkan status <strong>Terkirim</strong>.';
    openModal('approveModal');
}

function confirmReject(id, coins, userName) {
    document.getElementById('rejectWdId').value = id;
    document.getElementById('rejectWdLabel').innerText = '#WD_' + id + ' • ' + userName;
    document.getElementById('rejectWdText').innerHTML = 'Permintaan akan dibatalkan, dan saldo sebesar <strong>' + coins.toLocaleString('id-ID') + ' koin</strong> akan otomatis dikembalikan ke akun <strong>' + userName + '</strong>.';
    openModal('rejectModal');
}
</script>
