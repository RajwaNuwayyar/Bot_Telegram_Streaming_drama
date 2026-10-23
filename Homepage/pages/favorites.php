<?php
// Ambil user dan daftar drama favorit dari database
$telegram_user_id = isset($_SESSION['telegram_user_id']) ? $_SESSION['telegram_user_id'] : null;
$user = null;
$user_id = 0;

if ($telegram_user_id) {
    $user = getUserByTelegramId($pdo, $telegram_user_id);
}

// Fallback jika dibuka langsung di browser
if (!$user && $pdo) {
    $user = $pdo->query("SELECT * FROM users ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
}

if ($user) {
    $user_id = (int)$user['id'];
}

$favorites = ($pdo && $user_id) ? getUserFavorites($pdo, $user_id) : [];
$bot_username = isset($_ENV['BOT_USERNAME']) && !empty($_ENV['BOT_USERNAME']) ? $_ENV['BOT_USERNAME'] : 'TreadLessBot';
?>

<!-- Favorites View -->
<div class="px-5 pt-6 pb-28">

    <!-- Header Section with Back Button -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="?page=home" class="w-9 h-9 rounded-full bg-cardbg border border-white/10 flex items-center justify-center text-textmuted hover:text-white transition-all active:scale-90 shadow-sm" title="Kembali ke Beranda">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-rose-600 to-rose-400 flex items-center justify-center shadow-lg shadow-rose-500/20">
                    <i class="fa-solid fa-heart text-white text-xs"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white leading-tight" data-i18n="my_favorites">Drama Favorit</h1>
                    <p class="text-[11px] text-textmuted">Koleksi drama yang kamu simpan</p>
                </div>
            </div>
        </div>

        <span id="fav-count-badge" class="px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs font-bold">
            <?= count($favorites); ?> Drama
        </span>
    </div>

    <!-- Empty State -->
    <div id="fav-empty-state" class="<?= empty($favorites) ? '' : 'hidden'; ?> py-16 flex flex-col items-center justify-center text-center">
        <div class="w-20 h-20 rounded-full bg-cardbg border border-white/10 flex items-center justify-center mb-4 text-rose-500/80 shadow-inner">
            <i class="fa-solid fa-heart text-3xl animate-pulse"></i>
        </div>
        <h3 class="font-bold text-white text-base mb-1.5" data-i18n="favorites_empty_title">Belum Ada Drama Favorit</h3>
        <p class="text-xs text-textmuted max-w-[260px] leading-relaxed mb-6" data-i18n="favorites_empty_desc">
            Ketuk ikon hati pada kartu drama mana pun untuk menyimpannya ke koleksi favoritmu!
        </p>
        <a href="?page=home" class="px-5 py-2.5 bg-accent hover:bg-accentdark text-darkbg font-bold text-xs rounded-xl shadow-md shadow-accent/20 transition-all active:scale-95 flex items-center gap-2">
            <i class="fa-solid fa-compass text-xs"></i>
            <span data-i18n="explore_dramas">Jelajahi Drama Sekarang</span>
        </a>
    </div>

    <!-- Favorites Grid -->
    <?php if (!empty($favorites)): ?>
    <div class="grid grid-cols-2 gap-3.5" id="fav-grid">
        <?php foreach ($favorites as $idx => $d): 
            $poster = getPosterUrl($d['poster_url'], $idx);
            $epCount = (int)$d['total_episodes'];
            $catName = !empty($d['category_name']) ? $d['category_name'] : 'Drama';
            $watchId = !empty($d['first_episode_id']) ? $d['first_episode_id'] : $d['id'];
            $playLink = "https://t.me/{$bot_username}?start=watch_{$watchId}";
        ?>
            <div id="fav-card-<?= $d['id']; ?>" 
                 class="fav-drama-card flex flex-col cursor-pointer group relative"
                 onclick="playEpisode(<?= $watchId; ?>, '<?= $playLink; ?>')">
                
                <div class="w-full aspect-[3/4] rounded-2xl bg-cardbg border border-white/5 relative overflow-hidden mb-2.5 shadow-md transition-all duration-300 group-hover:border-accent/40 group-hover:shadow-accent/10 group-active:scale-[0.98]">
                    <!-- Poster Image -->
                    <img src="<?= htmlspecialchars($poster); ?>" 
                         alt="<?= htmlspecialchars($d['title']); ?>" 
                         loading="lazy"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&q=80';" />

                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-darkbg/90 via-transparent to-transparent"></div>

                    <!-- Category Badge -->
                    <div class="absolute top-2.5 left-2.5">
                        <span class="px-2 py-0.5 rounded-md bg-darkbg/70 backdrop-blur-md text-[9px] font-bold uppercase tracking-wider text-accent border border-accent/20 shadow-sm">
                            <?= htmlspecialchars($catName); ?>
                        </span>
                    </div>

                    <!-- Heart Button (Solid Red in Favorites page) -->
                    <button type="button"
                            onclick="event.stopPropagation(); toggleFavorite(<?= $d['id']; ?>, this)"
                            class="fav-btn absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-darkbg/85 backdrop-blur-md flex items-center justify-center border border-rose-500/40 text-rose-500 hover:scale-110 active:scale-75 transition-all shadow-md z-20"
                            title="Hapus dari Favorit">
                        <i class="fa-solid fa-heart text-xs"></i>
                    </button>

                    <!-- Play Hover Overlay -->
                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-darkbg/40 backdrop-blur-[2px]">
                        <div class="w-11 h-11 rounded-full bg-accent text-darkbg flex items-center justify-center shadow-xl transform scale-90 group-hover:scale-100 transition-transform">
                            <i class="fa-solid fa-play text-sm ml-0.5"></i>
                        </div>
                    </div>

                    <!-- Episode Count & Views -->
                    <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between text-[11px] text-white/90">
                        <span class="font-medium bg-darkbg/60 px-2 py-0.5 rounded-md backdrop-blur-sm border border-white/5">
                            <?= $epCount; ?> Episode
                        </span>
                        <span class="text-[10px] text-textmuted">
                            <i class="fa-solid fa-eye text-[9px]"></i> <?= number_format($d['views_count']); ?>
                        </span>
                    </div>
                </div>

                <!-- Title & Category Info -->
                <div class="px-1">
                    <h4 class="font-bold text-sm leading-tight text-white mb-1 line-clamp-1 group-hover:text-accent transition-colors">
                        <?= htmlspecialchars($d['title']); ?>
                    </h4>
                    <p class="text-[11px] text-textmuted flex items-center gap-1.5">
                        <span><?= htmlspecialchars($catName); ?></span>
                        <span>•</span>
                        <span class="text-accent"><?= $epCount > 0 ? $epCount . ' eps' : 'Segera'; ?></span>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
