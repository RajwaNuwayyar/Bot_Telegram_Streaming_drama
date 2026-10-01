<!-- Home View -->
<div class="px-5 pt-6 pb-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-accent flex items-center justify-center">
                <i class="fa-solid fa-play text-darkbg text-sm ml-0.5"></i>
            </div>
            <h1 class="text-xl font-bold tracking-wide">DramaStream <span class="text-[10px] text-accent tracking-widest uppercase align-top">MINI</span></h1>
        </div>
        <!-- Profile Button with Mini Popup -->
        <div class="relative" id="profile-btn-wrapper">
            <button id="profile-btn" onclick="toggleProfilePopup()" class="w-9 h-9 rounded-full bg-cardbg border border-white/10 flex items-center justify-center text-textmuted hover:border-accent/40 hover:text-white transition-all active:scale-95">
                <i class="fa-regular fa-user text-sm"></i>
            </button>

            <!-- Mini Popup -->
            <div id="profile-popup" class="hidden absolute right-0 top-11 w-48 bg-[#141C2B] border border-white/10 rounded-2xl shadow-xl overflow-hidden z-50">
                <div class="px-4 py-3 border-b border-white/5">
                    <p id="popup-username" class="text-sm font-bold text-white truncate">DramaFan99</p>
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
    <div class="relative mb-3">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i class="fa-solid fa-magnifying-glass text-textmuted text-sm"></i>
        </div>
        <input type="text" data-i18n-placeholder="search_placeholder" class="w-full bg-cardbg border border-white/5 rounded-xl py-3 pl-10 pr-4 text-sm text-white placeholder-textmuted focus:outline-none focus:border-accent/50 transition-colors" placeholder="Search dramas...">
    </div>

    <!-- Request Drama Prompt -->
    <div class="flex items-center justify-between bg-accent/10 border border-accent/20 rounded-xl px-4 py-3 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-accent/20 flex items-center justify-center text-accent">
                <i class="fa-solid fa-pen-to-square text-xs"></i>
            </div>
            <div>
                <p data-i18n="cant_find_drama" class="text-xs text-white font-semibold">Can't find your drama?</p>
                <p data-i18n="admin_upload" class="text-[10px] text-accent">Admin will upload it for you!</p>
            </div>
        </div>
        <a href="?page=request" data-i18n="request_btn" class="px-3 py-1.5 bg-accent text-darkbg text-[10px] font-bold uppercase tracking-wider rounded-lg shadow-sm">
            Request
        </a>
    </div>

    <!-- Genre Pills -->
    <div class="flex overflow-x-auto gap-3 pb-2 mb-6 hide-scroll">
        <button data-i18n="genre_all" class="whitespace-nowrap px-5 py-2 rounded-full bg-accent text-darkbg font-semibold text-sm">All</button>
        <button data-i18n="genre_romance" class="whitespace-nowrap px-5 py-2 rounded-full bg-cardbg border border-white/5 text-textmuted text-sm hover:text-white transition-colors">Romance</button>
        <button data-i18n="genre_thriller" class="whitespace-nowrap px-5 py-2 rounded-full bg-cardbg border border-white/5 text-textmuted text-sm hover:text-white transition-colors">Thriller</button>
        <button data-i18n="genre_drama" class="whitespace-nowrap px-5 py-2 rounded-full bg-cardbg border border-white/5 text-textmuted text-sm hover:text-white transition-colors">Drama</button>
        <button data-i18n="genre_fantasy" class="whitespace-nowrap px-5 py-2 rounded-full bg-cardbg border border-white/5 text-textmuted text-sm hover:text-white transition-colors">Fantasy</button>
    </div>

    <!-- Featured Banners -->
    <div class="flex overflow-x-auto gap-4 pb-4 mb-6 hide-scroll snap-x">
        <!-- Banner 1 -->
        <div class="min-w-[260px] h-[320px] rounded-2xl bg-gradient-to-b from-cardbg to-darkbg border border-white/5 relative overflow-hidden flex flex-col justify-end p-4 snap-center shrink-0 group">
            <div class="absolute inset-0 flex flex-col items-center justify-center opacity-30 group-hover:opacity-10 transition-opacity">
                <i class="fa-regular fa-image text-3xl mb-2"></i>
                <p class="text-[10px] tracking-widest uppercase text-center w-2/3">Secret Love Featured Banner</p>
            </div>
            <div class="relative z-10">
                <span data-i18n="genre_romance" class="inline-block px-2 py-0.5 rounded text-[9px] font-bold tracking-wider bg-accent/20 text-accent uppercase mb-2 border border-accent/20">Romance</span>
                <h3 class="font-bold text-lg mb-1 drop-shadow-md">Secret Love</h3>
                <div class="flex items-center gap-2 text-xs text-textmuted">
                    <span>24 eps</span>
                    <span>•</span>
                    <span class="flex items-center gap-1"><i class="fa-solid fa-star text-yellow-500 text-[10px]"></i> 4.8</span>
                </div>
            </div>
            <!-- Gradient Overlay for text readability -->
            <div class="absolute inset-0 bg-gradient-to-t from-darkbg via-darkbg/40 to-transparent"></div>
        </div>

        <!-- Banner 2 -->
        <div class="min-w-[260px] h-[320px] rounded-2xl bg-gradient-to-b from-cardbg to-darkbg border border-white/5 relative overflow-hidden flex flex-col justify-end p-4 snap-center shrink-0 group">
            <div class="absolute inset-0 flex flex-col items-center justify-center opacity-30 group-hover:opacity-10 transition-opacity">
                <i class="fa-regular fa-image text-3xl mb-2"></i>
                <p class="text-[10px] tracking-widest uppercase text-center w-2/3">Dark Throne Featured Banner</p>
            </div>
            <div class="relative z-10">
                <span data-i18n="genre_thriller" class="inline-block px-2 py-0.5 rounded text-[9px] font-bold tracking-wider bg-accent/20 text-accent uppercase mb-2 border border-accent/20">Thriller</span>
                <h3 class="font-bold text-lg mb-1 drop-shadow-md">Dark Throne</h3>
                <div class="flex items-center gap-2 text-xs text-textmuted">
                    <span>18 eps</span>
                    <span>•</span>
                    <span class="flex items-center gap-1"><i class="fa-solid fa-star text-yellow-500 text-[10px]"></i> 4.6</span>
                </div>
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-darkbg via-darkbg/40 to-transparent"></div>
        </div>
    </div>

    <!-- Trending Now -->
    <div class="flex justify-between items-end mb-4">
        <h2 data-i18n="trending_now" class="text-lg font-bold">Trending Now</h2>
        <a href="#" class="text-xs text-accent font-medium hover:underline"><span data-i18n="see_all">See all</span> <i class="fa-solid fa-arrow-right text-[10px] ml-0.5"></i></a>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <!-- Item 1 -->
        <div class="flex flex-col gap-2 cursor-pointer">
            <div class="w-full aspect-[3/4] rounded-xl bg-cardbg border border-white/5 flex flex-col items-center justify-center opacity-70 hover:opacity-100 transition-opacity relative overflow-hidden">
                <i class="fa-regular fa-image text-2xl mb-2 text-textmuted"></i>
                <p class="text-[9px] tracking-widest uppercase text-textmuted text-center">My CEO Husband</p>
            </div>
            <div>
                <h4 class="font-bold text-sm leading-tight mb-0.5 line-clamp-1">My CEO Husband</h4>
                <p class="text-xs text-textmuted">Romance • 32 eps</p>
            </div>
        </div>
        
        <!-- Item 2 -->
        <div class="flex flex-col gap-2 cursor-pointer">
            <div class="w-full aspect-[3/4] rounded-xl bg-cardbg border border-white/5 flex flex-col items-center justify-center opacity-70 hover:opacity-100 transition-opacity relative overflow-hidden">
                <i class="fa-regular fa-image text-2xl mb-2 text-textmuted"></i>
                <p class="text-[9px] tracking-widest uppercase text-textmuted text-center">Revenge Plan</p>
            </div>
            <div>
                <h4 class="font-bold text-sm leading-tight mb-0.5 line-clamp-1">Revenge Plan</h4>
                <p class="text-xs text-textmuted">Drama • 20 eps</p>
            </div>
        </div>

         <!-- Item 3 -->
         <div class="flex flex-col gap-2 cursor-pointer">
            <div class="w-full aspect-[3/4] rounded-xl bg-cardbg border border-white/5"></div>
        </div>
        <!-- Item 4 -->
        <div class="flex flex-col gap-2 cursor-pointer">
            <div class="w-full aspect-[3/4] rounded-xl bg-cardbg border border-white/5"></div>
        </div>
    </div>
</div>
