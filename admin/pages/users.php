<?php
// Halaman Pendataan Pelanggan Masuk (User Management)
$actionResult = null;

// Tangani aksi manajemen pengguna (POST)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['user_action'])) {
    $action = trim($_POST['user_action']);
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    // Proteksi: Tambah VIP, Banned, dan Input Manual dibatasi khusus Super Admin (Pemilik)
    if (in_array($action, ['add_vip', 'toggle_ban', 'add_user']) && !isSuperAdmin()) {
        $actionResult = ['success' => false, 'message' => 'Aksi ini dibatasi khusus untuk Super Admin (Pemilik).'];
    } else {
        $extra = [
            'days'             => isset($_POST['vip_days']) ? (int)$_POST['vip_days'] : 30,
            'telegram_user_id' => isset($_POST['telegram_user_id']) ? (int)$_POST['telegram_user_id'] : 0,
            'first_name'       => trim($_POST['first_name'] ?? ''),
            'username'         => trim($_POST['username'] ?? ''),
            'subscription'     => trim($_POST['subscription'] ?? 'regular'),
        ];
        $actionResult = manageUserAction($action, $userId, $extra);
    }
}

$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$pageNumber = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$perPage = 15;

// Hitung total data sesuai filter & pencarian untuk pagination
$totalFilteredUsers = getTelegramUsersTotal($search, $filter);
$totalPages = max(1, (int)ceil($totalFilteredUsers / $perPage));
if ($pageNumber > $totalPages) {
    $pageNumber = $totalPages;
}
$offset = ($pageNumber - 1) * $perPage;

// Ambil data pelanggan riil dari database sesuai halaman & offset
$usersList = getTelegramUsersList($search, $filter, $perPage, $offset);
$counts = getUserTabCounts();

// Helper untuk menyusun URL pagination yang menjaga parameter filter & search
$buildPageUrl = function($targetPage) use ($filter, $search) {
    $params = ['page' => 'users'];
    if ($filter !== 'all') {
        $params['filter'] = $filter;
    }
    if (!empty($search)) {
        $params['search'] = $search;
    }
    if ($targetPage > 1) {
        $params['p'] = $targetPage;
    }
    return 'index.php?' . http_build_query($params);
};
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

    <!-- Header Section with Stats Summary (100% Real Database Data) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-xl flex items-center justify-between border border-white/5">
            <div>
                <p class="text-xs text-slate-400">Total Terdata</p>
                <p class="text-xl font-bold text-white"><?= number_format($counts['all']); ?></p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center">
                <i class="fas fa-users text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex items-center justify-between border border-white/5">
            <div>
                <p class="text-xs text-slate-400">Pelanggan VIP</p>
                <p class="text-xl font-bold text-amber-300"><?= number_format($counts['vip']); ?></p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i class="fas fa-crown text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex items-center justify-between border border-white/5">
            <div>
                <p class="text-xs text-slate-400">Pelanggan Reguler</p>
                <p class="text-xl font-bold text-slate-300"><?= number_format($counts['regular']); ?></p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-500/10 text-slate-400 flex items-center justify-center">
                <i class="fas fa-user text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex items-center justify-between border border-white/5">
            <div>
                <p class="text-xs text-slate-400">Mitra Affiliate</p>
                <p class="text-xl font-bold text-[#00D0B6]"><?= number_format($counts['affiliate']); ?></p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-[#00D0B6]/10 text-[#00D0B6] flex items-center justify-center">
                <i class="fas fa-share-nodes text-sm"></i>
            </div>
        </div>
    </div>

    <!-- Main Container Card -->
    <div class="glass-panel rounded-2xl overflow-hidden border border-white/5">
        
        <!-- Toolbar: Search Bar, Filter Tabs, Export -->
        <div class="p-6 border-b border-white/5 space-y-4">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Search Box (Supports both Instant JS Filter & Server-side Submit) -->
                <form method="GET" action="index.php" class="relative flex-1 max-w-md">
                    <input type="hidden" name="page" value="users">
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter); ?>">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                    <input type="text" name="search" id="userSearchInput" value="<?= htmlspecialchars($search); ?>" placeholder="Cari Telegram ID, Username, atau Nama..." class="w-full pl-11 <?= !empty($search) ? 'pr-10' : 'pr-4'; ?> py-2.5 rounded-xl bg-slate-900/80 border border-white/10 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-[#00D0B6] transition">
                    <?php if (!empty($search)): ?>
                    <a href="index.php?page=users&filter=<?= urlencode($filter); ?>" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition" title="Hapus Pencarian">
                        <i class="fas fa-times-circle"></i>
                    </a>
                    <?php endif; ?>
                </form>

                <!-- Action Button -->
                <div class="flex items-center gap-3">
                    <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition border border-white/5 flex items-center gap-2">
                        <i class="fas fa-print"></i>
                        <span>Cetak Laporan</span>
                    </button>
                    <?php if (isSuperAdmin()): ?>
                    <button onclick="openModal('addUserModal')" class="px-4 py-2.5 rounded-xl bg-[#00D0B6] hover:bg-[#00D0B6]/90 text-slate-950 text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-[#00D0B6]/20">
                        <i class="fas fa-user-plus"></i>
                        <span>Input User Manual</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab Filters with Dynamic Counters -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 hide-scroll text-xs">
                <a href="index.php?page=users&filter=all<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'all') ? 'bg-[#00D0B6] text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Semua (<?= $counts['all']; ?>)
                </a>
                <a href="index.php?page=users&filter=vip<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'vip') ? 'bg-amber-400 text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-crown mr-1"></i> VIP Aktif (<?= $counts['vip']; ?>)
                </a>
                <a href="index.php?page=users&filter=regular<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'regular') ? 'bg-blue-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Pelanggan Reguler (<?= $counts['regular']; ?>)
                </a>
                <a href="index.php?page=users&filter=affiliate<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'affiliate') ? 'bg-[#00D0B6] text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-share-nodes mr-1"></i> Affiliate (<?= $counts['affiliate']; ?>)
                </a>
                <a href="index.php?page=users&filter=banned<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'banned') ? 'bg-rose-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-ban mr-1"></i> Banned (<?= $counts['banned']; ?>)
                </a>
            </div>

        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm custom-table">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Telegram ID</th>
                        <th>Status Pelanggan</th>
                        <th>Masa Aktif VIP</th>
                        <th>Koin Affiliate</th>
                        <th>Terdaftar Sejak</th>
                        <th>Terakhir Aktif</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="userTableBody" class="divide-y divide-white/5">
                    <?php if (empty($usersList)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-500">
                            <i class="fas fa-user-slash text-3xl mb-3 block opacity-50"></i>
                            Tidak ada data pelanggan yang sesuai dengan pencarian / filter.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($usersList as $u): 
                            $isVip = !empty($u['vip_until']) && strtotime($u['vip_until']) > time();
                            $fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                            if (empty($fullName)) $fullName = 'Pengguna Bot';
                            $userJson = htmlspecialchars(json_encode([
                                'id'               => $u['id'],
                                'telegram_user_id' => $u['telegram_user_id'],
                                'username'         => $u['username'] ?? '',
                                'first_name'       => $u['first_name'] ?? '',
                                'last_name'        => $u['last_name'] ?? '',
                                'is_vip'           => $isVip,
                                'vip_until'        => !empty($u['vip_until']) ? date('d M Y, H:i', strtotime($u['vip_until'])) : null,
                                'is_banned'        => (int)($u['is_banned'] ?? 0),
                                'coin_balance'     => (int)($u['coin_balance'] ?? 0),
                                'referral_code'    => $u['referral_code'] ?? '-',
                                'created_at'       => date('d M Y H:i', strtotime($u['created_at'])),
                                'last_active_at'   => date('d M Y H:i', strtotime($u['last_active_at']))
                            ]), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr class="hover:bg-white/[0.02] transition">
                            <!-- Pelanggan Info -->
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl <?= $u['is_banned'] ? 'bg-rose-500/20 text-rose-400 font-bold border border-rose-500/30' : ($isVip ? 'bg-gradient-to-tr from-amber-500 to-amber-300 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-800 text-slate-300 font-bold'); ?> flex items-center justify-center text-xs">
                                        <?= strtoupper(substr($fullName, 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold text-white"><?= htmlspecialchars($fullName); ?></p>
                                            <?php if ($isVip): ?>
                                                <i class="fas fa-crown text-amber-400 text-xs" title="Pelanggan VIP"></i>
                                            <?php endif; ?>
                                            <?php if ($u['is_banned']): ?>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] bg-rose-500/20 text-rose-400 font-bold border border-rose-500/30">Banned</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-slate-400"><?= !empty($u['username']) ? '@' . htmlspecialchars($u['username']) : '<span class="text-slate-600 italic">tanpa username</span>'; ?></p>
                                    </div>
                                </div>
                            </td>

                            <!-- Telegram ID -->
                            <td class="font-mono text-xs text-slate-300">
                                <span class="bg-slate-800/80 px-2 py-1 rounded-md border border-white/5 font-semibold">
                                    <?= htmlspecialchars($u['telegram_user_id']); ?>
                                </span>
                            </td>

                            <!-- Status VIP Badge -->
                            <td>
                                <?php if ($u['is_banned']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <i class="fas fa-ban mr-1 text-[10px]"></i> Banned
                                    </span>
                                <?php elseif ($isVip): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/20">
                                        <i class="fas fa-crown mr-1 text-[10px]"></i> VIP Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                        <i class="fas fa-user mr-1 text-[10px]"></i> Reguler Free
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Masa Aktif VIP -->
                            <td class="text-xs">
                                <?php if ($isVip): ?>
                                    <span class="text-amber-400 font-medium"><?= date('d M Y, H:i', strtotime($u['vip_until'])); ?></span>
                                    <p class="text-[10px] text-slate-400">Sisa <?= ceil((strtotime($u['vip_until']) - time()) / 86400); ?> hari lagi</p>
                                <?php else: ?>
                                    <span class="text-slate-500">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- Koin Affiliate -->
                            <td class="text-xs">
                                <?php if ($u['is_affiliate'] || ($u['coin_balance'] ?? 0) > 0): ?>
                                    <div class="flex items-center gap-1.5 font-semibold text-[#00D0B6]">
                                        <i class="fas fa-coins text-amber-400"></i>
                                        <span><?= number_format($u['coin_balance'] ?? 0); ?> Koin</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400">Kode: <span class="font-mono text-slate-300"><?= htmlspecialchars($u['referral_code'] ?? '-'); ?></span></p>
                                <?php else: ?>
                                    <span class="text-slate-500">Non-Affiliate</span>
                                <?php endif; ?>
                            </td>

                            <!-- Terdaftar Sejak -->
                            <td class="text-xs text-slate-400">
                                <?= date('d M Y', strtotime($u['created_at'])); ?>
                            </td>

                            <!-- Terakhir Aktif -->
                            <td class="text-xs text-slate-400">
                                <?= timeAgo($u['last_active_at']); ?>
                            </td>

                            <!-- Aksi Menu Riil (Database Mutation) -->
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- 1. Lihat Detail (Semua Role) -->
                                    <button onclick="openUserDetailModal(<?= $userJson; ?>)" class="p-2 rounded-lg bg-slate-800 hover:bg-[#00D0B6]/20 hover:text-[#00D0B6] text-slate-400 transition" title="Lihat Profil Lengkap">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>

                                    <?php if (isSuperAdmin()): ?>
                                    <!-- 2. Tambah VIP Manual (Super Admin Only) -->
                                    <button onclick="openAddVipModal(<?= $u['id']; ?>, '<?= htmlspecialchars(addslashes($fullName)); ?>')" class="p-2 rounded-lg bg-slate-800 hover:bg-amber-500/20 hover:text-amber-400 text-slate-400 transition" title="Tambah / Perpanjang Masa VIP">
                                        <i class="fas fa-crown text-xs"></i>
                                    </button>
                                    <!-- 3. Toggle Ban / Unban (Super Admin Only) -->
                                    <button onclick="openToggleBanModal(<?= $u['id']; ?>, '<?= htmlspecialchars(addslashes($fullName)); ?>', <?= $u['is_banned']; ?>)" class="p-2 rounded-lg bg-slate-800 <?= $u['is_banned'] ? 'hover:bg-emerald-500/20 hover:text-emerald-400 text-rose-400' : 'hover:bg-rose-500/20 hover:text-rose-400 text-slate-400'; ?> transition" title="<?= $u['is_banned'] ? 'Buka Banned (Unban)' : 'Banned Pengguna'; ?>">
                                        <i class="fas <?= $u['is_banned'] ? 'fa-unlock' : 'fa-ban'; ?> text-xs"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination / Summary Footnote -->
        <?php
        $fromItem = $totalFilteredUsers > 0 ? ($offset + 1) : 0;
        $toItem = min($offset + count($usersList), $totalFilteredUsers);
        ?>
        <div class="p-4 border-t border-white/5 bg-slate-900/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <div>
                Menampilkan <strong class="text-white"><?= $fromItem; ?> - <?= $toItem; ?></strong> dari <strong class="text-white"><?= number_format($totalFilteredUsers); ?></strong> pelanggan
                <?php if (!empty($search) || $filter !== 'all'): ?>
                    <span class="text-slate-500">(dari total <?= number_format($counts['all']); ?> terdaftar)</span>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-1.5 flex-wrap justify-center">
                <!-- Tombol Sebelumnya -->
                <?php if ($pageNumber > 1): ?>
                <a href="<?= htmlspecialchars($buildPageUrl($pageNumber - 1)); ?>" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition flex items-center gap-1.5 font-medium border border-white/5">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                    <span>Sebelumnya</span>
                </a>
                <?php else: ?>
                <span class="px-3 py-1.5 rounded-lg bg-slate-800/40 text-slate-600 cursor-not-allowed flex items-center gap-1.5 font-medium border border-white/5 select-none">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                    <span>Sebelumnya</span>
                </span>
                <?php endif; ?>

                <!-- Tombol Angka Halaman -->
                <?php
                $startPage = max(1, $pageNumber - 2);
                $endPage = min($totalPages, $pageNumber + 2);

                if ($startPage > 1): ?>
                    <a href="<?= htmlspecialchars($buildPageUrl(1)); ?>" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition flex items-center justify-center font-medium border border-white/5">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="px-1 text-slate-600 select-none">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                    <?php if ($p == $pageNumber): ?>
                        <span class="w-8 h-8 rounded-lg bg-[#00D0B6] text-slate-950 font-bold flex items-center justify-center shadow-md shadow-[#00D0B6]/20 select-none"><?= $p; ?></span>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($buildPageUrl($p)); ?>" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition flex items-center justify-center font-medium border border-white/5"><?= $p; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="px-1 text-slate-600 select-none">...</span>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars($buildPageUrl($totalPages)); ?>" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition flex items-center justify-center font-medium border border-white/5"><?= $totalPages; ?></a>
                <?php endif; ?>

                <!-- Tombol Berikutnya -->
                <?php if ($pageNumber < $totalPages): ?>
                <a href="<?= htmlspecialchars($buildPageUrl($pageNumber + 1)); ?>" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition flex items-center gap-1.5 font-medium border border-white/5">
                    <span>Berikutnya</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
                <?php else: ?>
                <span class="px-3 py-1.5 rounded-lg bg-slate-800/40 text-slate-600 cursor-not-allowed flex items-center gap-1.5 font-medium border border-white/5 select-none">
                    <span>Berikutnya</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </span>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<!-- Modal 1: Input Pelanggan Manual (POST Form) -->
<div id="addUserModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('addUserModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-[#00D0B6]/10 text-[#00D0B6] flex items-center justify-center">
                <i class="fas fa-user-plus"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base">Input Pelanggan Manual</h3>
                <p class="text-xs text-slate-400">Daftarkan atau perbarui data pelanggan bot</p>
            </div>
        </div>
        <form method="POST" action="" class="space-y-4 text-xs">
            <input type="hidden" name="user_action" value="add_user">
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Telegram User ID *</label>
                <input type="number" name="telegram_user_id" required placeholder="Contoh: 129384758" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Nama Depan *</label>
                    <input type="text" name="first_name" required placeholder="Andi" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                </div>
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Username Telegram</label>
                    <input type="text" name="username" placeholder="andipratama" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Status Langganan Awal</label>
                <select name="subscription" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                    <option value="regular">Reguler (Gratis)</option>
                    <option value="vip_7">Langsung VIP 7 Hari</option>
                    <option value="vip_30">Langsung VIP 30 Hari</option>
                    <option value="vip_90">Langsung VIP 90 Hari</option>
                </select>
            </div>
            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('addUserModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#00D0B6] text-slate-950 font-bold hover:bg-[#00D0B6]/90 shadow-lg shadow-[#00D0B6]/20">Simpan ke Database</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Detail Profil Pelanggan -->
<div id="userDetailModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-lg w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('userDetailModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="flex items-center gap-4 mb-6">
            <div id="detailAvatar" class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-[#00D0B6] to-teal-400 text-slate-950 font-black text-xl flex items-center justify-center shadow-lg shadow-[#00D0B6]/20">
                U
            </div>
            <div>
                <h3 id="detailName" class="font-bold text-white text-lg">-</h3>
                <p id="detailUsername" class="text-xs text-slate-400 font-mono">@username</p>
                <span id="detailStatus" class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">Status</span>
            </div>
        </div>

        <div class="space-y-3 bg-slate-900/60 p-4 rounded-xl border border-white/5 text-xs">
            <div class="flex justify-between py-1 border-b border-white/5">
                <span class="text-slate-400">Telegram User ID:</span>
                <span id="detailTgId" class="font-mono text-white font-semibold">-</span>
            </div>
            <div class="flex justify-between py-1 border-b border-white/5">
                <span class="text-slate-400">Masa Aktif VIP:</span>
                <span id="detailVip" class="text-amber-400 font-semibold">-</span>
            </div>
            <div class="flex justify-between py-1 border-b border-white/5">
                <span class="text-slate-400">Saldo Koin Affiliate:</span>
                <span id="detailCoins" class="text-[#00D0B6] font-semibold">-</span>
            </div>
            <div class="flex justify-between py-1 border-b border-white/5">
                <span class="text-slate-400">Kode Referral:</span>
                <span id="detailRefCode" class="font-mono text-white">-</span>
            </div>
            <div class="flex justify-between py-1 border-b border-white/5">
                <span class="text-slate-400">Waktu Terdaftar:</span>
                <span id="detailCreatedAt" class="text-slate-300">-</span>
            </div>
            <div class="flex justify-between py-1">
                <span class="text-slate-400">Terakhir Aktif:</span>
                <span id="detailLastActive" class="text-slate-300">-</span>
            </div>
        </div>

        <div class="pt-5 flex justify-end">
            <button type="button" onclick="closeModal('userDetailModal')" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-200 hover:text-white font-semibold text-xs">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal 3: Tambah / Perpanjang VIP Manual (POST Form) -->
<div id="addVipModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('addVipModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i class="fas fa-crown"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base">Tambah Masa VIP</h3>
                <p class="text-xs text-slate-400">Perpanjang masa aktif akun VIP pengguna</p>
            </div>
        </div>

        <div class="p-3 mb-4 bg-amber-500/10 border border-amber-500/20 rounded-xl text-xs text-amber-300">
            Pengguna target: <strong id="addVipUserName" class="text-white">-</strong>
        </div>

        <form method="POST" action="" class="space-y-4 text-xs">
            <input type="hidden" name="user_action" value="add_vip">
            <input type="hidden" name="user_id" id="addVipUserId" value="">
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Durasi Tambahan</label>
                <select name="vip_days" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-amber-400">
                    <option value="7">+7 Hari (Trial)</option>
                    <option value="15">+15 Hari</option>
                    <option value="30" selected>+30 Hari (1 Bulan)</option>
                    <option value="90">+90 Hari (3 Bulan)</option>
                    <option value="365">+365 Hari (1 Tahun)</option>
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Jika pengguna sudah memiliki VIP aktif, durasi akan ditambahkan secara akumulatif.</p>
            </div>
            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('addVipModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold shadow-lg shadow-amber-500/20">Perpanjang VIP</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Konfirmasi Toggle Banned (POST Form) -->
<div id="toggleBanModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('toggleBanModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <h3 id="banModalTitle" class="font-bold text-white text-base">Konfirmasi Aksi Banned</h3>
                <p class="text-xs text-slate-400">Kelola izin akses akun Telegram</p>
            </div>
        </div>

        <p id="banModalDesc" class="text-xs text-slate-300 mb-4 leading-relaxed">
            Apakah Anda yakin ingin memperbarui status izin akun ini?
        </p>

        <div class="p-3 mb-4 bg-slate-900 rounded-xl border border-white/5 text-xs">
            Pengguna target: <strong id="banUserName" class="text-white">-</strong>
        </div>

        <form method="POST" action="" class="flex items-center justify-end gap-3 text-xs">
            <input type="hidden" name="user_action" value="toggle_ban">
            <input type="hidden" name="user_id" id="banUserId" value="">
            <button type="button" onclick="closeModal('toggleBanModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold">Batal</button>
            <button type="submit" id="banSubmitBtn" class="px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold shadow-lg shadow-rose-500/20">Konfirmasi</button>
        </form>
    </div>
</div>

<!-- Interactive Modal Scripts -->
<script>
function openUserDetailModal(user) {
    document.getElementById('detailAvatar').textContent = (user.first_name || 'U').charAt(0).toUpperCase();
    document.getElementById('detailName').textContent = ((user.first_name || '') + ' ' + (user.last_name || '')).trim() || 'Pengguna Bot';
    document.getElementById('detailUsername').textContent = user.username ? '@' + user.username : 'Tidak ada username';
    document.getElementById('detailTgId').textContent = user.telegram_user_id || '-';
    document.getElementById('detailVip').textContent = user.vip_until ? user.vip_until : 'Bukan VIP (Gratis)';
    document.getElementById('detailCoins').textContent = (user.coin_balance || 0).toLocaleString('id-ID') + ' Koin';
    document.getElementById('detailRefCode').textContent = user.referral_code || '-';
    document.getElementById('detailCreatedAt').textContent = user.created_at || '-';
    document.getElementById('detailLastActive').textContent = user.last_active_at || '-';
    
    const statusEl = document.getElementById('detailStatus');
    if (user.is_banned == 1) {
        statusEl.className = 'inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30';
        statusEl.textContent = 'Banned';
    } else if (user.is_vip) {
        statusEl.className = 'inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30';
        statusEl.textContent = 'VIP Aktif';
    } else {
        statusEl.className = 'inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-white/5';
        statusEl.textContent = 'Reguler Free';
    }
    openModal('userDetailModal');
}

function openAddVipModal(userId, name) {
    document.getElementById('addVipUserId').value = userId;
    document.getElementById('addVipUserName').textContent = name;
    openModal('addVipModal');
}

function openToggleBanModal(userId, name, isBanned) {
    document.getElementById('banUserId').value = userId;
    document.getElementById('banUserName').textContent = name;
    const isB = (isBanned == 1 || isBanned === true);
    
    document.getElementById('banModalTitle').textContent = isB ? 'Konfirmasi Buka Blokir (Unban)' : 'Konfirmasi Banned Pengguna';
    document.getElementById('banModalDesc').textContent = isB 
        ? 'Apakah Anda yakin ingin membatalkan blokir akun ini? Pengguna akan dapat kembali berinteraksi dengan bot Telegram.' 
        : 'Pengguna yang di-banned tidak akan dapat mengakses konten streaming atau berinteraksi dengan bot Telegram.';
    
    const btn = document.getElementById('banSubmitBtn');
    if (isB) {
        btn.textContent = 'Buka Blokir (Unban)';
        btn.className = 'px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold shadow-lg shadow-emerald-500/20';
    } else {
        btn.textContent = 'Banned Pengguna';
        btn.className = 'px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold shadow-lg shadow-rose-500/20';
    }
    openModal('toggleBanModal');
}

// Inisialisasi filter tabel real-time lokal jika script dimuat
document.addEventListener('DOMContentLoaded', function() {
    if (typeof setupTableSearch === 'function') {
        setupTableSearch('userSearchInput', 'userTableBody');
    }
});
</script>
