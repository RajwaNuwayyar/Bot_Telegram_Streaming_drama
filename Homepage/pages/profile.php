<!-- Profile View -->
<div class="px-5 pt-6 pb-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-accent flex items-center justify-center shrink-0">
                <i class="fa-solid fa-play text-darkbg text-sm ml-0.5"></i>
            </div>
            <h1 data-i18n="profile_title" class="text-xl font-bold">Profile</h1>
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
            <p class="text-[11px] text-textmuted mb-2 truncate"><span id="tg-handle">@dramafan99</span> • <span data-i18n="joined">Joined</span> Sep 2024</p>
            <div class="flex items-center gap-2">
                <div class="bg-accent/20 border border-accent/30 text-accent px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                    <i class="fa-solid fa-crown text-[8px]"></i> VIP
                </div>
                <span class="text-[10px] text-textmuted"><span data-i18n="expires">Expires</span> Dec 15, 2024</span>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="bg-cardbg border border-white/5 rounded-2xl py-3 px-2 flex flex-col items-center justify-center">
            <h3 class="text-xl font-bold text-white mb-1">47</h3>
            <p data-i18n="watched" class="text-[10px] text-textmuted">Watched</p>
        </div>
        <div class="bg-cardbg border border-white/5 rounded-2xl py-3 px-2 flex flex-col items-center justify-center">
            <h3 class="text-xl font-bold text-white mb-1">23</h3>
            <p data-i18n="completed" class="text-[10px] text-textmuted">Completed</p>
        </div>
    </div>

    <!-- Highlighted Action (Manage VIP) -->
    <a href="?page=vip" class="block bg-cardbg border border-accent/30 rounded-2xl p-4 mb-4 flex items-center gap-4 hover:border-accent/60 transition-colors">
        <div class="w-8 h-8 rounded-full bg-accent/10 flex items-center justify-center shrink-0 text-accent">
            <i class="fa-solid fa-crown text-sm"></i>
        </div>
        <div class="flex-1">
            <h4 data-i18n="manage_vip" class="font-bold text-sm text-white mb-0.5">Manage VIP</h4>
            <p data-i18n="renew_upgrade" class="text-[11px] text-textmuted">Renew or upgrade your plan</p>
        </div>
        <i class="fa-solid fa-chevron-right text-textmuted text-xs"></i>
    </a>

    <!-- Menu List -->
    <div class="bg-cardbg border border-white/5 rounded-3xl p-2 mb-6">

        <!-- Menu Item: Affiliate -->
        <a href="?page=affiliate" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-white/5 transition-colors">
            <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center shrink-0 text-emerald-500">
                <i class="fa-solid fa-users-viewfinder text-sm"></i>
            </div>
            <div class="flex-1">
                <h4 data-i18n="affiliate_program" class="font-bold text-sm text-white mb-0.5">Affiliate Program</h4>
                <p data-i18n="earn_commission" class="text-[11px] text-textmuted">Earn up to 18% commission</p>
            </div>
            <i class="fa-solid fa-chevron-right text-textmuted text-xs"></i>
        </a>

        <!-- Divider -->
        <div class="h-px bg-white/5 ml-14 mr-4"></div>

        <!-- Menu Item: Language -->
        <div onclick="openLanguageModal()" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-white/5 transition-colors cursor-pointer">
            <div class="w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center shrink-0 text-blue-500">
                <i class="fa-solid fa-globe text-sm"></i>
            </div>
            <div class="flex-1">
                <h4 data-i18n="language" class="font-bold text-sm text-white mb-0.5">Language</h4>
                <p data-i18n="current_lang" id="profile-lang-label" class="text-[11px] text-textmuted">English</p>
            </div>
            <i class="fa-solid fa-chevron-right text-textmuted text-xs"></i>
        </div>

    </div>
</div>

<!-- Modal Dialog: Select Language -->
<div id="language-modal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-end sm:items-center justify-center p-4 transition-all">
    <div class="bg-cardbg border border-white/10 rounded-3xl w-full max-w-sm overflow-hidden p-5 shadow-2xl animate-in slide-in-from-bottom duration-200">
        <div class="flex justify-between items-center mb-4">
            <h3 data-i18n="select_language" class="text-lg font-bold text-white">Select Language</h3>
            <button onclick="closeLanguageModal()" class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-textmuted hover:text-white transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="flex flex-col gap-3 mb-6">
            <!-- English Option -->
            <div id="lang-option-en" onclick="selectLang('en')" class="flex items-center justify-between p-4 rounded-2xl bg-darkbg border border-white/5 cursor-pointer hover:border-accent/40 transition-all">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🇺🇸</span>
                    <div>
                        <p data-i18n="lang_en" class="font-bold text-sm text-white">English</p>
                        <p class="text-[10px] text-textmuted">Default</p>
                    </div>
                </div>
                <i id="lang-check-en" class="fa-solid fa-check text-accent text-lg"></i>
            </div>

            <!-- Bahasa Indonesia Option -->
            <div id="lang-option-id" onclick="selectLang('id')" class="flex items-center justify-between p-4 rounded-2xl bg-darkbg border border-white/5 cursor-pointer hover:border-accent/40 transition-all">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🇮🇩</span>
                    <div>
                        <p data-i18n="lang_id" class="font-bold text-sm text-white">Bahasa Indonesia</p>
                        <p class="text-[10px] text-textmuted">Indonesian</p>
                    </div>
                </div>
                <i id="lang-check-id" class="fa-solid fa-check text-accent text-lg hidden"></i>
            </div>
        </div>

        <button onclick="closeLanguageModal()" data-i18n="cancel" class="w-full py-3 rounded-xl bg-white/5 hover:bg-white/10 text-white font-semibold text-sm transition-colors">
            Cancel
        </button>
    </div>
</div>

<script>
    function openLanguageModal() {
        const modal = document.getElementById('language-modal');
        if (modal) modal.classList.remove('hidden');
        if (typeof applyTranslations === 'function') applyTranslations();
    }

    function closeLanguageModal() {
        const modal = document.getElementById('language-modal');
        if (modal) modal.classList.add('hidden');
    }

    function selectLang(lang) {
        if (typeof setLanguage === 'function') {
            setLanguage(lang);
        }
        closeLanguageModal();
    }
</script>
