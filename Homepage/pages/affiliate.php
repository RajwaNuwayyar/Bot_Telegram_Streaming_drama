<!-- Affiliate View -->
<div class="px-5 pt-6 pb-4">
    <!-- Header -->
    <div class="flex justify-start items-center gap-3 mb-6">
        <a href="?page=profile" class="w-8 h-8 rounded-full bg-cardbg border border-white/5 flex items-center justify-center text-textmuted hover:text-white transition-colors">
            <i class="fa-solid fa-chevron-left text-sm"></i>
        </a>
        <h1 data-i18n="affiliate_title" class="text-xl font-bold">Affiliate Program</h1>
    </div>

    <!-- Balance Card -->
    <div class="bg-gradient-to-br from-emerald-900 to-emerald-950 border border-emerald-500/30 rounded-3xl p-6 mb-6 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 text-[120px] text-emerald-500">
            <i class="fa-solid fa-users"></i>
        </div>
        
        <div class="relative z-10">
            <p data-i18n="total_commission" class="text-xs text-emerald-200 mb-1 uppercase tracking-widest font-semibold">Total Commission</p>
            <div class="flex items-end gap-2 mb-2">
                <h2 class="text-3xl font-bold text-white leading-none">Rp 120.000</h2>
            </div>
            
            <p data-i18n="ready_withdraw" class="text-[11px] text-emerald-200 mb-6">Ready to withdraw</p>
            
            <button data-i18n="btn_withdraw" class="w-full bg-emerald-500 hover:bg-emerald-400 text-darkbg font-bold py-3 rounded-xl shadow-lg transition-colors">
                Withdraw Funds
            </button>
        </div>
    </div>

    <!-- Referral Link section -->
    <h3 data-i18n="referral_link" class="text-sm font-bold mb-3 text-textmuted uppercase tracking-wider">Your Referral Link</h3>
    <div class="bg-cardbg border border-white/5 rounded-2xl p-4 flex items-center justify-between mb-8 gap-3">
        <div class="flex-1 min-w-0">
            <p data-i18n="share_link_desc" class="text-xs text-textmuted mb-1">Share this link to earn 18% commission</p>
            <div class="text-sm text-white font-mono bg-darkbg p-2 rounded-lg truncate border border-white/5 select-all">
                t.me/DramaStreamBot?start=REF123
            </div>
        </div>
        <button class="w-12 h-12 shrink-0 bg-emerald-500/10 text-emerald-500 rounded-xl flex items-center justify-center hover:bg-emerald-500/20 transition-colors">
            <i class="fa-regular fa-copy text-lg"></i>
        </button>
    </div>

    <!-- Commission Levels -->
    <h3 data-i18n="commission_tiers" class="text-sm font-bold mb-3 text-textmuted uppercase tracking-wider">Commission Tiers</h3>
    <div class="flex flex-col gap-3 pb-8">
        <div class="bg-cardbg border border-white/5 rounded-2xl p-4 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-sm text-white mb-0.5">Level 1 - Starter</h4>
                <p data-i18n="all_users" class="text-[11px] text-textmuted">All users</p>
            </div>
            <div class="text-right">
                <span class="text-emerald-500 font-bold">10%</span>
            </div>
        </div>
        
        <div class="bg-cardbg border border-white/5 rounded-2xl p-4 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-sm text-white mb-0.5">Level 2 - Pro</h4>
                <p data-i18n="sales_req_1" class="text-[11px] text-textmuted">> Rp 1M referral sales</p>
            </div>
            <div class="text-right">
                <span class="text-emerald-500 font-bold">12%</span>
            </div>
        </div>

        <div class="bg-cardbg border border-emerald-500/30 rounded-2xl p-4 flex items-center justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-1.5 h-full bg-emerald-500"></div>
            <div>
                <h4 class="font-bold text-sm text-white mb-0.5 flex items-center gap-2">Level 3 - Elite <span data-i18n="current_tier" class="bg-emerald-500/20 text-emerald-500 px-1.5 py-0.5 rounded text-[8px] uppercase tracking-wider">Current</span></h4>
                <p data-i18n="sales_req_3" class="text-[11px] text-textmuted">> Rp 3M referral sales</p>
            </div>
            <div class="text-right mr-3">
                <span class="text-emerald-500 font-bold text-lg">15%</span>
            </div>
        </div>

        <div class="bg-cardbg border border-white/5 rounded-2xl p-4 flex items-center justify-between opacity-50">
            <div>
                <h4 class="font-bold text-sm text-white mb-0.5">Level 4 - Master</h4>
                <p data-i18n="sales_req_5" class="text-[11px] text-textmuted">> Rp 5M referral sales</p>
            </div>
            <div class="text-right">
                <span class="text-emerald-500 font-bold">18%</span>
                <i class="fa-solid fa-lock text-[10px] ml-1 text-textmuted"></i>
            </div>
        </div>
    </div>
</div>
