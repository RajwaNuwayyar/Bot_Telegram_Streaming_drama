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
                    <p id="popup-username" class="text-sm font-bold text-white truncate">—</p>
                    <p id="popup-handle" class="text-[11px] text-textmuted truncate"></p>
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

    <!-- Server-Side Search Script logic replaced below -->
</div>

<script>
let currentGenre = 'all';
let originalCardsHTML = '';

document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('dramas-grid');
    if (grid) {
        originalCardsHTML = grid.innerHTML;
    }
    // Tampilkan/sembunyikan tombol clear saat user mengetik (tanpa trigger filter)
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

// Filter genre pill — tetap instan tanpa perlu Enter
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
    // Sembunyikan keyboard virtual di mobile
    input.blur();
    applyFilters();
}

// Reset pencarian dan tampilkan semua drama kembali
function clearDramaSearch() {
    const input = document.getElementById('drama-search-input');
    input.value = '';
    document.getElementById('search-clear-btn').classList.add('hidden');

    const grid = document.getElementById('dramas-grid');
    if (grid && originalCardsHTML && currentGenre === 'all') {
        grid.innerHTML = originalCardsHTML;
        const count = (originalCardsHTML.match(/class="drama-card/g) || []).length;
        const countLabel = document.getElementById('drama-count-label');
        if (countLabel) countLabel.textContent = count + ' Drama';
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
    const grid = document.getElementById('dramas-grid');
    if (!grid) return;

    const countLabel = document.getElementById('drama-count-label');

    // Tampilkan loading state singkat
    grid.style.opacity = '0.5';

    const apiUrl = `api/search.php?q=${encodeURIComponent(searchVal)}&cat=${encodeURIComponent(currentGenre)}`;

    fetch(apiUrl)
        .then(res => {
            if (!res.ok) throw new Error('Network error: ' + res.status);
            return res.text();
        })
        .then(html => {
            grid.style.opacity = '1';
            const count = (html.match(/class="drama-card/g) || []).length;
            if (count === 0) {
                // Tidak ada hasil — tampilkan empty state
                grid.innerHTML = emptyStateHTML(searchVal);
                if (countLabel) countLabel.textContent = '0 Drama';
            } else {
                grid.innerHTML = html;
                if (countLabel) countLabel.textContent = count + ' Drama';
            }
        })
        .catch(err => {
            grid.style.opacity = '1';
            console.warn('Search API error, fallback ke client filter:', err);
            // Fallback filter client-side
            const searchLower = searchVal.toLowerCase();
            let visible = 0;
            grid.querySelectorAll('.drama-card').forEach(card => {
                const title = (card.getAttribute('data-title') || '').toLowerCase();
                const cat = (card.getAttribute('data-category') || '').toLowerCase();
                const matchSearch = !searchLower || title.includes(searchLower);
                const matchGenre = currentGenre === 'all' || cat === currentGenre.toLowerCase();
                card.style.display = (matchSearch && matchGenre) ? '' : 'none';
                if (matchSearch && matchGenre) visible++;
            });
            if (visible === 0) grid.innerHTML = emptyStateHTML(searchVal);
            if (countLabel) countLabel.textContent = visible + ' Drama';
        });
}
</script>
