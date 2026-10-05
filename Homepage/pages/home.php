<?php
// Ambil data untuk Home dari Database
$featuredBanners = getFeaturedBanners($pdo, 5);
$allDramas = getDramas($pdo, 24);
$categories = getCategories($pdo);
$bot_username = "TreadLessBot";
?>
<!-- Home View -->
<div class="px-5 pt-6 pb-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-accentdark to-accent flex items-center justify-center shadow-lg shadow-accent/20">
                <i class="fa-solid fa-play text-darkbg text-xs ml-0.5"></i>
            </div>
            <h1 class="text-xl font-bold tracking-wide">DramaStream <span class="text-[10px] text-accent tracking-widest uppercase align-top font-bold">MINI</span></h1>
        </div>
        <!-- Profile Button with Mini Popup -->
        <div class="relative" id="profile-btn-wrapper">
            <button id="profile-btn" onclick="toggleProfilePopup()" class="w-9 h-9 rounded-full bg-cardbg border border-white/10 flex items-center justify-center text-textmuted hover:border-accent/40 hover:text-white transition-all active:scale-95 shadow-sm">
                <i class="fa-regular fa-user text-sm"></i>
            </button>

            <!-- Mini Popup -->
            <div id="profile-popup" class="hidden absolute right-0 top-11 w-48 bg-[#141C2B] border border-white/10 rounded-2xl shadow-2xl overflow-hidden z-50 backdrop-blur-md">
                <div class="px-4 py-3 border-b border-white/5">
                    <p id="popup-username" class="text-sm font-bold text-white truncate">DramaFan</p>
                    <p id="popup-handle" class="text-[11px] text-textmuted truncate">@dramafan99</p>
                </div>
                <a href="?page=profile" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors">
                    <i class="fa-regular fa-user text-accent text-sm w-4 text-center"></i>
                    <span data-i18n="view_profile" class="text-sm text-white">Lihat Profil</span>
                </a>
                <a href="?page=vip" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors">
                    <i class="fa-solid fa-crown text-accent text-sm w-4 text-center"></i>
                    <span data-i18n="manage_vip" class="text-sm text-white">Kelola VIP</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="relative mb-3.5">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-textmuted">
            <i class="fa-solid fa-magnifying-glass text-sm"></i>
        </div>
        <input type="text" id="drama-search-input" oninput="filterDramasBySearch()" data-i18n-placeholder="search_placeholder" class="w-full bg-cardbg border border-white/10 rounded-2xl py-3 pl-10 pr-10 text-sm text-white placeholder-textmuted focus:outline-none focus:border-accent/60 transition-all shadow-inner" placeholder="Cari drama, judul, genre...">
        <button id="search-clear-btn" onclick="clearDramaSearch()" class="hidden absolute inset-y-0 right-0 pr-3.5 flex items-center text-textmuted hover:text-white">
            <i class="fa-solid fa-circle-xmark text-sm"></i>
        </button>
    </div>

    <!-- Request Drama Prompt Banner -->
    <div class="flex items-center justify-between bg-gradient-to-r from-accent/15 to-transparent border border-accent/20 rounded-2xl px-4 py-3 mb-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-accent/20 flex items-center justify-center text-accent shrink-0 shadow-sm">
                <i class="fa-solid fa-pen-to-square text-sm"></i>
            </div>
            <div>
                <p data-i18n="cant_find_drama" class="text-xs text-white font-semibold">Tidak menemukan drama favorit?</p>
                <p data-i18n="admin_upload" class="text-[11px] text-accent">Admin akan segera mengunggahnya untuk Anda!</p>
            </div>
        </div>
        <a href="?page=request" data-i18n="request_btn" class="px-3.5 py-1.5 bg-accent hover:bg-accentdark text-darkbg text-[11px] font-bold uppercase tracking-wider rounded-xl shadow-md transition-transform active:scale-95">
            Request
        </a>
    </div>

    <!-- Genre Pills -->
    <div class="flex overflow-x-auto gap-2.5 pb-2 mb-6 hide-scroll" id="genre-pills-container">
        <button onclick="filterDramasByGenre('all', this)" class="genre-pill whitespace-nowrap px-4 py-2 rounded-full bg-accent text-darkbg font-semibold text-xs transition-all shadow-sm">
            Semua
        </button>
        <?php foreach ($categories as $cat): ?>
            <button onclick="filterDramasByGenre('<?php echo htmlspecialchars($cat['slug']); ?>', this)" 
                    data-slug="<?php echo htmlspecialchars($cat['slug']); ?>"
                    class="genre-pill whitespace-nowrap px-4 py-2 rounded-full bg-cardbg border border-white/5 text-textmuted text-xs hover:text-white hover:border-white/20 transition-all">
                <?php echo htmlspecialchars($cat['name']); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Featured Banners Section -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-fire text-accent text-sm"></i> Unggulan Hari Ini
            </h2>
            <span class="text-[11px] text-textmuted">Geser untuk melihat</span>
        </div>

        <div class="flex overflow-x-auto gap-4 pb-3 hide-scroll snap-x" id="featured-banners">
            <?php if (empty($featuredBanners)): ?>
                <div class="w-full h-40 rounded-2xl bg-cardbg border border-white/5 flex items-center justify-center text-textmuted text-sm">
                    Belum ada drama unggulan
                </div>
            <?php else: ?>
                <?php foreach ($featuredBanners as $idx => $fb): 
                    $bannerPoster = getPosterUrl($fb['poster_url'], $idx);
                    $firstEpId = !empty($fb['first_episode_id']) ? $fb['first_episode_id'] : $fb['id'];
                    $playLink = "https://t.me/{$bot_username}?start=watch_{$firstEpId}";
                ?>
                    <div onclick="window.Telegram.WebApp.openTelegramLink('<?php echo $playLink; ?>')" 
                         class="min-w-[270px] max-w-[290px] h-[340px] rounded-3xl border border-white/10 relative overflow-hidden flex flex-col justify-end p-5 snap-center shrink-0 group cursor-pointer shadow-xl transition-transform active:scale-[0.98]">
                        <!-- Poster Image with Parallax zoom on hover -->
                        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-105" 
                             style="background-image: url('<?php echo htmlspecialchars($bannerPoster); ?>');">
                        </div>

                        <!-- Gradient Overlays for optimal readability -->
                        <div class="absolute inset-0 bg-gradient-to-t from-darkbg via-darkbg/50 to-transparent"></div>
                        <div class="absolute inset-0 bg-gradient-to-b from-darkbg/40 via-transparent to-transparent"></div>

                        <!-- Floating Play Badge on Hover -->
                        <div class="absolute top-4 right-4 w-10 h-10 rounded-full bg-accent/90 text-darkbg flex items-center justify-center shadow-lg transition-all transform scale-90 group-hover:scale-100 opacity-90 group-hover:opacity-100">
                            <i class="fa-solid fa-play text-sm ml-0.5"></i>
                        </div>

                        <!-- Content Info -->
                        <div class="relative z-10">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider bg-accent/20 text-accent uppercase border border-accent/30 backdrop-blur-md">
                                    <?php echo htmlspecialchars($fb['category_name']); ?>
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-black/40 text-textmuted backdrop-blur-md border border-white/5">
                                    <?php echo (int)$fb['total_episodes']; ?> Episode
                                </span>
                            </div>

                            <h3 class="font-bold text-lg text-white mb-1.5 drop-shadow-md line-clamp-1 leading-snug group-hover:text-accent transition-colors">
                                <?php echo htmlspecialchars($fb['title']); ?>
                            </h3>

                            <?php if (!empty($fb['description'])): ?>
                                <p class="text-xs text-white/70 line-clamp-2 mb-3 leading-relaxed drop-shadow-sm font-normal">
                                    <?php echo htmlspecialchars($fb['description']); ?>
                                </p>
                            <?php endif; ?>

                            <div class="flex items-center justify-between text-xs text-textmuted pt-1 border-t border-white/10">
                                <span class="flex items-center gap-1.5 text-yellow-400 font-semibold text-[11px]">
                                    <i class="fa-solid fa-star text-[10px]"></i> 4.9
                                </span>
                                <span class="text-[11px] text-white/60">
                                    <i class="fa-regular fa-eye mr-1"></i><?php echo number_format($fb['views_count']); ?> ditonton
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Trending Now / Drama List Grid -->
    <div class="flex justify-between items-end mb-4">
        <div>
            <h2 data-i18n="trending_now" class="text-lg font-bold text-white">Trending Sekarang</h2>
            <p class="text-[11px] text-textmuted">Koleksi mini-drama terlengkap</p>
        </div>
        <span id="drama-count-label" class="text-xs text-accent font-medium">
            <?php echo count($allDramas); ?> Drama
        </span>
    </div>

    <!-- Drama Cards Grid -->
    <div class="grid grid-cols-2 gap-3.5" id="dramas-grid">
        <?php if (empty($allDramas)): ?>
            <div class="col-span-2 py-16 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 rounded-full bg-cardbg border border-white/10 flex items-center justify-center mb-3 text-textmuted">
                    <i class="fa-regular fa-folder-open text-2xl"></i>
                </div>
                <h4 class="font-bold text-white mb-1">Belum Ada Drama</h4>
                <p class="text-xs text-textmuted">Upload drama di channel Telegram untuk langsung menampilkannya di sini.</p>
            </div>
        <?php else: ?>
            <?php foreach ($allDramas as $idx => $d): 
                $poster = getPosterUrl($d['poster_url'], $idx);
                $epCount = (int)$d['total_episodes'];
                $catName = !empty($d['category_name']) ? $d['category_name'] : 'Drama';
                $catSlug = !empty($d['category_slug']) ? $d['category_slug'] : 'drama';
                $watchId = !empty($d['first_episode_id']) ? $d['first_episode_id'] : $d['id'];
                $playLink = "https://t.me/{$bot_username}?start=watch_{$watchId}";
            ?>
                <div class="drama-card flex flex-col cursor-pointer group" 
                     data-title="<?php echo strtolower(htmlspecialchars($d['title'])); ?>" 
                     data-category="<?php echo htmlspecialchars($catSlug); ?>"
                     onclick="window.Telegram.WebApp.openTelegramLink('<?php echo $playLink; ?>')">
                    
                    <!-- Poster Container with 3:4 Aspect Ratio -->
                    <div class="w-full aspect-[3/4] rounded-2xl bg-cardbg border border-white/5 relative overflow-hidden mb-2.5 shadow-md transition-all duration-300 group-hover:border-accent/40 group-hover:shadow-accent/10 group-active:scale-[0.98]">
                        <!-- Image -->
                        <img src="<?php echo htmlspecialchars($poster); ?>" 
                             alt="<?php echo htmlspecialchars($d['title']); ?>" 
                             loading="lazy"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&q=80';" />

                        <!-- Dark Bottom Gradient for badge & contrast -->
                        <div class="absolute inset-0 bg-gradient-to-t from-darkbg/90 via-transparent to-transparent"></div>

                        <!-- Category Tag on Top Left -->
                        <div class="absolute top-2.5 left-2.5">
                            <span class="px-2 py-0.5 rounded-md bg-darkbg/70 backdrop-blur-md text-[9px] font-bold uppercase tracking-wider text-accent border border-accent/20 shadow-sm">
                                <?php echo htmlspecialchars($catName); ?>
                            </span>
                        </div>

                        <!-- Rating Badge on Top Right -->
                        <div class="absolute top-2.5 right-2.5 px-1.5 py-0.5 rounded-md bg-darkbg/80 backdrop-blur-md text-[10px] font-bold text-yellow-400 flex items-center gap-1 border border-white/10 shadow-sm">
                            <i class="fa-solid fa-star text-[9px]"></i> 4.8
                        </div>

                        <!-- Play Icon Button overlay on hover -->
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-darkbg/40 backdrop-blur-[2px]">
                            <div class="w-11 h-11 rounded-full bg-accent text-darkbg flex items-center justify-center shadow-xl transform scale-90 group-hover:scale-100 transition-transform">
                                <i class="fa-solid fa-play text-sm ml-0.5"></i>
                            </div>
                        </div>

                        <!-- Episode Count Badge on Bottom -->
                        <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between text-[11px] text-white/90">
                            <span class="font-medium bg-darkbg/60 px-2 py-0.5 rounded-md backdrop-blur-sm border border-white/5">
                                <?php echo $epCount; ?> Episode
                            </span>
                            <span class="text-[10px] text-textmuted">
                                <i class="fa-solid fa-eye text-[9px]"></i> <?php echo number_format($d['views_count']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Meta Info below card -->
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
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Empty Search State -->
    <div id="no-search-results" class="hidden py-14 flex flex-col items-center justify-center text-center">
        <div class="w-14 h-14 rounded-full bg-cardbg border border-white/10 flex items-center justify-center mb-3 text-textmuted">
            <i class="fa-solid fa-magnifying-glass text-xl"></i>
        </div>
        <h4 class="font-bold text-white text-sm mb-1">Drama Tidak Ditemukan</h4>
        <p class="text-xs text-textmuted max-w-[240px]">Coba cari dengan kata kunci lain atau kirimkan permintaan di menu Request.</p>
    </div>
</div>

<!-- Home Interactivity Script -->
<script>
let currentGenre = 'all';

function filterDramasByGenre(slug, btn) {
    currentGenre = slug;
    
    // Update pill buttons style
    document.querySelectorAll('.genre-pill').forEach(b => {
        b.classList.remove('bg-accent', 'text-darkbg', 'font-semibold');
        b.classList.add('bg-cardbg', 'border', 'border-white/5', 'text-textmuted');
    });
    btn.classList.add('bg-accent', 'text-darkbg', 'font-semibold');
    btn.classList.remove('bg-cardbg', 'border', 'border-white/5', 'text-textmuted');

    applyFilters();
}

function filterDramasBySearch() {
    const input = document.getElementById('drama-search-input');
    const clearBtn = document.getElementById('search-clear-btn');
    if (input.value.trim().length > 0) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }
    applyFilters();
}

function clearDramaSearch() {
    const input = document.getElementById('drama-search-input');
    input.value = '';
    document.getElementById('search-clear-btn').classList.add('hidden');
    applyFilters();
}

function applyFilters() {
    const searchVal = document.getElementById('drama-search-input').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.drama-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const title = card.getAttribute('data-title') || '';
        const category = card.getAttribute('data-category') || '';
        
        const matchesGenre = (currentGenre === 'all' || category === currentGenre);
        const matchesSearch = (searchVal === '' || title.includes(searchVal));

        if (matchesGenre && matchesSearch) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    const noResults = document.getElementById('no-search-results');
    const countLabel = document.getElementById('drama-count-label');
    
    if (visibleCount === 0 && cards.length > 0) {
        noResults.classList.remove('hidden');
    } else {
        noResults.classList.add('hidden');
    }

    if (countLabel) {
        countLabel.textContent = visibleCount + ' Drama';
    }
}
</script>
