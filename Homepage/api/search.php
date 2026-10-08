<?php
session_start();
require_once __DIR__ . '/../../database/koneksi.php';
require_once __DIR__ . '/../includes/db_queries.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$cat = isset($_GET['cat']) ? trim($_GET['cat']) : 'all';

// Baca bot username dari env jika ada
$bot_username = isset($_ENV['BOT_USERNAME']) && !empty($_ENV['BOT_USERNAME']) ? $_ENV['BOT_USERNAME'] : 'TreadLessBot';

$category_id = null;
if ($pdo && $cat !== 'all' && !empty($cat)) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->execute([$cat]);
        $category_id = $stmt->fetchColumn();
    } catch (Exception $e) {
        $category_id = null;
    }
}

$dramas = ($pdo) ? getDramas($pdo, 24, $category_id, $q) : [];

if (empty($dramas)) {
    echo '<div class="col-span-2 py-16 flex flex-col items-center justify-center text-center">';
    echo '    <div class="w-16 h-16 rounded-full bg-cardbg border border-white/10 flex items-center justify-center mb-3 text-textmuted">';
    echo '        <i class="fa-solid fa-magnifying-glass text-2xl"></i>';
    echo '    </div>';
    echo '    <h4 class="font-bold text-white mb-1">Drama Tidak Ditemukan</h4>';
    echo '    <p class="text-xs text-textmuted">Coba cari dengan kata kunci lain.</p>';
    echo '</div>';
    exit;
}

foreach ($dramas as $idx => $d) {
    $poster = getPosterUrl($d['poster_url'], $idx);
    $epCount = (int)$d['total_episodes'];
    $catName = !empty($d['category_name']) ? $d['category_name'] : 'Drama';
    $catSlug = !empty($d['category_slug']) ? $d['category_slug'] : 'drama';
    $watchId = !empty($d['first_episode_id']) ? $d['first_episode_id'] : $d['id'];
    $playLink = "https://t.me/{$bot_username}?start=watch_{$watchId}";
    
    // Exact same card HTML structure from home.php
    ?>
    <div class="drama-card flex flex-col cursor-pointer group" 
         data-title="<?php echo strtolower(htmlspecialchars($d['title'])); ?>" 
         data-category="<?php echo htmlspecialchars($catSlug); ?>"
         onclick="playEpisode(<?php echo $watchId; ?>, '<?php echo $playLink; ?>')">
        
        <div class="w-full aspect-[3/4] rounded-2xl bg-cardbg border border-white/5 relative overflow-hidden mb-2.5 shadow-md transition-all duration-300 group-hover:border-accent/40 group-hover:shadow-accent/10 group-active:scale-[0.98]">
            <img src="<?php echo htmlspecialchars($poster); ?>" 
                 alt="<?php echo htmlspecialchars($d['title']); ?>" 
                 loading="lazy"
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&q=80';" />

            <div class="absolute inset-0 bg-gradient-to-t from-darkbg/90 via-transparent to-transparent"></div>

            <div class="absolute top-2.5 left-2.5">
                <span class="px-2 py-0.5 rounded-md bg-darkbg/70 backdrop-blur-md text-[9px] font-bold uppercase tracking-wider text-accent border border-accent/20 shadow-sm">
                    <?php echo htmlspecialchars($catName); ?>
                </span>
            </div>

            <div class="absolute top-2.5 right-2.5 px-1.5 py-0.5 rounded-md bg-darkbg/80 backdrop-blur-md text-[10px] font-bold text-yellow-400 flex items-center gap-1 border border-white/10 shadow-sm">
                <i class="fa-solid fa-star text-[9px]"></i> 4.8
            </div>

            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-darkbg/40 backdrop-blur-[2px]">
                <div class="w-11 h-11 rounded-full bg-accent text-darkbg flex items-center justify-center shadow-xl transform scale-90 group-hover:scale-100 transition-transform">
                    <i class="fa-solid fa-play text-sm ml-0.5"></i>
                </div>
            </div>

            <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between text-[11px] text-white/90">
                <span class="font-medium bg-darkbg/60 px-2 py-0.5 rounded-md backdrop-blur-sm border border-white/5">
                    <?php echo $epCount; ?> Episode
                </span>
                <span class="text-[10px] text-textmuted">
                    <i class="fa-solid fa-eye text-[9px]"></i> <?php echo number_format($d['views_count']); ?>
                </span>
            </div>
        </div>

        <div class="px-1">
            <h4 class="font-bold text-sm leading-tight text-white mb-1 line-clamp-1 group-hover:text-accent transition-colors">
                <?php echo htmlspecialchars($d['title']); ?>
            </h4>
            <p class="text-[11px] text-textmuted flex items-center gap-1.5">
                <span><?php echo htmlspecialchars($catName); ?></span>
                <span>•</span>
                <span class="text-accent"><?php echo $epCount > 0 ? $epCount . ' eps' : 'Segera'; ?></span>
            </p>
        </div>
    </div>
    <?php
}
?>
