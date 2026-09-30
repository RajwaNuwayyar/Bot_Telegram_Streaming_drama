<!-- Profile View -->
<div class="px-5 pt-6 pb-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-accent flex items-center justify-center shrink-0">
                <i class="fa-solid fa-play text-darkbg text-sm ml-0.5"></i>
            </div>
            <h1 class="text-xl font-bold">Profile</h1>
        </div>
        <button class="w-8 h-8 rounded-full bg-cardbg border border-white/5 flex items-center justify-center text-textmuted hover:text-white transition-colors">
            <i class="fa-solid fa-circle-exclamation text-sm"></i>
        </button>
    </div>

    <!-- User Info Card -->
    <div class="bg-cardbg border border-white/5 rounded-3xl p-5 mb-5 flex items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-darkbg border border-white/10 flex items-center justify-center shrink-0 text-textmuted text-2xl">
            <i class="fa-regular fa-user"></i>
        </div>
        <div class="flex-1 min-w-0">
            <h2 id="tg-username" class="text-lg font-bold truncate mb-1 text-white">DramaFan99</h2>
            <p class="text-[11px] text-textmuted mb-2 truncate"><span id="tg-handle">@dramafan99</span> • Joined Sep 2024</p>
            <div class="flex items-center gap-2">
                <div class="bg-accent/20 border border-accent/30 text-accent px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                    <i class="fa-solid fa-crown text-[8px]"></i> VIP
                </div>
                <span class="text-[10px] text-textmuted">Expires Dec 15, 2024</span>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-cardbg border border-white/5 rounded-2xl py-3 px-2 flex flex-col items-center justify-center">
            <h3 class="text-xl font-bold text-white mb-1">47</h3>
            <p class="text-[10px] text-textmuted">Watched</p>
        </div>
        <div class="bg-cardbg border border-white/5 rounded-2xl py-3 px-2 flex flex-col items-center justify-center">
            <h3 class="text-xl font-bold text-white mb-1">23</h3>
            <p class="text-[10px] text-textmuted">Completed</p>
        </div>
        <div class="bg-cardbg border border-white/5 rounded-2xl py-3 px-2 flex flex-col items-center justify-center">
            <h3 class="text-xl font-bold text-white mb-1">120</h3>
            <p class="text-[10px] text-textmuted">Coins</p>
        </div>
    </div>

    <!-- Highlighted Action (Manage VIP) -->
    <a href="?page=vip" class="block bg-cardbg border border-accent/30 rounded-2xl p-4 mb-4 flex items-center gap-4 hover:border-accent/60 transition-colors">
        <div class="w-8 h-8 rounded-full bg-accent/10 flex items-center justify-center shrink-0 text-accent">
            <i class="fa-solid fa-crown text-sm"></i>
        </div>
        <div class="flex-1">
            <h4 class="font-bold text-sm text-white mb-0.5">Manage VIP</h4>
            <p class="text-[11px] text-textmuted">Renew or upgrade your plan</p>
        </div>
        <i class="fa-solid fa-chevron-right text-textmuted text-xs"></i>
    </a>

    <!-- Menu List -->
    <div class="bg-cardbg border border-white/5 rounded-3xl p-2 mb-6">
        
        <!-- Menu Item 0 (Affiliate) -->
        <a href="?page=affiliate" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-white/5 transition-colors">
            <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center shrink-0 text-emerald-500">
                <i class="fa-solid fa-users-viewfinder text-sm"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-sm text-white mb-0.5">Affiliate Program</h4>
                <p class="text-[11px] text-textmuted">Earn up to 18% commission</p>
            </div>
            <i class="fa-solid fa-chevron-right text-textmuted text-xs"></i>
        </a>

        <!-- Divider -->
        <div class="h-px bg-white/5 ml-14 mr-4"></div>

        <!-- Menu Item 1 -->
        <a href="?page=vip" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-white/5 transition-colors">
            <div class="w-8 h-8 rounded-full bg-yellow-500/10 flex items-center justify-center shrink-0 text-yellow-500">
                <i class="fa-solid fa-coins text-sm"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-sm text-white mb-0.5">Buy Coins</h4>
                <p class="text-[11px] text-textmuted">Balance: 120 coins</p>
            </div>
            <i class="fa-solid fa-chevron-right text-textmuted text-xs"></i>
        </a>

        <!-- Divider -->
        <div class="h-px bg-white/5 ml-14 mr-4"></div>

        <!-- Menu Item 2 -->
        <a href="#" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-white/5 transition-colors">
            <div class="w-8 h-8 rounded-full bg-orange-500/10 flex items-center justify-center shrink-0 text-orange-500">
                <i class="fa-solid fa-receipt text-sm"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-sm text-white mb-0.5">Transaction History</h4>
                <p class="text-[11px] text-textmuted">View all purchases</p>
            </div>
            <i class="fa-solid fa-chevron-right text-textmuted text-xs"></i>
        </a>

        <!-- Divider -->
        <div class="h-px bg-white/5 ml-14 mr-4"></div>

        <!-- Menu Item 3 -->
        <a href="#" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-white/5 transition-colors">
            <div class="w-8 h-8 rounded-full bg-red-500/10 flex items-center justify-center shrink-0 text-red-500">
                <i class="fa-solid fa-bell text-sm"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-sm text-white mb-0.5">Notifications</h4>
                <p class="text-[11px] text-textmuted">Manage alerts</p>
            </div>
            <i class="fa-solid fa-chevron-right text-textmuted text-xs"></i>
        </a>

        <!-- Divider -->
        <div class="h-px bg-white/5 ml-14 mr-4"></div>

        <!-- Menu Item 4 -->
        <a href="#" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-white/5 transition-colors">
            <div class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center shrink-0 text-blue-500">
                <i class="fa-solid fa-globe text-sm"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-sm text-white mb-0.5">Language</h4>
                <p class="text-[11px] text-textmuted">English</p>
            </div>
            <i class="fa-solid fa-chevron-right text-textmuted text-xs"></i>
        </a>

        <!-- Divider -->
        <div class="h-px bg-white/5 ml-14 mr-4"></div>

        <!-- Menu Item 5 -->
        <a href="#" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-white/5 transition-colors">
            <div class="w-8 h-8 rounded-full bg-green-500/10 flex items-center justify-center shrink-0 text-green-500">
                <i class="fa-solid fa-lock text-sm"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-sm text-white mb-0.5">Privacy</h4>
                <p class="text-[11px] text-textmuted">Data & permissions</p>
            </div>
            <i class="fa-solid fa-chevron-right text-textmuted text-xs"></i>
        </a>

        <!-- Divider -->
        <div class="h-px bg-white/5 ml-14 mr-4"></div>

        <!-- Menu Item 6 -->
        <a href="#" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-white/5 transition-colors">
            <div class="w-8 h-8 rounded-full bg-pink-500/10 flex items-center justify-center shrink-0 text-pink-500">
                <i class="fa-solid fa-circle-question text-sm"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-sm text-white mb-0.5">Help & Support</h4>
                <p class="text-[11px] text-textmuted">FAQ, contact us</p>
            </div>
            <i class="fa-solid fa-chevron-right text-textmuted text-xs"></i>
        </a>

    </div>
</div>
