<?php
// Halaman Request Film Masuk dari Pengguna Bot Riil
$actionResult = null;

// Tangani aksi permintaan film (POST)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['request_action'], $_POST['request_id'])) {
    $action = trim($_POST['request_action']);
    $reqId = (int)$_POST['request_id'];
    $adminNotes = trim($_POST['admin_notes'] ?? '');
    $linkedDramaId = !empty($_POST['linked_drama_id']) ? (int)$_POST['linked_drama_id'] : null;

    $actionResult = processFilmRequest($reqId, $action, $adminNotes, $linkedDramaId);
}

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$requests = getFilmRequestsList($filter, $search);
$stats = getFilmRequestStats();
$dramasCatalog = getDramasCatalog();
?>

<div class="space-y-6">

    <!-- Notifikasi Aksi -->
    <?php if ($actionResult): ?>
    <div class="p-4 rounded-xl <?= $actionResult['success'] ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-300' : 'bg-rose-500/10 border border-rose-500/20 text-rose-300'; ?> flex items-center justify-between animate-slide-up">
        <div class="flex items-center gap-3">
            <i class="fas <?= $actionResult['success'] ? 'fa-check-circle text-emerald-400' : 'fa-exclamation-circle text-rose-400'; ?> text-base"></i>
            <span class="text-xs font-semibold"><?= htmlspecialchars($actionResult['message']); ?></span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-xs opacity-70 hover:opacity-100">✕</button>
    </div>
    <?php endif; ?>

    <!-- Summary Stats Header (100% Real Database Data) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-xl flex items-center justify-between border border-white/5">
            <div>
                <p class="text-xs text-slate-400">Total Permintaan</p>
                <p class="text-xl font-bold text-white"><?= number_format($stats['total_requests']); ?></p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center">
                <i class="fas fa-inbox text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex items-center justify-between border border-white/5">
            <div>
                <p class="text-xs text-slate-400">Menunggu Respon</p>
                <p class="text-xl font-bold text-amber-400"><?= number_format($stats['pending_count']); ?></p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i class="fas fa-clock text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex items-center justify-between border border-white/5">
            <div>
                <p class="text-xs text-slate-400">Prioritas VIP</p>
                <p class="text-xl font-bold text-amber-300"><?= number_format($stats['priority_count']); ?></p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i class="fas fa-crown text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex items-center justify-between border border-white/5">
            <div>
                <p class="text-xs text-slate-400">Selesai Ditambahkan</p>
                <p class="text-xl font-bold text-emerald-400"><?= number_format($stats['completed_count']); ?></p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i class="fas fa-check-circle text-sm"></i>
            </div>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="glass-panel rounded-2xl overflow-hidden border border-white/5">
        
        <!-- Toolbar & Filter Tabs -->
        <div class="p-6 border-b border-white/5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-white font-heading">Permintaan Film & Drama Masuk</h2>
                    <p class="text-xs text-slate-400">Antrean judul yang direquest oleh pengguna bot / mini app</p>
                </div>

                <!-- Search Input -->
                <form method="GET" action="index.php" class="relative max-w-xs w-full">
                    <input type="hidden" name="page" value="requests">
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter); ?>">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text" name="search" id="reqSearchInput" value="<?= htmlspecialchars($search); ?>" placeholder="Cari judul atau user..." class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-900 border border-white/10 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-[#00D0B6] transition">
                </form>
            </div>

            <!-- Tab Filters with Dynamic Counters -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 hide-scroll text-xs">
                <a href="index.php?page=requests&filter=all<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'all') ? 'bg-[#00D0B6] text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Semua (<?= $stats['total_requests']; ?>)
                </a>
                <a href="index.php?page=requests&filter=pending<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'pending') ? 'bg-amber-400 text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-clock mr-1"></i> Menunggu Respon (<?= $stats['pending_count']; ?>)
                </a>
                <a href="index.php?page=requests&filter=priority<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'priority') ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-crown mr-1"></i> Prioritas VIP (<?= $stats['priority_count']; ?>)
                </a>
                <a href="index.php?page=requests&filter=completed<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'completed') ? 'bg-emerald-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-check mr-1"></i> Selesai (<?= $stats['completed_count']; ?>)
                </a>
                <a href="index.php?page=requests&filter=rejected<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'rejected') ? 'bg-rose-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-times mr-1"></i> Ditolak (<?= $stats['rejected_count']; ?>)
                </a>
            </div>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm custom-table">
                <thead>
                    <tr>
                        <th>Prioritas</th>
                        <th>Judul Drama Diminta</th>
                        <th>Sumber Platform</th>
                        <th>Catatan Pemohon</th>
                        <th>Pelanggan Pengirim</th>
                        <th>Status</th>
                        <th>Waktu Request</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="reqTableBody" class="divide-y divide-white/5">
                    <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-500">
                            <i class="fas fa-inbox text-3xl mb-3 block opacity-40"></i>
                            Tidak ada data permintaan film yang sesuai.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): 
                            $isVipReq = (int)($r['is_priority'] ?? 0) === 1;
                            $userDisplay = !empty($r['first_name']) ? $r['first_name'] : 'Pengguna Bot';
                        ?>
                        <tr class="hover:bg-white/[0.02] transition">
                            <!-- Prioritas Badge -->
                            <td>
                                <?php if ($isVipReq): ?>
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-black bg-gradient-to-r from-amber-500 to-amber-300 text-slate-950 inline-flex items-center gap-1 shadow-md shadow-amber-500/20">
                                        <i class="fas fa-crown text-[9px]"></i> VIP PRIORITY
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-800 text-slate-400 border border-white/5">
                                        Reguler
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Judul Drama -->
                            <td>
                                <p class="font-bold text-white text-sm"><?= htmlspecialchars($r['requested_title']); ?></p>
                                <?php if (!empty($r['linked_drama_title'])): ?>
                                    <span class="text-[10px] text-[#00D0B6] flex items-center gap-1 mt-0.5">
                                        <i class="fas fa-link"></i> Tertaut: <?= htmlspecialchars($r['linked_drama_title']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($r['admin_notes'])): ?>
                                    <p class="text-[10px] text-slate-400 italic mt-0.5">Catatan Admin: <?= htmlspecialchars($r['admin_notes']); ?></p>
                                <?php endif; ?>
                            </td>

                            <!-- Platform -->
                            <td class="text-xs text-slate-300">
                                <span class="px-2 py-1 rounded bg-slate-800 border border-white/5 font-mono text-[11px]">
                                    <?= htmlspecialchars($r['source_app'] ?? '-'); ?>
                                </span>
                            </td>

                            <!-- Catatan -->
                            <td class="text-xs text-slate-400 max-w-xs">
                                <p class="line-clamp-2" title="<?= htmlspecialchars($r['notes'] ?? '-'); ?>">
                                    <?= !empty($r['notes']) ? htmlspecialchars($r['notes']) : '<span class="italic text-slate-600">-</span>'; ?>
                                </p>
                            </td>

                            <!-- Pengirim -->
                            <td>
                                <div class="text-xs">
                                    <p class="font-semibold text-white"><?= htmlspecialchars($userDisplay); ?></p>
                                    <p class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($r['telegram_user_id']); ?></p>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td>
                                <?= getStatusBadge($r['status']); ?>
                            </td>

                            <!-- Waktu -->
                            <td class="text-xs text-slate-400">
                                <p><?= timeAgo($r['created_at']); ?></p>
                                <span class="text-[10px] text-slate-500"><?= date('d M Y, H:i', strtotime($r['created_at'])); ?></span>
                            </td>

                            <!-- Aksi Menu -->
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <?php if ($r['status'] !== 'completed'): ?>
                                    <!-- Selesai / Uploaded -->
                                    <button onclick="openCompleteRequestModal(<?= $r['id']; ?>, '<?= htmlspecialchars(addslashes($r['requested_title'])); ?>')" class="p-2 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 transition" title="Tandai Selesai / Telah Diunggah">
                                        <i class="fas fa-check text-xs"></i>
                                    </button>
                                    <?php endif; ?>

                                    <?php if ($r['status'] !== 'rejected'): ?>
                                    <!-- Tolak Request -->
                                    <button onclick="openRejectRequestModal(<?= $r['id']; ?>, '<?= htmlspecialchars(addslashes($r['requested_title'])); ?>')" class="p-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 transition" title="Tolak Permintaan">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                    <?php endif; ?>

                                    <?php if ($r['status'] !== 'pending'): ?>
                                    <!-- Kembalikan ke Pending -->
                                    <form method="POST" action="" class="inline" onsubmit="return confirm('Kembalikan status permintaan ke Pending?');">
                                        <input type="hidden" name="request_action" value="pending">
                                        <input type="hidden" name="request_id" value="<?= $r['id']; ?>">
                                        <button type="submit" class="p-2 rounded-lg bg-slate-800 hover:bg-amber-500/20 text-slate-400 hover:text-amber-400 transition" title="Kembalikan ke Status Pending">
                                            <i class="fas fa-rotate-left text-xs"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Summary -->
        <div class="p-4 border-t border-white/5 bg-slate-900/40 flex items-center justify-between text-xs text-slate-400">
            <p>Menampilkan <?= count($requests); ?> dari <?= $stats['total_requests']; ?> total permintaan terdata.</p>
        </div>

    </div>

</div>

<!-- Modal 1: Tandai Selesai / Telah Diunggah (POST Form) -->
<div id="completeRequestModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('completeRequestModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base">Tandai Selesai / Terpenuhi</h3>
                <p class="text-xs text-slate-400">Perbarui status permintaan yang telah diunggah ke channel</p>
            </div>
        </div>

        <div class="p-3 mb-4 bg-slate-900 rounded-xl border border-white/5 text-xs">
            Judul diminta: <strong id="completeReqTitle" class="text-white">-</strong>
        </div>

        <form method="POST" action="" class="space-y-4 text-xs">
            <input type="hidden" name="request_action" value="complete">
            <input type="hidden" name="request_id" id="completeReqId" value="">

            <div>
                <label class="block text-slate-300 font-semibold mb-1">Tautkan ke Judul Drama di Katalog (Opsional)</label>
                <select name="linked_drama_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-emerald-400">
                    <option value="">-- Tidak ditautkan --</option>
                    <?php foreach ($dramasCatalog as $d): ?>
                        <option value="<?= $d['id']; ?>"><?= htmlspecialchars($d['title']); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Jika drama sudah dimasukkan ke katalog, tautkan agar sistem dapat melacaknya.</p>
            </div>

            <div>
                <label class="block text-slate-300 font-semibold mb-1">Catatan Admin</label>
                <input type="text" name="admin_notes" placeholder="Contoh: Sudah diunggah full episode di channel bot" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-emerald-400">
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('completeRequestModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold shadow-lg shadow-emerald-500/20">Tandai Selesai</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Tolak Permintaan Film (POST Form) -->
<div id="rejectRequestModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('rejectRequestModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center">
                <i class="fas fa-times-circle"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base">Tolak Permintaan Film</h3>
                <p class="text-xs text-slate-400">Beri alasan mengapa permintaan tidak dapat dipenuhi</p>
            </div>
        </div>

        <div class="p-3 mb-4 bg-slate-900 rounded-xl border border-white/5 text-xs">
            Judul diminta: <strong id="rejectReqTitle" class="text-white">-</strong>
        </div>

        <form method="POST" action="" class="space-y-4 text-xs">
            <input type="hidden" name="request_action" value="reject">
            <input type="hidden" name="request_id" id="rejectReqId" value="">

            <div>
                <label class="block text-slate-300 font-semibold mb-1">Alasan Penolakan</label>
                <input type="text" name="admin_notes" required placeholder="Contoh: File sumber tidak ditemukan / Kualitas tidak memadai" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-rose-400">
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('rejectRequestModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold shadow-lg shadow-rose-500/20">Tolak Permintaan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCompleteRequestModal(reqId, title) {
    document.getElementById('completeReqId').value = reqId;
    document.getElementById('completeReqTitle').textContent = title;
    openModal('completeRequestModal');
}

function openRejectRequestModal(reqId, title) {
    document.getElementById('rejectReqId').value = reqId;
    document.getElementById('rejectReqTitle').textContent = title;
    openModal('rejectRequestModal');
}
</script>
