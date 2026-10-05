<?php
// Ambil user dan data riwayat tontonan dari database
$telegram_user_id = isset($_SESSION['telegram_user_id']) ? $_SESSION['telegram_user_id'] : null;
$user = null;
$user_id = 0;

if ($telegram_user_id) {
    $user = getUserByTelegramId($pdo, $telegram_user_id);
}

// Fallback untuk testing di browser biasa
if (!$user) {
    $user = $pdo->query("SELECT * FROM users ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
}

if ($user) {
    $user_id = $user['id'];
}

$historyItems = getWatchHistory($pdo, $user_id, 'all');
$bot_username = "TreadLessBot";
?>
<!-- History View -->
<div class="px-5 pt-6 pb-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-accentdark to-accent flex items-center justify-center shadow-lg shadow-accent/20">
                <i class="fa-solid fa-clock-rotate-left text-darkbg text-xs"></i>
            </div>
            <div>
                <h1 data-i18n="history_title" class="text-xl font-bold text-white leading-tight">Riwayat Tontonan</h1>
                <p class="text-[11px] text-textmuted">Lanjutkan tontonan terakhirmu</p>
            </div>
        </div>
        <?php if (!empty($historyItems)): ?>
            <button id="clear-all-btn" onclick="confirmClearAll()" data-i18n="clear_all" class="text-xs font-semibold text-accent hover:text-white uppercase tracking-wider transition-colors px-2 py-1 rounded-lg hover:bg-white/5">
                Hapus Semua
            </button>
        <?php endif; ?>
    </div>

    <!-- Filter Pills -->
    <div class="flex gap-2.5 mb-6">
        <button onclick="filterHistory('all', this)" data-i18n="filter_all" class="history-filter-btn px-4 py-2 rounded-full bg-accent text-darkbg font-semibold text-xs shadow-sm transition-all">
            Semua
        </button>
        <button onclick="filterHistory('progress', this)" data-i18n="filter_in_progress" class="history-filter-btn px-4 py-2 rounded-full bg-cardbg border border-white/5 text-textmuted text-xs hover:text-white transition-all">
            Sedang Ditonton
        </button>
        <button onclick="filterHistory('completed', this)" data-i18n="filter_completed" class="history-filter-btn px-4 py-2 rounded-full bg-cardbg border border-white/5 text-textmuted text-xs hover:text-white transition-all">
            Selesai
        </button>
    </div>

    <!-- History List -->
    <div class="flex flex-col gap-3.5" id="history-list">
        <?php if (!empty($historyItems)): ?>
            <?php foreach ($historyItems as $idx => $item): 
                $status = $item['is_completed'] ? 'completed' : 'progress';
                $poster = getPosterUrl($item['poster_url'], $idx);
                $duration = max(1, (int)$item['duration_seconds']);
                $progressSec = (int)$item['progress_seconds'];
                
                if ($item['is_completed']) {
                    $percent = 100;
                } else {
                    $percent = min(98, max(8, round(($progressSec / $duration) * 100)));
                }

                $timeAgo = timeAgoIndo($item['last_watched_at']);
                $watchLink = "https://t.me/{$bot_username}?start=watch_{$item['episode_id']}";
                $epLabel = !empty($item['episode_title']) ? $item['episode_title'] : ("Episode " . $item['episode_number']);
                $catName = !empty($item['category_name']) ? $item['category_name'] : 'Drama';
            ?>
                <div data-status="<?php echo $status; ?>" 
                     onclick="window.Telegram.WebApp.openTelegramLink('<?php echo $watchLink; ?>')"
                     class="history-item bg-cardbg border border-white/10 rounded-2xl p-3.5 flex gap-3.5 items-center cursor-pointer hover:border-accent/40 transition-all active:scale-[0.99] group shadow-sm">
                    
                    <!-- Thumbnail with play icon / completed icon -->
                    <div class="w-20 h-20 rounded-xl bg-darkbg border border-white/5 relative overflow-hidden shrink-0 shadow-inner">
                        <img src="<?php echo htmlspecialchars($poster); ?>" 
                             alt="<?php echo htmlspecialchars($item['drama_title']); ?>" 
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&q=80';" />

                        <div class="absolute inset-0 bg-darkbg/40 flex items-center justify-center group-hover:bg-darkbg/20 transition-colors">
                            <?php if ($item['is_completed']): ?>
                                <div class="w-7 h-7 rounded-full bg-accent/90 text-darkbg flex items-center justify-center shadow-md">
                                    <i class="fa-solid fa-check text-xs font-bold"></i>
                                </div>
                            <?php else: ?>
                                <div class="w-7 h-7 rounded-full bg-darkbg/80 text-accent border border-accent/40 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-play text-[10px] ml-0.5"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-bold text-sm truncate text-white group-hover:text-accent transition-colors leading-tight">
                                <?php echo htmlspecialchars($item['drama_title']); ?>
                            </h4>
                            <span class="text-[10px] text-textmuted shrink-0 ml-2">
                                <?php echo $timeAgo; ?>
                            </span>
                        </div>

                        <p class="text-xs text-textmuted truncate mb-2.5">
                            Ep <?php echo $item['episode_number']; ?> - "<?php echo htmlspecialchars($epLabel); ?>"
                        </p>

                        <!-- Progress Bar Row -->
                        <div class="flex items-center gap-3 mb-1.5">
                            <div class="flex-1 h-1.5 bg-darkbg rounded-full overflow-hidden border border-white/5">
                                <div class="h-full bg-gradient-to-r from-accentdark to-accent rounded-full transition-all duration-500" 
                                     style="width: <?php echo $percent; ?>%"></div>
                            </div>
                            <span class="text-[10px] font-medium <?php echo $item['is_completed'] ? 'text-accent' : 'text-textmuted'; ?> w-16 text-right shrink-0">
                                <?php echo $item['is_completed'] ? 'Selesai' : ($percent . '% tonton'); ?>
                            </span>
                        </div>

                        <!-- Genre and Action Tags -->
                        <div class="flex justify-between items-center pt-0.5">
                            <span class="px-2 py-0.5 rounded text-[8px] border border-white/10 text-textmuted uppercase tracking-wider font-semibold">
                                <?php echo htmlspecialchars($catName); ?>
                            </span>
                            <span class="text-[10px] text-accent flex items-center gap-1 font-medium group-hover:underline">
                                Nonton <i class="fa-solid fa-chevron-right text-[8px]"></i>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Empty State -->
    <div id="history-empty" class="<?php echo empty($historyItems) ? 'flex' : 'hidden'; ?> flex-col items-center justify-center py-16 text-center">
        <div class="w-16 h-16 rounded-full bg-cardbg border border-white/10 flex items-center justify-center mb-4 text-textmuted shadow-inner">
            <i class="fa-regular fa-clock text-2xl text-accent"></i>
        </div>
        <h3 class="text-base font-bold text-white mb-1.5">Belum Ada Riwayat Tontonan</h3>
        <p class="text-xs text-textmuted max-w-[260px] leading-relaxed mb-6">
            Mulai tonton drama favoritmu dan riwayat progres nonton akan otomatis tersimpan di sini!
        </p>
        <a href="?page=home" class="px-6 py-2.5 bg-accent hover:bg-accentdark text-darkbg font-bold text-xs rounded-xl shadow-lg shadow-accent/20 transition-all active:scale-95 flex items-center gap-2">
            <i class="fa-solid fa-play text-[10px]"></i> Jelajahi Drama Sekarang
        </a>
    </div>
</div>

<!-- History Interactivity Script -->
<script>
function filterHistory(status, btn) {
    // Update active button style
    document.querySelectorAll('.history-filter-btn').forEach(b => {
        b.classList.remove('bg-accent', 'text-darkbg', 'font-semibold');
        b.classList.add('bg-cardbg', 'border', 'border-white/5', 'text-textmuted');
    });
    btn.classList.add('bg-accent', 'text-darkbg', 'font-semibold');
    btn.classList.remove('bg-cardbg', 'border', 'border-white/5', 'text-textmuted');

    // Filter items
    const items = document.querySelectorAll('.history-item');
    let visible = 0;
    items.forEach(item => {
        if (status === 'all' || item.dataset.status === status) {
            item.style.display = 'flex';
            visible++;
        } else {
            item.style.display = 'none';
        }
    });

    // Toggle empty state
    const empty = document.getElementById('history-empty');
    if (visible === 0) {
        empty.classList.remove('hidden');
        empty.classList.add('flex');
    } else {
        empty.classList.add('hidden');
        empty.classList.remove('flex');
    }
}

function confirmClearAll() {
    if (confirm('Hapus seluruh riwayat tontonan Anda dari database?')) {
        fetch('api/clear_history.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.history-item').forEach(item => item.remove());
                const clearBtn = document.getElementById('clear-all-btn');
                if (clearBtn) clearBtn.remove();
                const empty = document.getElementById('history-empty');
                empty.classList.remove('hidden');
                empty.classList.add('flex');
            } else {
                alert('Gagal menghapus riwayat: ' + (data.error || 'Terjadi kesalahan'));
            }
        })
        .catch(err => {
            console.error('Error clearing history:', err);
            // Fallback UI clear
            document.querySelectorAll('.history-item').forEach(item => item.remove());
            const empty = document.getElementById('history-empty');
            empty.classList.remove('hidden');
            empty.classList.add('flex');
        });
    }
}
</script>
