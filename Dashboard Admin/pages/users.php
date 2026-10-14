<?php
// Halaman Pendataan Pelanggan Masuk (User Management)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$usersList = getTelegramUsersList($search, $filter);
$totalCount = count($usersList);

// Hitung cepat untuk badge tab
$allUsersRaw = getTelegramUsersList();
$vipCount = count(array_filter($allUsersRaw, fn($u) => !empty($u['vip_until']) && strtotime($u['vip_until']) > time()));
$freeCount = count(array_filter($allUsersRaw, fn($u) => empty($u['vip_until']) || strtotime($u['vip_until']) <= time()));
$affiliateCount = count(array_filter($allUsersRaw, fn($u) => $u['is_affiliate'] == 1));
$bannedCount = count(array_filter($allUsersRaw, fn($u) => $u['is_banned'] == 1));
?>

<div class="space-y-6">

    <!-- Header Section with Stats Summary -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-xl flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400">Total Terdata</p>
                <p class="text-xl font-bold text-white"><?= count($allUsersRaw); ?></p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center">
                <i class="fas fa-users text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400">Pelanggan VIP</p>
                <p class="text-xl font-bold text-amber-300"><?= $vipCount; ?></p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i class="fas fa-crown text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400">Pelanggan Free</p>
                <p class="text-xl font-bold text-slate-300"><?= $freeCount; ?></p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-slate-500/10 text-slate-400 flex items-center justify-center">
                <i class="fas fa-user text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400">Mitra Affiliate</p>
                <p class="text-xl font-bold text-[#00D0B6]"><?= $affiliateCount; ?></p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-[#00D0B6]/10 text-[#00D0B6] flex items-center justify-center">
                <i class="fas fa-share-nodes text-sm"></i>
            </div>
        </div>
    </div>

    <!-- Main Container Card -->
    <div class="glass-panel rounded-2xl overflow-hidden">
        
        <!-- Toolbar: Search Bar, Filter Tabs, Export -->
        <div class="p-6 border-b border-white/5 space-y-4">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Search Box -->
                <div class="relative flex-1 max-w-md">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                    <input type="text" id="userSearchInput" value="<?= htmlspecialchars($search); ?>" placeholder="Cari Telegram ID, Username, atau Nama..." class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-slate-900/80 border border-white/10 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-[#00D0B6] transition">
                </div>

                <!-- Action Button -->
                <div class="flex items-center gap-3">
                    <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition border border-white/5 flex items-center gap-2">
                        <i class="fas fa-print"></i>
                        <span>Cetak Laporan</span>
                    </button>
                    <button onclick="openModal('addUserModal')" class="px-4 py-2.5 rounded-xl bg-[#00D0B6] hover:bg-[#00D0B6]/90 text-slate-950 text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-[#00D0B6]/20">
                        <i class="fas fa-plus"></i>
                        <span>Input User Manual</span>
                    </button>
                </div>
            </div>

            <!-- Tab Filters -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 hide-scroll text-xs">
                <a href="index.php?page=users&filter=all" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'all') ? 'bg-[#00D0B6] text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Semua (<?= count($allUsersRaw); ?>)
                </a>
                <a href="index.php?page=users&filter=vip" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'vip') ? 'bg-amber-400 text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    <i class="fas fa-crown mr-1"></i> VIP Aktif (<?= $vipCount; ?>)
                </a>
                <a href="index.php?page=users&filter=regular" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'regular') ? 'bg-blue-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Pelanggan Reguler (<?= $freeCount; ?>)
                </a>
                <a href="index.php?page=users&filter=affiliate" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'affiliate') ? 'bg-[#00D0B6] text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Affiliate (<?= $affiliateCount; ?>)
                </a>
                <a href="index.php?page=users&filter=banned" class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition <?= ($filter === 'banned') ? 'bg-rose-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Banned (<?= $bannedCount; ?>)
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
                            <i class="fas fa-user-slash text-3xl mb-3 block"></i>
                            Tidak ada data pelanggan yang sesuai dengan pencarian / filter.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($usersList as $u): 
                            $isVip = !empty($u['vip_until']) && strtotime($u['vip_until']) > time();
                            $fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                            if (empty($fullName)) $fullName = 'Pengguna Bot';
                        ?>
                        <tr class="hover:bg-white/[0.02] transition">
                            <!-- Pelanggan Info -->
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl <?= $isVip ? 'bg-gradient-to-tr from-amber-500 to-amber-300 text-slate-950 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-800 text-slate-300 font-bold'; ?> flex items-center justify-center text-xs">
                                        <?= strtoupper(substr($fullName, 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold text-white"><?= htmlspecialchars($fullName); ?></p>
                                            <?php if ($isVip): ?>
                                                <i class="fas fa-crown text-amber-400 text-xs" title="Pelanggan VIP"></i>
                                            <?php endif; ?>
                                            <?php if ($u['is_banned']): ?>
                                                <span class="px-1.5 py-0.2 rounded text-[10px] bg-rose-500/20 text-rose-400 font-bold">Banned</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-slate-400">@<?= htmlspecialchars($u['username'] ?? 'tanpa_username'); ?></p>
                                    </div>
                                </div>
                            </td>

                            <!-- Telegram ID -->
                            <td class="font-mono text-xs text-slate-300">
                                <span class="bg-slate-800/80 px-2 py-1 rounded-md border border-white/5">
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
                                <?php if ($u['is_affiliate']): ?>
                                    <div class="flex items-center gap-1.5 font-semibold text-[#00D0B6]">
                                        <i class="fas fa-coins text-amber-400"></i>
                                        <span><?= number_format($u['coin_balance'] ?? 0); ?> Koin</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400">Kode: <?= htmlspecialchars($u['referral_code'] ?? '-'); ?></p>
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

                            <!-- Aksi Menu -->
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick="showToast('Fitur edit user Telegram <?= $u['telegram_user_id']; ?> terbuka!')" class="p-2 rounded-lg bg-slate-800 hover:bg-[#00D0B6]/20 hover:text-[#00D0B6] text-slate-400 transition" title="Lihat Profil Lengkap">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                    <button onclick="showToast('Masa VIP berhasil diperpanjang 30 hari!', 'success')" class="p-2 rounded-lg bg-slate-800 hover:bg-amber-500/20 hover:text-amber-400 text-slate-400 transition" title="Tambah VIP Manual (30 Hari)">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                    <button onclick="showToast('Status banned user diperbarui', 'error')" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-500/20 hover:text-rose-400 text-slate-400 transition" title="Toggle Ban">
                                        <i class="fas fa-ban text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination / Summary Footnote -->
        <div class="p-4 border-t border-white/5 bg-slate-900/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <p>Menampilkan <?= count($usersList); ?> dari <?= count($allUsersRaw); ?> total pelanggan terdata di database.</p>
            <div class="flex items-center gap-1">
                <button class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white transition disabled:opacity-50" disabled>Sebelumnya</button>
                <button class="px-3 py-1.5 rounded-lg bg-[#00D0B6] text-slate-950 font-bold">1</button>
                <button class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white transition disabled:opacity-50" disabled>Berikutnya</button>
            </div>
        </div>

    </div>

</div>

<!-- Modal Input User Manual -->
<div id="addUserModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4">
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
        <form onsubmit="event.preventDefault(); showToast('Data pelanggan baru berhasil ditambahkan!', 'success'); closeModal('addUserModal');" class="space-y-4 text-xs">
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Telegram User ID *</label>
                <input type="number" required placeholder="Contoh: 129384758" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Nama Depan *</label>
                    <input type="text" required placeholder="Andi" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                </div>
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Username Telegram</label>
                    <input type="text" placeholder="andipratama" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Status Langganan Awal</label>
                <select class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                    <option value="regular">Reguler (Gratis)</option>
                    <option value="vip_7">Langsung VIP 7 Hari</option>
                    <option value="vip_30">Langsung VIP 30 Hari</option>
                    <option value="vip_90">Langsung VIP 90 Hari</option>
                </select>
            </div>
            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('addUserModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#00D0B6] text-slate-950 font-bold hover:bg-[#00D0B6]/90 shadow-lg shadow-[#00D0B6]/20">Simpan Pelanggan</button>
            </div>
        </form>
    </div>
</div>
