<?php
// Halaman Manajemen Katalog Drama & Episode Riil
$actionResult = null;

// Tangani aksi manajemen drama & episode (POST)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['drama_action'])) {
    $action = trim($_POST['drama_action']);
    $actionResult = manageDramaAction($action, $_POST);
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';

$dramas = getDramasCatalog($search, $filter);
$stats = getDramaStats();
$categories = getDramaCategoriesList();

// Ambil seluruh data episode per drama untuk modal instan
$allEpisodes = [];
if (!empty($dramas)) {
    foreach ($dramas as $d) {
        $allEpisodes[$d['id']] = getDramaEpisodes($d['id']);
    }
}
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

    <!-- Summary Stats Row (100% Real Database Data) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass-card p-4 rounded-xl flex items-center justify-between border border-white/5">
            <div>
                <p class="text-xs text-slate-400">Total Judul Drama</p>
                <p class="text-xl font-bold text-white"><?= number_format($stats['total_dramas']); ?> Judul</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-[#00D0B6]/10 text-[#00D0B6] flex items-center justify-center">
                <i class="fas fa-film text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex items-center justify-between border border-white/5">
            <div>
                <p class="text-xs text-slate-400">Episode Tersinkron</p>
                <p class="text-xl font-bold text-sky-400"><?= number_format($stats['total_episodes_uploaded']); ?> Video</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center">
                <i class="fas fa-video text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex items-center justify-between border border-white/5">
            <div>
                <p class="text-xs text-slate-400">Status Ongoing</p>
                <p class="text-xl font-bold text-emerald-400"><?= number_format($stats['ongoing_count']); ?> Judul</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i class="fas fa-play-circle text-sm"></i>
            </div>
        </div>

        <div class="glass-card p-4 rounded-xl flex items-center justify-between border border-white/5">
            <div>
                <p class="text-xs text-slate-400">Status Tamat</p>
                <p class="text-xl font-bold text-indigo-400"><?= number_format($stats['completed_count']); ?> Judul</p>
            </div>
            <div class="w-9 h-9 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                <i class="fas fa-check-double text-sm"></i>
            </div>
        </div>
    </div>

    <!-- Top Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Search & Filter Tab -->
        <div class="flex flex-wrap items-center gap-3 flex-1">
            <form method="GET" action="index.php" class="relative max-w-xs w-full">
                <input type="hidden" name="page" value="dramas">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter); ?>">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text" name="search" id="dramaSearchInput" value="<?= htmlspecialchars($search); ?>" placeholder="Cari judul atau negara..." class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-900 border border-white/10 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-[#00D0B6] transition">
            </form>

            <div class="flex items-center gap-1.5 text-xs">
                <a href="index.php?page=dramas&filter=all<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-1.5 rounded-lg font-medium transition <?= ($filter === 'all') ? 'bg-[#00D0B6] text-slate-950 font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Semua (<?= $stats['total_dramas']; ?>)
                </a>
                <a href="index.php?page=dramas&filter=ongoing<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-1.5 rounded-lg font-medium transition <?= ($filter === 'ongoing') ? 'bg-emerald-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Ongoing (<?= $stats['ongoing_count']; ?>)
                </a>
                <a href="index.php?page=dramas&filter=completed<?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="px-3 py-1.5 rounded-lg font-medium transition <?= ($filter === 'completed') ? 'bg-indigo-500 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'; ?>">
                    Tamat (<?= $stats['completed_count']; ?>)
                </a>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="openModal('addDramaModal')" class="px-4 py-2.5 rounded-xl bg-[#00D0B6] hover:bg-[#00D0B6]/90 text-slate-950 text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-[#00D0B6]/20">
                <i class="fas fa-plus"></i>
                <span>Tambah Drama Baru</span>
            </button>
        </div>
    </div>

    <!-- Drama Cards Grid -->
    <div id="dramaGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php if (empty($dramas)): ?>
        <div class="col-span-full py-16 text-center text-slate-500 glass-panel rounded-2xl">
            <i class="fas fa-film text-4xl mb-3 block opacity-40"></i>
            <p class="font-semibold text-white">Tidak ada judul drama ditemukan</p>
            <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau tambah drama baru.</p>
        </div>
        <?php else: ?>
            <?php foreach ($dramas as $drama): 
                $isUrl = preg_match('/^(http:\/\/|https:\/\/|\/)/i', $drama['poster_url'] ?? '');
                $epUploaded = (int)($drama['ep_count'] ?? 0);
                $totalTarget = max($epUploaded, (int)($drama['total_episodes'] ?? 0));
                $dramaJson = htmlspecialchars(json_encode($drama), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="glass-card rounded-2xl overflow-hidden flex flex-col justify-between group border border-white/5 hover:border-white/20 transition duration-300">
                
                <!-- Poster Image Container -->
                <div class="relative h-56 overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 flex items-center justify-center">
                    <?php if ($isUrl): ?>
                        <img src="<?= htmlspecialchars($drama['poster_url']); ?>" alt="<?= htmlspecialchars($drama['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <?php endif; ?>

                    <!-- Styled Fallback Poster Cover (Used for Telegram file_id or broken URLs) -->
                    <div class="<?= $isUrl ? 'hidden' : 'flex'; ?> w-full h-full flex-col items-center justify-center p-4 text-center bg-gradient-to-tr from-slate-900 via-[#131c31] to-slate-900 relative">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#00D0B6]/20 to-sky-500/20 text-[#00D0B6] border border-[#00D0B6]/30 flex items-center justify-center text-2xl shadow-lg mb-2">
                            <i class="fas fa-clapperboard"></i>
                        </div>
                        <h4 class="font-extrabold text-white text-sm line-clamp-2"><?= htmlspecialchars($drama['title']); ?></h4>
                        <?php if (!empty($drama['poster_url'])): ?>
                            <span class="mt-2 text-[9px] font-mono px-2 py-0.5 rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/20 flex items-center gap-1">
                                <i class="fab fa-telegram-plane"></i> Telegram Poster
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="absolute inset-0 bg-gradient-to-t from-[#111827] via-transparent to-black/40 pointer-events-none"></div>
                    
                    <!-- Badges on Poster -->
                    <div class="absolute top-3 left-3 flex flex-wrap gap-1">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-black/60 backdrop-blur-md <?= ($drama['status'] === 'completed') ? 'text-indigo-400 border border-indigo-500/30' : 'text-[#00D0B6] border border-[#00D0B6]/30'; ?> uppercase">
                            <?= htmlspecialchars($drama['status']); ?>
                        </span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/90 backdrop-blur-md text-slate-950">
                            Ep 1-<?= $drama['free_episodes_count']; ?> Free
                        </span>
                    </div>

                    <!-- Delete button shortcut -->
                    <button onclick="confirmDeleteDrama(<?= $drama['id']; ?>, '<?= htmlspecialchars(addslashes($drama['title'])); ?>')" class="absolute top-3 right-3 w-7 h-7 rounded-lg bg-black/50 hover:bg-rose-500 text-slate-400 hover:text-white flex items-center justify-center text-xs transition backdrop-blur-md" title="Hapus Drama">
                        <i class="fas fa-trash-alt"></i>
                    </button>

                    <div class="absolute bottom-3 left-3 right-3 pointer-events-none">
                        <span class="text-[11px] text-slate-300 font-medium"><?= htmlspecialchars($drama['country_origin'] ?? 'Asia'); ?> <?= !empty($drama['release_year']) ? '• ' . $drama['release_year'] : ''; ?></span>
                        <h3 class="text-base font-bold text-white truncate"><?= htmlspecialchars($drama['title']); ?></h3>
                    </div>
                </div>

                <!-- Card Details -->
                <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                    <p class="text-xs text-slate-400 line-clamp-2 min-h-[32px]"><?= !empty($drama['description']) ? htmlspecialchars($drama['description']) : '<span class="italic text-slate-600">Tidak ada sinopsis/deskripsi</span>'; ?></p>
                    
                    <div class="pt-3 border-t border-white/5 flex items-center justify-between text-xs text-slate-400">
                        <span class="flex items-center gap-1.5 font-semibold text-slate-200">
                            <i class="fas fa-layer-group text-[#00D0B6]"></i>
                            <span><?= $epUploaded; ?> Episode</span>
                            <?php if ($totalTarget > $epUploaded): ?>
                                <span class="text-[10px] text-slate-500 font-normal">/ <?= $totalTarget; ?> target</span>
                            <?php endif; ?>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-eye text-sky-400"></i>
                            <?= number_format($drama['views_count']); ?> views
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button onclick="openManageEpisodesModal(<?= $drama['id']; ?>, '<?= htmlspecialchars(addslashes($drama['title'])); ?>')" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition border border-white/5 flex items-center justify-center gap-1.5">
                            <i class="fas fa-video text-[11px] text-[#00D0B6]"></i>
                            <span>Episode (<?= $epUploaded; ?>)</span>
                        </button>
                        <button onclick="openAddEpisodeModal(<?= $drama['id']; ?>, '<?= htmlspecialchars(addslashes($drama['title'])); ?>', <?= $epUploaded + 1; ?>)" class="py-2 px-3 rounded-xl bg-[#00D0B6]/10 hover:bg-[#00D0B6]/20 text-[#00D0B6] text-xs font-bold transition border border-[#00D0B6]/30 flex items-center justify-center gap-1.5">
                            <i class="fas fa-plus text-[11px]"></i>
                            <span>+ Episode</span>
                        </button>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- Modal 1: Tambah Drama Baru (POST Form) -->
<div id="addDramaModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-lg w-full p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <button onclick="closeModal('addDramaModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-[#00D0B6]/10 text-[#00D0B6] flex items-center justify-center">
                <i class="fas fa-film"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base">Tambah Judul Drama Baru</h3>
                <p class="text-xs text-slate-400">Data akan tersimpan langsung ke database tabel dramas</p>
            </div>
        </div>

        <form method="POST" action="" class="space-y-4 text-xs">
            <input type="hidden" name="drama_action" value="add_drama">
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Judul Drama *</label>
                <input type="text" name="title" required placeholder="Contoh: Queen of Tears" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Negara Asal</label>
                    <input type="text" name="country_origin" placeholder="Korea Selatan" value="Korea Selatan" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                </div>
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Tahun Rilis</label>
                    <input type="number" name="release_year" value="<?= date('Y'); ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Poster URL / Telegram File ID</label>
                <input type="text" name="poster_url" placeholder="https://... atau Telegram file_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                <p class="text-[11px] text-slate-500 mt-1">Dapat berupa URL gambar (HTTPS) atau string Telegram file_id.</p>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Target Total Ep</label>
                    <input type="number" name="total_episodes" value="16" min="0" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                </div>
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Episode Gratis</label>
                    <input type="number" name="free_episodes_count" value="1" min="0" title="1 = ep 1 gratis, selebihnya VIP" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                </div>
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Status</label>
                    <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="hiatus">Hiatus</option>
                    </select>
                </div>
            </div>

            <!-- Pilih Genre / Kategori -->
            <?php if (!empty($categories)): ?>
            <div>
                <label class="block text-slate-300 font-semibold mb-1.5">Genre / Kategori Drama</label>
                <div class="grid grid-cols-3 gap-2 max-h-32 overflow-y-auto p-2 bg-slate-900 rounded-xl border border-white/5">
                    <?php foreach ($categories as $cat): ?>
                    <label class="flex items-center gap-2 cursor-pointer text-slate-300 hover:text-white">
                        <input type="checkbox" name="category_ids[]" value="<?= $cat['id']; ?>" class="rounded bg-slate-800 border-white/10 text-[#00D0B6] focus:ring-0">
                        <span><?= htmlspecialchars($cat['name']); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div>
                <label class="block text-slate-300 font-semibold mb-1">Sinopsis / Deskripsi Singkat</label>
                <textarea name="description" rows="3" placeholder="Sinopsis cerita drama..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]"></textarea>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('addDramaModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#00D0B6] text-slate-950 font-bold hover:bg-[#00D0B6]/90 shadow-lg shadow-[#00D0B6]/20">Simpan ke Database</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Tambah / Sinkronkan Episode Telegram (POST Form) -->
<div id="addEpisodeModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('addEpisodeModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                <i class="fab fa-telegram-plane"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base">Sinkronkan Episode Telegram</h3>
                <p class="text-xs text-slate-400">Hubungkan postingan channel video ke episode bot</p>
            </div>
        </div>

        <div class="p-3 mb-4 bg-slate-900 rounded-xl border border-white/5 text-xs">
            Drama: <strong id="addEpDramaTitle" class="text-white">-</strong>
        </div>

        <form method="POST" action="" class="space-y-4 text-xs">
            <input type="hidden" name="drama_action" value="add_episode">
            <input type="hidden" name="drama_id" id="addEpDramaId" value="">
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Nomor Episode *</label>
                    <input type="number" name="episode_number" id="addEpNumber" value="1" min="1" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-sky-400">
                </div>
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Judul Episode</label>
                    <input type="text" name="title" id="addEpTitle" placeholder="Episode 1" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-sky-400">
                </div>
            </div>

            <div>
                <label class="block text-slate-300 font-semibold mb-1">Telegram Channel ID *</label>
                <input type="text" name="telegram_channel_id" required placeholder="Contoh: -100192837465" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white font-mono focus:outline-none focus:border-sky-400">
                <p class="text-[11px] text-slate-500 mt-1">ID Channel privat penyimpanan video (diawali -100...)</p>
            </div>

            <div>
                <label class="block text-slate-300 font-semibold mb-1">Telegram Message ID *</label>
                <input type="number" name="telegram_message_id" required placeholder="Contoh: 42" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white font-mono focus:outline-none focus:border-sky-400">
            </div>

            <div>
                <label class="block text-slate-300 font-semibold mb-1">Telegram File ID (Opsional)</label>
                <input type="text" name="telegram_file_id" placeholder="BAACAgIAAxkBAAE..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white font-mono focus:outline-none focus:border-sky-400">
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('addEpisodeModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-sky-500 hover:bg-sky-400 text-white font-bold shadow-lg shadow-sky-500/20">Simpan Episode</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Kelola Daftar Episode Drama (Interactive Inspector) -->
<div id="manageEpisodesModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-2xl w-full p-6 shadow-2xl relative max-h-[85vh] flex flex-col">
        <button onclick="closeModal('manageEpisodesModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="flex items-center justify-between pb-4 border-b border-white/5">
            <div>
                <h3 class="font-bold text-white text-base">Daftar Episode Drama</h3>
                <p id="manageEpDramaTitle" class="text-xs text-[#00D0B6] font-semibold">-</p>
            </div>
            <button id="manageEpAddBtn" class="px-3 py-1.5 rounded-lg bg-[#00D0B6] hover:bg-[#00D0B6]/90 text-slate-950 text-xs font-bold transition flex items-center gap-1.5 shadow-md">
                <i class="fas fa-plus"></i>
                <span>+ Episode Baru</span>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto my-4 space-y-2" id="manageEpListContainer">
            <!-- Dynamic Episodes injected via JS -->
        </div>

        <div class="pt-3 border-t border-white/5 flex justify-end">
            <button type="button" onclick="closeModal('manageEpisodesModal')" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-200 hover:text-white font-semibold text-xs">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal 4: Konfirmasi Hapus Drama (POST Form) -->
<div id="deleteDramaModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('deleteDramaModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base">Hapus Judul Drama?</h3>
                <p class="text-xs text-slate-400">Tindakan ini tidak dapat dibatalkan</p>
            </div>
        </div>

        <p class="text-xs text-slate-300 mb-4 leading-relaxed">
            Apakah Anda yakin ingin menghapus drama <strong id="deleteDramaTitle" class="text-white">-</strong>? Seluruh data episode yang terhubung juga akan dihapus dari database.
        </p>

        <form method="POST" action="" class="flex items-center justify-end gap-3 text-xs">
            <input type="hidden" name="drama_action" value="delete_drama">
            <input type="hidden" name="drama_id" id="deleteDramaId" value="">
            <button type="button" onclick="closeModal('deleteDramaModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold">Batal</button>
            <button type="submit" class="px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold shadow-lg shadow-rose-500/20">Hapus Drama</button>
        </form>
    </div>
</div>

<!-- Embedded Episodes Data for Instant Interaction -->
<script>
window.dramaEpisodes = <?= json_encode($allEpisodes); ?>;

function openAddEpisodeModal(dramaId, dramaTitle, nextEpNum) {
    document.getElementById('addEpDramaId').value = dramaId;
    document.getElementById('addEpDramaTitle').textContent = dramaTitle;
    document.getElementById('addEpNumber').value = nextEpNum || 1;
    document.getElementById('addEpTitle').value = 'Episode ' + (nextEpNum || 1);
    openModal('addEpisodeModal');
}

function openManageEpisodesModal(dramaId, dramaTitle) {
    document.getElementById('manageEpDramaTitle').textContent = dramaTitle;
    const episodes = window.dramaEpisodes[dramaId] || [];
    const container = document.getElementById('manageEpListContainer');
    
    // Wire "+ Episode Baru" button inside modal
    const addBtn = document.getElementById('manageEpAddBtn');
    addBtn.onclick = function() {
        closeModal('manageEpisodesModal');
        openAddEpisodeModal(dramaId, dramaTitle, episodes.length + 1);
    };

    if (episodes.length === 0) {
        container.innerHTML = `
            <div class="py-10 text-center text-slate-500">
                <i class="fas fa-video-slash text-3xl mb-2 block opacity-40"></i>
                <p class="font-semibold text-white text-xs">Belum ada episode tersinkron</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Klik tombol '+ Episode Baru' untuk menautkan postingan Telegram.</p>
            </div>
        `;
    } else {
        let html = '';
        episodes.forEach(ep => {
            html += `
                <div class="p-3 bg-slate-900/80 rounded-xl border border-white/5 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-400 font-bold flex items-center justify-center text-xs">
                            ${ep.episode_number}
                        </div>
                        <div>
                            <p class="font-bold text-white">${ep.title || 'Episode ' + ep.episode_number}</p>
                            <p class="text-[10px] text-slate-400 font-mono">Channel: ${ep.telegram_channel_id} | Msg ID: ${ep.telegram_message_id}</p>
                        </div>
                    </div>
                    <form method="POST" action="" onsubmit="return confirm('Hapus episode #${ep.episode_number}?');">
                        <input type="hidden" name="drama_action" value="delete_episode">
                        <input type="hidden" name="episode_id" value="${ep.id}">
                        <button type="submit" class="p-1.5 rounded-lg bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition" title="Hapus Episode">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </form>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    openModal('manageEpisodesModal');
}

function confirmDeleteDrama(dramaId, dramaTitle) {
    document.getElementById('deleteDramaId').value = dramaId;
    document.getElementById('deleteDramaTitle').textContent = dramaTitle;
    openModal('deleteDramaModal');
}
</script>
