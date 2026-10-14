<?php
// Halaman Program Affiliate & Verifikasi Penarikan Saldo
$withdrawals = getAffiliateWithdrawalsList();
?>

<div class="space-y-6">

    <!-- Header Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-xl">
            <span class="text-xs text-slate-400 block mb-1">Menunggu Pencairan</span>
            <span class="text-xl font-bold text-amber-400">2 Permintaan</span>
            <p class="text-[11px] text-slate-500 mt-1">Perlu ditransfer segera</p>
        </div>
        <div class="glass-card p-4 rounded-xl">
            <span class="text-xs text-slate-400 block mb-1">Total Komisi Ditarik</span>
            <span class="text-xl font-bold text-emerald-400">Rp 1.450.000</span>
            <p class="text-[11px] text-slate-500 mt-1">18 transaksi selesai</p>
        </div>
        <div class="glass-card p-4 rounded-xl">
            <span class="text-xs text-slate-400 block mb-1">Saldo Koin Beredar</span>
            <span class="text-xl font-bold text-sky-400">275.000 Koin</span>
            <p class="text-[11px] text-slate-500 mt-1">1 Koin = Rp 1</p>
        </div>
        <div class="glass-card p-4 rounded-xl">
            <span class="text-xs text-slate-400 block mb-1">Total Mitra Affiliate</span>
            <span class="text-xl font-bold text-white">48 Mitra</span>
            <p class="text-[11px] text-slate-500 mt-1">Aktif membagikan referral</p>
        </div>
    </div>

    <!-- Table Permintaan Penarikan -->
    <div class="glass-panel rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-white font-heading">Daftar Permintaan Penarikan Dana</h2>
                <p class="text-xs text-slate-400">Verifikasi transfer komisi ke rekening bank atau dompet digital affiliate</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm custom-table">
                <thead>
                    <tr>
                        <th>Pelanggan Affiliate</th>
                        <th>Tujuan Pencairan</th>
                        <th>Nomor Rekening / HP</th>
                        <th>Atas Nama</th>
                        <th>Penarikan Koin</th>
                        <th>Fee Admin (5%)</th>
                        <th>Jumlah Bersih Cair</th>
                        <th>Status</th>
                        <th class="text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach ($withdrawals as $w): ?>
                    <tr class="hover:bg-white/[0.02] transition">
                        <td>
                            <div>
                                <p class="font-semibold text-white"><?= htmlspecialchars($w['first_name'] ?? 'User'); ?></p>
                                <p class="text-xs text-slate-400">@<?= htmlspecialchars($w['username'] ?? '-'); ?></p>
                            </div>
                        </td>
                        <td>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-800 text-[#00D0B6] border border-white/5">
                                <?= htmlspecialchars($w['bank_or_ewallet_name']); ?>
                            </span>
                        </td>
                        <td class="font-mono text-xs text-slate-300">
                            <?= htmlspecialchars($w['account_number']); ?>
                        </td>
                        <td class="text-xs font-medium text-slate-200">
                            <?= htmlspecialchars($w['account_holder_name']); ?>
                        </td>
                        <td class="font-semibold text-amber-300 text-xs">
                            <?= number_format($w['amount_coin']); ?> Koin
                        </td>
                        <td class="text-xs text-rose-400">
                            - <?= formatRupiah($w['fee_amount']); ?>
                        </td>
                        <td class="font-bold text-emerald-400 text-sm">
                            <?= formatRupiah($w['amount_after_fee']); ?>
                        </td>
                        <td>
                            <?= getStatusBadge($w['status']); ?>
                        </td>
                        <td class="text-right">
                            <?php if ($w['status'] === 'pending'): ?>
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="showToast('Transfer sebesar <?= formatRupiah($w['amount_after_fee']); ?> berhasil disetujui!', 'success')" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold transition flex items-center gap-1 shadow-lg shadow-emerald-500/20">
                                    <i class="fas fa-check text-[10px]"></i>
                                    <span>Setujui</span>
                                </button>
                                <button onclick="showToast('Permintaan penarikan ditolak', 'error')" class="px-3 py-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500 text-rose-300 hover:text-white text-xs font-semibold transition border border-rose-500/30">
                                    Tolak
                                </button>
                            </div>
                            <?php else: ?>
                            <span class="text-xs text-slate-500">Telah Diproses</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
