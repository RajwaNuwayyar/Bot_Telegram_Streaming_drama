<!-- VIP View -->
<div class="px-5 pt-6 pb-4">
    <!-- Header -->
    <div class="flex justify-start items-center gap-3 mb-6">
        <div class="w-8 h-8 rounded-full bg-accent flex items-center justify-center">
            <i class="fa-solid fa-crown text-darkbg text-sm"></i>
        </div>
        <h1 data-i18n="vip_title" class="text-xl font-bold">VIP Member</h1>
    </div>

    <!-- VIP Feature Card -->
    <div class="bg-gradient-to-br from-[#0c2436] to-[#0A1420] border border-accent/20 rounded-3xl p-5 mb-6 relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute -right-8 -top-8 w-32 h-32 bg-accent/5 rounded-full blur-xl pointer-events-none"></div>
        <div class="absolute -left-4 -bottom-4 w-24 h-24 bg-accent/5 rounded-full blur-xl pointer-events-none"></div>

        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-accent/20 flex items-center justify-center text-accent">
                <i class="fa-solid fa-crown"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white leading-tight">DramaStream VIP</h2>
                <p data-i18n="vip_subtitle" class="text-xs text-textmuted">Full access to all drama content</p>
            </div>
        </div>

        <ul class="flex flex-col gap-2.5">
            <li class="flex items-start gap-2 text-sm text-gray-200">
                <i class="fa-solid fa-check text-accent text-xs mt-1 shrink-0"></i>
                <span data-i18n="vip_feat_1">Unlimited access to all episodes</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-200">
                <i class="fa-solid fa-check text-accent text-xs mt-1 shrink-0"></i>
                <span data-i18n="vip_feat_2">Ad-free streaming experience</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-200">
                <i class="fa-solid fa-check text-accent text-xs mt-1 shrink-0"></i>
                <span data-i18n="vip_feat_3">Early access to new drama releases</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-200">
                <i class="fa-solid fa-check text-accent text-xs mt-1 shrink-0"></i>
                <span data-i18n="vip_feat_4">Exclusive VIP profile badge</span>
            </li>
        </ul>
    </div>

    <!-- Choose Plan -->
    <h3 data-i18n="choose_plan" class="text-sm font-bold mb-3 text-textmuted uppercase tracking-wider">Choose VIP Plan</h3>

    <div class="flex flex-col gap-3 pb-8">

        <!-- VIP 1 Hari -->
        <div onclick="openQrisModal('VIP 1 Hari', 3000, 1, 1)" class="bg-cardbg border border-white/5 rounded-2xl p-4 flex justify-between items-center cursor-pointer transition-all hover:border-accent/40 hover:shadow-[0_0_10px_rgba(0,208,182,0.08)] active:scale-[0.98]">
            <div>
                <h4 data-i18n="plan_1d" class="font-bold text-base mb-0.5">VIP 1 Day</h4>
                <p data-i18n="plan_1d_desc" class="text-xs text-textmuted">Full access for 1 day</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-lg leading-tight text-white">Rp 3.000</p>
                <p data-i18n="per_1d" class="text-[10px] text-textmuted">/ 1 day</p>
            </div>
        </div>

        <!-- VIP 3 Hari -->
        <div onclick="openQrisModal('VIP 3 Hari', 6000, 2, 3)" class="bg-cardbg border border-white/5 rounded-2xl p-4 flex justify-between items-center cursor-pointer transition-all hover:border-accent/40 hover:shadow-[0_0_10px_rgba(0,208,182,0.08)] active:scale-[0.98]">
            <div>
                <h4 data-i18n="plan_3d" class="font-bold text-base mb-0.5">VIP 3 Days</h4>
                <p class="text-xs text-textmuted">~Rp 2.000/day</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-lg leading-tight text-white">Rp 6.000</p>
                <p data-i18n="per_3d" class="text-[10px] text-textmuted">/ 3 days</p>
            </div>
        </div>

        <!-- VIP 7 Hari -->
        <div onclick="openQrisModal('VIP 7 Hari', 10000, 3, 7)" class="bg-cardbg border border-white/5 rounded-2xl p-4 flex justify-between items-center cursor-pointer transition-all hover:border-accent/40 hover:shadow-[0_0_10px_rgba(0,208,182,0.08)] active:scale-[0.98]">
            <div>
                <h4 data-i18n="plan_7d" class="font-bold text-base mb-0.5">VIP 7 Days</h4>
                <p class="text-xs text-textmuted">~Rp 1.429/day</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-lg leading-tight text-white">Rp 10.000</p>
                <p data-i18n="per_7d" class="text-[10px] text-textmuted">/ 7 days</p>
            </div>
        </div>

        <!-- VIP 15 Hari -->
        <div onclick="openQrisModal('VIP 15 Hari', 20000, 4, 15)" class="bg-cardbg border border-white/5 rounded-2xl p-4 flex justify-between items-center cursor-pointer transition-all hover:border-accent/40 hover:shadow-[0_0_10px_rgba(0,208,182,0.08)] active:scale-[0.98]">
            <div>
                <h4 data-i18n="plan_15d" class="font-bold text-base mb-0.5">VIP 15 Days</h4>
                <p class="text-xs text-textmuted">~Rp 1.333/day</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-lg leading-tight text-white">Rp 20.000</p>
                <p data-i18n="per_15d" class="text-[10px] text-textmuted">/ 15 days</p>
            </div>
        </div>

        <!-- VIP 30 Hari (Best Value) -->
        <div onclick="openQrisModal('VIP 30 Hari', 35000, 5, 30)" class="bg-cardbg border border-accent rounded-2xl p-4 flex justify-between items-center cursor-pointer relative shadow-[0_0_15px_rgba(0,208,182,0.1)] active:scale-[0.98]">
            <div data-i18n="best_value" class="absolute -top-2.5 right-6 bg-accent text-darkbg text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded shadow-sm">
                BEST VALUE
            </div>
            <div>
                <h4 data-i18n="plan_30d" class="font-bold text-base mb-0.5">VIP 30 Days</h4>
                <p class="text-xs text-textmuted">~Rp 1.167/day</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-lg leading-tight text-white">Rp 35.000</p>
                <p data-i18n="per_30d" class="text-[10px] text-textmuted">/ 30 days</p>
            </div>
        </div>

        <!-- VIP 90 Hari -->
        <div onclick="openQrisModal('VIP 90 Hari', 90000, 6, 90)" class="bg-cardbg border border-white/5 rounded-2xl p-4 flex justify-between items-center cursor-pointer relative transition-all hover:border-accent/40 hover:shadow-[0_0_10px_rgba(0,208,182,0.08)] active:scale-[0.98]">
            <div data-i18n="save_tag" class="absolute -top-2.5 right-6 bg-accent/80 text-darkbg text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded shadow-sm">
                SAVINGS
            </div>
            <div>
                <h4 data-i18n="plan_90d" class="font-bold text-base mb-0.5">VIP 90 Days</h4>
                <p class="text-xs text-textmuted">~Rp 1.000/day</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-lg leading-tight text-white">Rp 90.000</p>
                <p data-i18n="per_90d" class="text-[10px] text-textmuted">/ 90 days</p>
            </div>
        </div>

        <!-- VIP 365 Hari -->
        <div onclick="openQrisModal('VIP 365 Hari', 300000, 7, 365)" class="bg-cardbg border border-white/5 rounded-2xl p-4 flex justify-between items-center cursor-pointer relative transition-all hover:border-accent/40 hover:shadow-[0_0_10px_rgba(0,208,182,0.08)] active:scale-[0.98]">
            <div data-i18n="save_33" class="absolute -top-2.5 right-6 bg-yellow-500 text-darkbg text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded shadow-sm">
                SAVE 33%
            </div>
            <div>
                <h4 data-i18n="plan_365d" class="font-bold text-base mb-0.5">VIP 365 Days</h4>
                <p class="text-xs text-textmuted">~Rp 822/day</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-lg leading-tight text-white">Rp 300.000</p>
                <p data-i18n="per_1y" class="text-[10px] text-textmuted">/ 1 year</p>
            </div>
        </div>

    </div>
</div>
