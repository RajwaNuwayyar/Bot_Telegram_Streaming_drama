<?php
// Ambil data untuk Home dari Database
$featuredBanners = getFeaturedBanners($pdo, 5);
$allDramas = getDramas($pdo, 1000);
$categories = getCategories($pdo);
$bot_username = "TreadLessBot";

// Ambil ID drama yang difavoritkan user untuk penandaan ikon hati
$userFavoriteIds = [];
$telegram_user_id = $_SESSION['telegram_user_id'] ?? null;
if ($telegram_user_id && $pdo) {
    $u = getUserByTelegramId($pdo, $telegram_user_id);
    if ($u) {
        $userFavoriteIds = getUserFavoriteIds($pdo, $u['id']);
    }
}
?>
<!-- Home View -->
<div class="px-5 pt-6 pb-28">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-accentdark to-accent flex items-center justify-center shadow-lg shadow-accent/20">
                <i class="fa-solid fa-play text-darkbg text-xs ml-0.5"></i>
            </div>
            <h1 class="text-xl font-bold tracking-wide">DramaStream <span class="text-[10px] text-accent tracking-widest uppercase align-top font-bold">MINI</span></h1>
        </div>

        <!-- Header Actions: Theme Toggle & Profile Button -->
        <div class="flex items-center gap-2">
            <!-- Theme Toggle Button (Dark / Light Mode) -->
            <button id="theme-toggle-btn" 
                    onclick="toggleTheme()" 
                    title="Ubah Mode (Dark / Light)" 
                    aria-label="Toggle Dark/Light Mode"
                    class="w-9 h-9 rounded-full bg-cardbg border border-white/10 flex items-center justify-center text-textmuted hover:border-accent/40 hover:text-accent transition-all active:scale-90 shadow-sm relative overflow-hidden group">
                <!-- Sun Icon (Tampil saat Dark Mode) -->
                <i id="theme-icon-sun" class="fa-solid fa-sun text-sm text-amber-400 group-hover:rotate-45 transition-transform duration-300"></i>
                <!-- Moon Icon (Tampil saat Light Mode) -->
                <i id="theme-icon-moon" class="fa-solid fa-moon text-sm text-indigo-500 group-hover:-rotate-12 transition-transform duration-300 hidden"></i>
            </button>

            <!-- Profile Button with Mini Popup -->
            <div class="relative" id="profile-btn-wrapper">
                <button id="profile-btn" 
                        onclick="toggleProfilePopup()" 
                        class="w-9 h-9 rounded-full bg-cardbg border border-white/10 flex items-center justify-center text-textmuted hover:border-accent/40 hover:text-white transition-all active:scale-95 shadow-sm relative overflow-hidden focus:outline-none">
                    
                    <!-- 1. Foto Avatar Telegram User (Jika tersedia) -->
                    <img id="home-profile-avatar" 
                         src="" 
                         alt="Avatar" 
                         class="w-full h-full object-cover hidden rounded-full" 
                         onerror="handleHomeAvatarError()">
                    
                    <!-- 2. Initial Avatar (Fallback jika tidak ada foto tapi ada nama user) -->
                    <div id="home-profile-initial" class="hidden w-full h-full rounded-full bg-gradient-to-tr from-accentdark to-accent flex items-center justify-center text-darkbg font-bold text-xs select-none shadow-inner">
                        <span id="home-profile-initial-char">U</span>
                    </div>

                    <!-- 3. Icon Default jika belum ada data user -->
                    <i id="home-profile-icon" class="fa-regular fa-user text-sm"></i>
                </button>

                <!-- Mini Popup -->
                <div id="profile-popup" class="hidden absolute right-0 top-11 w-52 bg-cardbg border border-white/10 rounded-2xl shadow-2xl overflow-hidden z-50 backdrop-blur-md">
                    <div class="px-4 py-3 border-b border-white/5 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-cardbg border border-white/10 flex items-center justify-center shrink-0 overflow-hidden relative">
                            <img id="popup-profile-avatar" src="" alt="Avatar" class="w-full h-full object-cover hidden rounded-full" onerror="this.classList.add('hidden')">
                            <div id="popup-profile-initial" class="hidden w-full h-full rounded-full bg-gradient-to-tr from-accentdark to-accent flex items-center justify-center text-darkbg font-bold text-xs">
                                <span id="popup-profile-initial-char">U</span>
                            </div>
                            <i id="popup-profile-icon" class="fa-regular fa-user text-xs text-textmuted"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p id="popup-username" class="text-sm font-bold text-white truncate">—</p>
                            <p id="popup-handle" class="text-[11px] text-textmuted truncate"></p>
                        </div>
                    </div>
                    <a href="?page=profile" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors">
                        <i class="fa-regular fa-user text-accent text-sm w-4 text-center"></i>
                        <span data-i18n="view_profile" class="text-sm text-white">Lihat Profil</span>
                    </a>
                    <a href="?page=vip" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors">
                        <i class="fa-solid fa-crown text-accent text-sm w-4 text-center"></i>
                        <span data-i18n="manage_vip" class="text-sm text-white">Kelola VIP</span>
                    </a>
                    <a href="?page=favorites" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition-colors border-t border-white/5">
                        <i class="fa-solid fa-heart text-rose-500 text-sm w-4 text-center"></i>
                        <span data-i18n="my_favorites" class="text-sm text-white">Drama Favorit</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="relative mb-3.5 flex gap-2">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-textmuted">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </div>
            <input type="text" id="drama-search-input"
                onkeydown="if(event.key==='Enter'){submitSearch();}"
                data-i18n-placeholder="search_placeholder"
                class="w-full bg-cardbg border border-white/10 rounded-2xl py-3 pl-10 pr-10 text-sm text-white placeholder-textmuted focus:outline-none focus:border-accent/60 transition-all shadow-inner"
                placeholder="Cari drama, judul...">
            <button id="search-clear-btn" onclick="clearDramaSearch()" class="hidden absolute inset-y-0 right-0 pr-3.5 flex items-center text-textmuted hover:text-white">
                <i class="fa-solid fa-circle-xmark text-sm"></i>
            </button>
        </div>
        <!-- Tombol Cari eksplisit untuk mobile -->
        <button onclick="submitSearch()" class="shrink-0 px-4 py-3 bg-accent hover:bg-accentdark text-darkbg font-bold text-sm rounded-2xl shadow-md transition-all active:scale-95">
            Cari
        </button>
    </div>

    <!-- 2. Featured Drama Carousel (Hero Banner) -->
    <?php if (!empty($featuredBanners)): ?>
    <div class="mb-6 relative" id="featured-carousel-section">
        <div class="flex items-center justify-between mb-2.5">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                <h2 class="text-xs font-bold uppercase tracking-wider text-accent" data-i18n="trending_now">Sedang Tren</h2>
            </div>
            <span class="text-[10px] text-textmuted font-medium"><?= count($featuredBanners); ?> Pilihan Utama</span>
        </div>

        <!-- Carousel Track with horizontal snap scroll -->
        <div id="hero-carousel-track" class="flex overflow-x-auto snap-x snap-mandatory hide-scroll gap-3.5 pb-1 -mx-5 px-5 scroll-smooth">
            <?php foreach ($featuredBanners as $bIdx => $banner): 
                $bPoster = getPosterUrl($banner['poster_url'], $bIdx);
                $bWatchId = !empty($banner['first_episode_id']) ? $banner['first_episode_id'] : $banner['id'];
                $bPlayLink = "https://t.me/{$bot_username}?start=watch_{$bWatchId}";
                $bCatName = !empty($banner['category_name']) ? $banner['category_name'] : 'Drama Unggulan';
                $bEpisodes = (int)$banner['total_episodes'];
            ?>
                <div class="hero-slide flex-shrink-0 w-full snap-center cursor-pointer group select-none"
                     onclick="playEpisode(<?= $bWatchId; ?>, '<?= $bPlayLink; ?>')">
                    
                    <div class="relative w-full aspect-[16/9] sm:aspect-[21/9] rounded-3xl bg-cardbg border border-white/10 overflow-hidden shadow-xl transition-all duration-300 group-hover:border-accent/50 group-active:scale-[0.99]">
                        <!-- Poster Image -->
                        <img src="<?= htmlspecialchars($bPoster); ?>" 
                             alt="<?= htmlspecialchars($banner['title']); ?>" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=800&q=80';" />

                        <!-- Gradient overlays for cinematic effect & contrast -->
                        <div class="absolute inset-0 bg-gradient-to-t from-darkbg via-darkbg/50 to-transparent"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-darkbg/90 via-darkbg/20 to-transparent"></div>

                        <!-- Top Badges -->
                        <div class="absolute top-3 left-3 flex items-center gap-1.5">
                            <span class="px-2.5 py-1 rounded-xl bg-accent text-darkbg text-[9px] font-extrabold uppercase tracking-wider shadow-md flex items-center gap-1">
                                <i class="fa-solid fa-fire text-[8px]"></i> HOT
                            </span>
                            <span class="px-2.5 py-1 rounded-xl bg-darkbg/80 backdrop-blur-md text-white text-[9px] font-bold border border-white/10">
                                <?= htmlspecialchars($bCatName); ?>
                            </span>
                        </div>

                        <!-- Bottom Content -->
                        <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h3 class="text-base sm:text-lg font-extrabold text-white leading-tight truncate drop-shadow-md mb-1 group-hover:text-accent transition-colors">
                                    <?= htmlspecialchars($banner['title']); ?>
                                </h3>
                                <div class="flex items-center gap-2 text-[11px] text-slate-300">
                                    <?php if ($bEpisodes > 0): ?>
                                        <span class="flex items-center gap-1 font-semibold text-accent">
                                            <i class="fa-solid fa-layer-group text-[9px]"></i> <?= $bEpisodes; ?> Episode
                                        </span>
                                        <span>•</span>
                                    <?php endif; ?>
                                    <span class="text-white/80 font-medium">Full HD</span>
                                </div>
                            </div>

                            <!-- Play Action Button -->
                            <button class="shrink-0 px-3.5 py-2 rounded-xl bg-accent hover:bg-accentdark text-darkbg font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-accent/25 transition-transform active:scale-95">
                                <i class="fa-solid fa-play text-[10px] ml-0.5"></i>
                                <span>Nonton</span>
                            </button>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- Carousel Pagination Dots -->
        <div class="flex items-center justify-center gap-1.5 mt-2.5" id="hero-carousel-dots">
            <?php foreach ($featuredBanners as $dIdx => $banner): ?>
                <button type="button" 
                        onclick="scrollHeroSlide(<?= $dIdx; ?>)" 
                        aria-label="Slide <?= $dIdx + 1; ?>"
                        class="hero-dot h-1.5 rounded-full transition-all duration-300 <?= $dIdx === 0 ? 'w-6 bg-accent' : 'w-1.5 bg-white/20 hover:bg-white/40'; ?>">
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

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

    <?php if (!empty($categories)): ?>
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
    <?php endif; ?>


    <!-- Drama List Grid -->
    <div class="flex justify-between items-end mb-4">
        <div>
            <h2 class="text-lg font-bold text-white">Semua Drama</h2>
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
                     onclick="playEpisode(<?php echo $watchId; ?>, '<?php echo $playLink; ?>')">
                    
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

                        <!-- Heart / Favorite Button on Top Right -->
                        <?php $isFav = in_array((int)$d['id'], $userFavoriteIds); ?>
                        <button type="button" 
                                onclick="event.stopPropagation(); toggleFavorite(<?= $d['id']; ?>, this)" 
                                class="fav-btn absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-darkbg/80 backdrop-blur-md flex items-center justify-center border transition-all active:scale-75 z-20 <?= $isFav ? 'text-rose-500 border-rose-500/40 shadow-sm shadow-rose-500/20' : 'border-white/10 text-white/70 hover:text-rose-500 hover:border-rose-500/30'; ?>"
                                title="Simpan ke Favorit">
                            <i class="<?= $isFav ? 'fa-solid' : 'fa-regular'; ?> fa-heart text-xs"></i>
                        </button>

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

    <!-- Slide Bar Pagination Controls (Sesuai Desain Daily Drama Bot) -->
    <div id="pagination-wrapper" class="flex items-center justify-center gap-4 mt-6 mb-4 select-none transition-all duration-300">
        <!-- Tombol Prev (<) -->
        <button id="prev-page-btn" 
                onclick="changeDramaPage(-1)" 
                type="button"
                title="Halaman Sebelumnya" 
                aria-label="Previous Page"
                class="w-12 h-12 rounded-full bg-cardbg border border-white/10 flex items-center justify-center text-accent text-base shadow-lg transition-all duration-200 active:scale-90 hover:border-accent/40 hover:text-white disabled:opacity-20 disabled:pointer-events-none">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <!-- Indikator Halaman (1 / X) -->
        <button id="page-indicator-btn" 
                onclick="promptJumpPage()" 
                type="button"
                title="Klik untuk lompat ke nomor halaman" 
                aria-label="Current Page Number"
                class="h-12 px-7 rounded-full bg-cardbg border border-white/10 flex items-center justify-center text-white font-bold text-sm tracking-wider shadow-lg transition-all duration-200 active:scale-95 hover:border-accent/40 cursor-pointer group">
            <span id="current-page-num" class="text-white group-hover:text-accent transition-colors">1</span>
            <span class="text-textmuted mx-2 font-normal">/</span>
            <span id="total-pages-num" class="text-white">1</span>
        </button>

        <!-- Tombol Next (>) -->
        <button id="next-page-btn" 
                onclick="changeDramaPage(1)" 
                type="button"
                title="Halaman Selanjutnya" 
                aria-label="Next Page"
                class="w-12 h-12 rounded-full bg-cardbg border border-white/10 flex items-center justify-center text-accent text-base shadow-lg transition-all duration-200 active:scale-90 hover:border-accent/40 hover:text-white disabled:opacity-20 disabled:pointer-events-none">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</div>

<style>
#dramas-grid {
    transition: opacity 0.15s ease-out, transform 0.15s ease-out;
}
#dramas-grid.page-transitioning {
    opacity: 0.15;
    transform: scale(0.98);
}
.drama-slide-in {
    animation: dramaCardIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}
@keyframes dramaCardIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
// Konfigurasi Pagination (Slide Bar)
const ITEMS_PER_PAGE = 4; // 2 kolom x 2 baris (4 drama per slide sesuai desain)
let currentPage = 1;
let totalPages = 1;
let currentGenre = 'all';
let originalCardsHTML = '';

// Gesture swipe horizontal untuk slide halaman di HP/Telegram
let touchStartX = 0;
let touchStartY = 0;

function setupSwipeGestures() {
    const grid = document.getElementById('dramas-grid');
    if (!grid) return;

    grid.addEventListener('touchstart', (e) => {
        if (!e.touches || e.touches.length === 0) return;
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
    }, { passive: true });

    grid.addEventListener('touchend', (e) => {
        if (!e.changedTouches || e.changedTouches.length === 0) return;
        const diffX = e.changedTouches[0].clientX - touchStartX;
        const diffY = e.changedTouches[0].clientY - touchStartY;
        // Geser dominan horizontal minimal 45px
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 45) {
            if (diffX < 0) {
                // Geser ke kiri -> Halaman berikutnya (Next)
                if (currentPage < totalPages) changeDramaPage(1);
            } else {
                // Geser ke kanan -> Halaman sebelumnya (Prev)
                if (currentPage > 1) changeDramaPage(-1);
            }
        }
    }, { passive: true });
}

function renderDramaPagination(targetPage = 1) {
    const grid = document.getElementById('dramas-grid');
    const paginationWrapper = document.getElementById('pagination-wrapper');
    if (!grid) return;

    const cards = Array.from(grid.querySelectorAll('.drama-card:not(.filtered-out)'));
    const countLabel = document.getElementById('drama-count-label');

    // Jika tidak ada hasil sama sekali
    if (cards.length === 0) {
        if (paginationWrapper) paginationWrapper.classList.add('hidden');
        if (countLabel) countLabel.textContent = '0 Drama';
        return;
    }

    if (paginationWrapper) paginationWrapper.classList.remove('hidden');

    totalPages = Math.ceil(cards.length / ITEMS_PER_PAGE) || 1;
    if (targetPage < 1) targetPage = 1;
    if (targetPage > totalPages) targetPage = totalPages;
    currentPage = targetPage;

    const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
    const endIndex = startIndex + ITEMS_PER_PAGE;

    cards.forEach((card, idx) => {
        if (idx >= startIndex && idx < endIndex) {
            card.style.display = 'flex';
            card.classList.remove('drama-slide-in');
            void card.offsetWidth; // Trigger reflow
            card.classList.add('drama-slide-in');
        } else {
            card.style.display = 'none';
        }
    });

    // Update Text Page Number & Total
    const currentNumEl = document.getElementById('current-page-num');
    const totalNumEl = document.getElementById('total-pages-num');
    const prevBtn = document.getElementById('prev-page-btn');
    const nextBtn = document.getElementById('next-page-btn');

    if (currentNumEl) currentNumEl.textContent = currentPage;
    if (totalNumEl) totalNumEl.textContent = totalPages;

    if (prevBtn) {
        const isPrevDisabled = (currentPage <= 1);
        prevBtn.disabled = isPrevDisabled;
        prevBtn.classList.toggle('opacity-20', isPrevDisabled);
        prevBtn.classList.toggle('pointer-events-none', isPrevDisabled);
    }

    if (nextBtn) {
        const isNextDisabled = (currentPage >= totalPages);
        nextBtn.disabled = isNextDisabled;
        nextBtn.classList.toggle('opacity-20', isNextDisabled);
        nextBtn.classList.toggle('pointer-events-none', isNextDisabled);
    }

    if (countLabel) countLabel.textContent = cards.length + ' Drama';
}

function goToDramaPage(targetPage) {
    if (targetPage < 1 || targetPage > totalPages || targetPage === currentPage) return;
    const grid = document.getElementById('dramas-grid');
    if (grid) {
        grid.classList.add('page-transitioning');
        setTimeout(() => {
            renderDramaPagination(targetPage);
            grid.classList.remove('page-transitioning');
            // Scroll halus jika posisi layar tergulung di bawah daftar drama
            const gridRect = grid.getBoundingClientRect();
            if (gridRect.top < -50) {
                grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 120);
    } else {
        renderDramaPagination(targetPage);
    }
}

function changeDramaPage(direction) {
    goToDramaPage(currentPage + direction);
}

function promptJumpPage() {
    if (totalPages <= 1) return;
    const input = prompt(`Lompat ke halaman drama (1 - ${totalPages}):`, currentPage);
    if (input !== null) {
        const page = parseInt(input.trim(), 10);
        if (!isNaN(page) && page >= 1 && page <= totalPages) {
            goToDramaPage(page);
        } else if (!isNaN(page)) {
            alert(`Halaman tidak valid. Masukkan angka antara 1 dan ${totalPages}.`);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('dramas-grid');
    if (grid) {
        originalCardsHTML = grid.innerHTML;
    }
    // Inisialisasi awal pagination & gesture swipe
    renderDramaPagination(1);
    setupSwipeGestures();

    // Tampilkan/sembunyikan tombol clear saat user mengetik
    const input = document.getElementById('drama-search-input');
    if (input) {
        input.addEventListener('input', () => {
            const clearBtn = document.getElementById('search-clear-btn');
            if (input.value.trim().length > 0) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        });
    }
});

// Filter genre pill
function filterDramasByGenre(slug, btn) {
    currentGenre = slug;

    document.querySelectorAll('.genre-pill').forEach(b => {
        b.classList.remove('bg-accent', 'text-darkbg', 'font-semibold');
        b.classList.add('bg-cardbg', 'border', 'border-white/5', 'text-textmuted');
    });
    btn.classList.add('bg-accent', 'text-darkbg', 'font-semibold');
    btn.classList.remove('bg-cardbg', 'border', 'border-white/5', 'text-textmuted');

    // Reset search input saat ganti genre agar tidak membingungkan
    const input = document.getElementById('drama-search-input');
    if (input) input.value = '';
    document.getElementById('search-clear-btn')?.classList.add('hidden');

    applyFilters();
}

// Dipanggil saat user tekan Enter atau klik tombol Cari
function submitSearch() {
    const input = document.getElementById('drama-search-input');
    if (!input) return;
    input.blur();
    applyFilters();
}

// Reset pencarian dan tampilkan semua drama kembali
function clearDramaSearch() {
    const input = document.getElementById('drama-search-input');
    if (input) input.value = '';
    document.getElementById('search-clear-btn')?.classList.add('hidden');

    const heroSection = document.getElementById('featured-carousel-section');
    if (heroSection && currentGenre === 'all') {
        heroSection.style.display = 'block';
    }

    const grid = document.getElementById('dramas-grid');
    if (grid && originalCardsHTML && currentGenre === 'all') {
        grid.innerHTML = originalCardsHTML;
        renderDramaPagination(1);
        return;
    }
    applyFilters();
}

// Empty state HTML saat tidak ada hasil
function emptyStateHTML(query) {
    const msg = query
        ? `Drama <strong>"${query}"</strong> tidak ditemukan`
        : 'Belum ada drama di kategori ini';
    return `
        <div class="col-span-2 py-14 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 rounded-full bg-cardbg border border-white/10 flex items-center justify-center mb-4 text-textmuted">
                <i class="fa-solid fa-film text-2xl text-accent/60"></i>
            </div>
            <h4 class="font-bold text-white mb-1.5">Drama Tidak Ditemukan</h4>
            <p class="text-xs text-textmuted max-w-[240px] leading-relaxed mb-5">${msg}.<br>Coba kata kunci lain atau request drama favoritmu!</p>
            <a href="?page=request" class="px-5 py-2.5 bg-accent hover:bg-accentdark text-darkbg font-bold text-xs rounded-xl shadow-md shadow-accent/20 transition-all active:scale-95 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-[10px]"></i> Request Drama
            </a>
        </div>`;
}

// Kirim query ke server API
function applyFilters() {
    const searchVal = document.getElementById('drama-search-input').value.trim();
    const heroSection = document.getElementById('featured-carousel-section');
    if (heroSection) {
        heroSection.style.display = (searchVal || currentGenre !== 'all') ? 'none' : 'block';
    }

    const grid = document.getElementById('dramas-grid');
    if (!grid) return;

    // Tampilkan loading state singkat
    grid.style.opacity = '0.5';

    // Sertakan Telegram ID jika tersedia di environment WebApp
    const tgId = window.Telegram?.WebApp?.initDataUnsafe?.user?.id || '';
    const apiUrl = `api/search.php?q=${encodeURIComponent(searchVal)}&cat=${encodeURIComponent(currentGenre)}${tgId ? '&tg_user_id=' + encodeURIComponent(tgId) : ''}`;

    fetch(apiUrl)
        .then(res => {
            if (!res.ok) throw new Error('Network error: ' + res.status);
            return res.text();
        })
        .then(html => {
            grid.style.opacity = '1';
            const count = (html.match(/class="drama-card/g) || []).length;
            if (count === 0) {
                grid.innerHTML = emptyStateHTML(searchVal);
            } else {
                grid.innerHTML = html;
            }
            renderDramaPagination(1);
        })
        .catch(err => {
            grid.style.opacity = '1';
            console.warn('Search API error, fallback ke client filter:', err);
            const searchLower = searchVal.toLowerCase();
            let visible = 0;
            grid.querySelectorAll('.drama-card').forEach(card => {
                const title = (card.getAttribute('data-title') || '').toLowerCase();
                const cat = (card.getAttribute('data-category') || '').toLowerCase();
                const matchSearch = !searchLower || title.includes(searchLower);
                const matchGenre = currentGenre === 'all' || cat === currentGenre.toLowerCase();
                if (matchSearch && matchGenre) {
                    card.classList.remove('filtered-out');
                    visible++;
                } else {
                    card.classList.add('filtered-out');
                    card.style.display = 'none';
                }
            });
            if (visible === 0) {
                grid.innerHTML = emptyStateHTML(searchVal);
            }
            renderDramaPagination(1);
        });
}

// ==========================================
// HERO BANNER CAROUSEL INTERACTION & AUTO SLIDE
// ==========================================
(function() {
    const track = document.getElementById('hero-carousel-track');
    const dots = document.querySelectorAll('.hero-dot');
    if (!track || dots.length <= 1) return;

    let autoScrollTimer = null;
    let currentIndex = 0;

    function updateDots(index) {
        currentIndex = index;
        dots.forEach((dot, idx) => {
            if (idx === index) {
                dot.classList.remove('w-1.5', 'bg-white/20');
                dot.classList.add('w-6', 'bg-accent');
            } else {
                dot.classList.remove('w-6', 'bg-accent');
                dot.classList.add('w-1.5', 'bg-white/20');
            }
        });
    }

    // Listener scroll untuk update dot aktif saat digeser (swipe)
    track.addEventListener('scroll', () => {
        const slideWidth = track.clientWidth;
        if (slideWidth > 0) {
            const index = Math.round(track.scrollLeft / slideWidth);
            if (index !== currentIndex && index < dots.length) {
                updateDots(index);
            }
        }
    }, { passive: true });

    // Expose scroll function untuk klik dot
    window.scrollHeroSlide = function(index) {
        const slideWidth = track.clientWidth;
        track.scrollTo({
            left: index * slideWidth,
            behavior: 'smooth'
        });
        updateDots(index);
        restartAutoScroll();
    };

    // Auto rotate banner setiap 5 detik
    function startAutoScroll() {
        autoScrollTimer = setInterval(() => {
            const nextIndex = (currentIndex + 1) % dots.length;
            window.scrollHeroSlide(nextIndex);
        }, 5000);
    }

    function restartAutoScroll() {
        if (autoScrollTimer) clearInterval(autoScrollTimer);
        startAutoScroll();
    }

    // Jeda auto scroll saat disentuh / kursor berada di atas slide
    track.addEventListener('touchstart', () => clearInterval(autoScrollTimer), { passive: true });
    track.addEventListener('touchend', () => restartAutoScroll(), { passive: true });
    track.addEventListener('mouseenter', () => clearInterval(autoScrollTimer));
    track.addEventListener('mouseleave', () => restartAutoScroll());

    startAutoScroll();
})();
</script>
