<!-- VIP View -->
<div class="px-5 pt-6 pb-4">
    <!-- Header -->
    <div class="flex justify-start items-center gap-3 mb-6">
        <div class="w-8 h-8 rounded-full bg-accent flex items-center justify-center">
            <i class="fa-solid fa-play text-darkbg text-sm ml-0.5"></i>
        </div>
        <h1 class="text-xl font-bold">VIP & Coins</h1>
    </div>

    <!-- VIP Feature Card -->
    <div class="bg-gradient-to-br from-[#0c2436] to-[#0A1420] border border-white/5 rounded-3xl p-5 mb-8 relative overflow-hidden">
        <!-- Decoration Circle -->
        <div class="absolute -right-8 -top-8 w-32 h-32 bg-accent/5 rounded-full blur-xl pointer-events-none"></div>
        
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-accent/20 flex items-center justify-center text-accent">
                <i class="fa-solid fa-crown"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white leading-tight">DramaStream VIP</h2>
                <p class="text-xs text-textmuted">Unlimited access to all content</p>
            </div>
        </div>
        
        <ul class="flex flex-col gap-2.5">
            <li class="flex items-start gap-2 text-sm text-gray-200">
                <i class="fa-solid fa-check text-accent text-xs mt-1 shrink-0"></i>
                <span>Unlock all episodes instantly</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-200">
                <i class="fa-solid fa-check text-accent text-xs mt-1 shrink-0"></i>
                <span>No ads experience</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-200">
                <i class="fa-solid fa-check text-accent text-xs mt-1 shrink-0"></i>
                <span>Early access to new dramas</span>
            </li>
            <li class="flex items-start gap-2 text-sm text-gray-200">
                <i class="fa-solid fa-check text-accent text-xs mt-1 shrink-0"></i>
                <span>Exclusive VIP badge</span>
            </li>
        </ul>
    </div>

    <!-- Choose Plan -->
    <h3 class="text-lg font-bold mb-4">Choose Plan</h3>
    
    <div class="flex flex-col gap-3 mb-8">
        <!-- Monthly Plan -->
        <div class="bg-cardbg border border-white/5 rounded-2xl p-4 flex justify-between items-center cursor-pointer transition-colors hover:border-white/20">
            <div>
                <h4 class="font-bold text-base mb-0.5">Monthly</h4>
                <p class="text-xs text-textmuted">+100 bonus coins</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-lg leading-tight">Rp 29.000</p>
                <p class="text-[10px] text-textmuted">/month</p>
            </div>
        </div>

        <!-- 3 Months Plan (Active) -->
        <div class="bg-cardbg border border-accent rounded-2xl p-4 flex justify-between items-center cursor-pointer relative shadow-[0_0_15px_rgba(0,208,182,0.1)]">
            <div class="absolute -top-2.5 right-6 bg-accent text-darkbg text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded shadow-sm">
                BEST VALUE
            </div>
            <div>
                <h4 class="font-bold text-base mb-0.5">3 Months</h4>
                <p class="text-xs text-textmuted">+350 bonus coins</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-lg leading-tight text-white">Rp 69.000</p>
                <p class="text-[10px] text-textmuted">/3 months</p>
            </div>
        </div>

        <!-- Annual Plan -->
        <div class="bg-cardbg border border-white/5 rounded-2xl p-4 flex justify-between items-center cursor-pointer relative transition-colors hover:border-white/20">
            <div class="absolute -top-2.5 right-6 bg-accent text-darkbg text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded shadow-sm">
                SAVE 43%
            </div>
            <div>
                <h4 class="font-bold text-base mb-0.5">Annual</h4>
                <p class="text-xs text-textmuted">+1200 bonus coins</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-lg leading-tight">Rp 199.000</p>
                <p class="text-[10px] text-textmuted">/year</p>
            </div>
        </div>
    </div>

    <!-- Buy Coins -->
    <h3 class="text-lg font-bold mb-4">Buy Coins</h3>
    
    <div class="grid grid-cols-2 gap-3 pb-8">
        <!-- Coin 1 -->
        <div class="bg-cardbg border border-white/5 rounded-2xl p-4 flex flex-col items-center justify-center cursor-pointer hover:border-white/20 transition-colors">
            <i class="fa-solid fa-coins text-yellow-500 text-3xl mb-3 drop-shadow-[0_2px_4px_rgba(234,179,8,0.3)]"></i>
            <h4 class="font-bold text-xl leading-none mb-1">50</h4>
            <p class="text-[10px] text-textmuted mb-2">coins</p>
            <p class="font-semibold text-accent text-sm">Rp 5.000</p>
        </div>

        <!-- Coin 2 (Popular) -->
        <div class="bg-cardbg border border-accent/50 rounded-2xl p-4 flex flex-col items-center justify-center cursor-pointer relative">
            <div class="absolute -top-2.5 left-1/2 -translate-x-1/2 bg-accent text-darkbg text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded shadow-sm whitespace-nowrap">
                POPULAR
            </div>
            <i class="fa-solid fa-coins text-yellow-500 text-3xl mb-3 drop-shadow-[0_2px_4px_rgba(234,179,8,0.3)]"></i>
            <h4 class="font-bold text-xl leading-none mb-1">150</h4>
            <p class="text-[10px] text-textmuted mb-2">coins</p>
            <p class="font-semibold text-accent text-sm">Rp 13.000</p>
        </div>
    </div>
</div>
