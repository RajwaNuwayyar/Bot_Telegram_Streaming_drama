<?php
// Halaman Manajemen Katalog Drama & Episode
$dramas = getDramasCatalog();
?>

<div class="space-y-6">

    <!-- Top Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-white font-heading">Katalog Drama & Episode</h2>
            <p class="text-xs text-slate-400">Kelola judul drama, poster, batas episode gratis, dan konten channel Telegram</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openModal('addDramaModal')" class="px-4 py-2.5 rounded-xl bg-[#00D0B6] hover:bg-[#00D0B6]/90 text-slate-950 text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-[#00D0B6]/20">
                <i class="fas fa-plus"></i>
                <span>Tambah Drama Baru</span>
            </button>
        </div>
    </div>

    <!-- Drama Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($dramas as $drama): ?>
        <div class="glass-card rounded-2xl overflow-hidden flex flex-col justify-between group">
            
            <!-- Poster Image Container -->
            <div class="relative h-56 overflow-hidden bg-slate-900">
                <img src="<?= htmlspecialchars($drama['poster_url']); ?>" alt="<?= htmlspecialchars($drama['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                <div class="absolute inset-0 bg-gradient-to-t from-[#111827] via-transparent to-black/40"></div>
                
                <!-- Badges on Poster -->
                <div class="absolute top-3 left-3 flex flex-wrap gap-1">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-black/60 backdrop-blur-md text-[#00D0B6] border border-[#00D0B6]/30 uppercase">
                        <?= $drama['status']; ?>
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/80 backdrop-blur-md text-slate-950">
                        <?= $drama['free_episodes_count']; ?> Ep Gratis
                    </span>
                </div>

                <div class="absolute bottom-3 left-3 right-3">
                    <span class="text-[11px] text-slate-300 font-medium"><?= htmlspecialchars($drama['country_origin'] ?? 'Korea'); ?> • <?= $drama['release_year'] ?? '2024'; ?></span>
                    <h3 class="text-base font-bold text-white truncate"><?= htmlspecialchars($drama['title']); ?></h3>
                </div>
            </div>

            <!-- Card Details -->
            <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                <p class="text-xs text-slate-400 line-clamp-2"><?= htmlspecialchars($drama['description']); ?></p>
                
                <div class="pt-3 border-t border-white/5 flex items-center justify-between text-xs text-slate-400">
                    <span class="flex items-center gap-1.5 font-semibold text-slate-200">
                        <i class="fas fa-layer-group text-[#00D0B6]"></i>
                        <?= $drama['total_episodes']; ?> Episode
                    </span>
                    <span class="flex items-center gap-1.5">
                        <i class="fas fa-eye text-sky-400"></i>
                        <?= number_format($drama['views_count']); ?> views
                    </span>
                </div>

                <!-- Action Button -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <button onclick="openModal('addEpisodeModal')" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition border border-white/5 flex items-center justify-center gap-1.5">
                        <i class="fas fa-video text-[11px] text-[#00D0B6]"></i>
                        <span>Episode</span>
                    </button>
                    <button onclick="showToast('Edit drama <?= htmlspecialchars($drama['title']); ?>', 'info')" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition border border-white/5 flex items-center justify-center gap-1.5">
                        <i class="fas fa-edit text-[11px]"></i>
                        <span>Edit</span>
                    </button>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- Modal Tambah Drama Baru -->
<div id="addDramaModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4">
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
                <p class="text-xs text-slate-400">Data akan tersimpan ke database tabel dramas</p>
            </div>
        </div>
        <form onsubmit="event.preventDefault(); showToast('Drama baru berhasil disimpan!', 'success'); closeModal('addDramaModal');" class="space-y-4 text-xs">
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Judul Drama *</label>
                <input type="text" required placeholder="Contoh: Queen of Tears" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Negara Asal</label>
                    <input type="text" placeholder="Korea Selatan" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                </div>
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Tahun Rilis</label>
                    <input type="number" value="2024" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Poster URL (Gambar Poster)</label>
                <input type="url" placeholder="https://..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Total Episode</label>
                    <input type="number" value="16" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                </div>
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Episode Gratis (Free)</label>
                    <input type="number" value="1" title="1 = ep 1 gratis, selebihnya butuh VIP" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Sinopsis / Deskripsi</label>
                <textarea rows="3" placeholder="Sinopsis drama singkat..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]"></textarea>
            </div>
            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('addDramaModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#00D0B6] text-slate-950 font-bold hover:bg-[#00D0B6]/90 shadow-lg shadow-[#00D0B6]/20">Simpan Drama</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Episode (Telegram Message Link) -->
<div id="addEpisodeModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('addEpisodeModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                <i class="fab fa-telegram-plane"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base">Sinkronkan Episode Telegram</h3>
                <p class="text-xs text-slate-400">Hubungkan postingan video channel ke episode</p>
            </div>
        </div>
        <form onsubmit="event.preventDefault(); showToast('Episode baru berhasil disinkronkan ke Telegram!', 'success'); closeModal('addEpisodeModal');" class="space-y-4 text-xs">
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Nomor Episode</label>
                <input type="number" value="1" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Telegram Channel ID</label>
                <input type="text" placeholder="-100192837465" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">Telegram Message ID</label>
                <input type="number" placeholder="42" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
            </div>
            <div>
                <label class="block text-slate-300 font-semibold mb-1">File ID (Opsional dari Bot)</label>
                <input type="text" placeholder="BAACAgIAAxkBAAE..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6]">
            </div>
            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('addEpisodeModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold">Tutup</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-sky-500 text-white font-bold hover:bg-sky-400">Simpan Episode</button>
            </div>
        </form>
    </div>
</div>
