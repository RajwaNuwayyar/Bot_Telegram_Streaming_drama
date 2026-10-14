<?php
// Halaman Request Film Masuk dari Pengguna Bot
$requests = getFilmRequestsList();
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-white font-heading">Permintaan Film & Drama Masuk</h2>
            <p class="text-xs text-slate-400">Daftar judul drama yang direquest oleh pengguna bot / mini app</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                <i class="fas fa-star mr-1"></i> VIP Request Diprioritaskan
            </span>
        </div>
    </div>

    <!-- Table Requests -->
    <div class="glass-panel rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm custom-table">
                <thead>
                    <tr>
                        <th>Prioritas</th>
                        <th>Judul Drama Diminta</th>
                        <th>Sumber / Platform</th>
                        <th>Catatan User</th>
                        <th>Pelanggan Pengirim</th>
                        <th>Status</th>
                        <th>Waktu Request</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php foreach ($requests as $r): ?>
                    <tr class="hover:bg-white/[0.02] transition">
                        <td>
                            <?php if ($r['is_priority']): ?>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-gradient-to-r from-amber-500 to-amber-300 text-slate-950 inline-flex items-center gap-1 shadow-sm">
                                    <i class="fas fa-crown text-[9px]"></i> VIP
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-800 text-slate-400">
                                    Reguler
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="font-bold text-white">
                            <?= htmlspecialchars($r['requested_title']); ?>
                        </td>
                        <td class="text-xs text-slate-300">
                            <?= htmlspecialchars($r['source_app'] ?? '-'); ?>
                        </td>
                        <td class="text-xs text-slate-400 max-w-xs truncate">
                            <?= htmlspecialchars($r['notes'] ?? '-'); ?>
                        </td>
                        <td>
                            <div>
                                <p class="font-semibold text-xs text-white"><?= htmlspecialchars($r['first_name'] ?? 'User'); ?></p>
                                <p class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($r['telegram_user_id']); ?></p>
                            </div>
                        </td>
                        <td>
                            <?= getStatusBadge($r['status']); ?>
                        </td>
                        <td class="text-xs text-slate-400">
                            <?= timeAgo($r['created_at']); ?>
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button onclick="showToast('Status drama diubah: Telah ditambahkan!', 'success')" class="p-2 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 transition" title="Tandai Selesai / Uploaded">
                                    <i class="fas fa-check text-xs"></i>
                                </button>
                                <button onclick="showToast('Request ditolak', 'error')" class="p-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 transition" title="Tolak Request">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
