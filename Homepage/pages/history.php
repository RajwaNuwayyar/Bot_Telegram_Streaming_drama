<!-- History View -->
<div class="px-5 pt-6 pb-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-accent flex items-center justify-center">
                <i class="fa-solid fa-play text-darkbg text-sm ml-0.5"></i>
            </div>
            <h1 class="text-xl font-bold">Watch History</h1>
        </div>
        <button class="text-xs font-semibold text-accent uppercase tracking-wider hover:text-white transition-colors">Clear All</button>
    </div>

    <!-- Filter Pills -->
    <div class="flex gap-3 mb-6">
        <button class="px-5 py-2 rounded-full bg-accent text-darkbg font-semibold text-sm">All</button>
        <button class="px-5 py-2 rounded-full bg-cardbg border border-white/5 text-textmuted text-sm hover:text-white transition-colors">In Progress</button>
        <button class="px-5 py-2 rounded-full bg-cardbg border border-white/5 text-textmuted text-sm hover:text-white transition-colors">Completed</button>
    </div>

    <!-- History List -->
    <div class="flex flex-col gap-3">
        <!-- Item 1 (In Progress) -->
        <div class="bg-cardbg border border-white/5 rounded-2xl p-3 flex gap-4 items-center">
            <div class="w-20 h-16 rounded-xl bg-darkbg border border-white/5 flex flex-col items-center justify-center shrink-0">
                <i class="fa-regular fa-image text-textmuted text-sm mb-1"></i>
                <span class="text-[8px] uppercase tracking-widest text-textmuted">Thumb</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start mb-0.5">
                    <h4 class="font-bold text-sm truncate text-white">My CEO Husband</h4>
                    <span class="text-[10px] text-textmuted shrink-0 ml-2">2h ago</span>
                </div>
                <p class="text-xs text-textmuted truncate mb-2">Ep 12 - "The Proposal"</p>
                
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-1 bg-darkbg rounded-full overflow-hidden">
                        <div class="h-full bg-accent rounded-full" style="width: 65%"></div>
                    </div>
                    <span class="text-[10px] text-textmuted w-14">65% watched</span>
                </div>
                
                <div class="flex justify-end mt-1">
                    <span class="px-2 py-0.5 rounded text-[8px] border border-white/10 text-textmuted uppercase tracking-wider">Romance</span>
                </div>
            </div>
        </div>

        <!-- Item 2 (In Progress) -->
        <div class="bg-cardbg border border-white/5 rounded-2xl p-3 flex gap-4 items-center">
            <div class="w-20 h-16 rounded-xl bg-darkbg border border-white/5 flex flex-col items-center justify-center shrink-0">
                <i class="fa-regular fa-image text-textmuted text-sm mb-1"></i>
                <span class="text-[8px] uppercase tracking-widest text-textmuted">Thumb</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start mb-0.5">
                    <h4 class="font-bold text-sm truncate text-white">Secret Love</h4>
                    <span class="text-[10px] text-textmuted shrink-0 ml-2">Yesterday</span>
                </div>
                <p class="text-xs text-textmuted truncate mb-2">Ep 2 - "The Office Secret"</p>
                
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-1 bg-darkbg rounded-full overflow-hidden">
                        <div class="h-full bg-accent rounded-full" style="width: 40%"></div>
                    </div>
                    <span class="text-[10px] text-textmuted w-14">40% watched</span>
                </div>
                
                <div class="flex justify-end mt-1">
                    <span class="px-2 py-0.5 rounded text-[8px] border border-white/10 text-textmuted uppercase tracking-wider">Romance</span>
                </div>
            </div>
        </div>

        <!-- Item 3 (Completed) -->
        <div class="bg-cardbg border border-white/5 rounded-2xl p-3 flex gap-4 items-center opacity-80">
            <div class="w-20 h-16 rounded-xl bg-darkbg border border-white/5 flex items-center justify-center shrink-0 relative overflow-hidden">
                <div class="absolute inset-0 bg-darkbg opacity-60"></div>
                <div class="w-6 h-6 rounded-full border border-accent text-accent flex items-center justify-center z-10">
                    <i class="fa-solid fa-check text-xs"></i>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start mb-0.5">
                    <h4 class="font-bold text-sm truncate text-white">Dark Throne</h4>
                    <span class="text-[10px] text-textmuted shrink-0 ml-2">3 days ago</span>
                </div>
                <p class="text-xs text-textmuted truncate mb-2">Ep 5 - "Betrayal"</p>
                
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-1 bg-darkbg rounded-full overflow-hidden">
                        <div class="h-full bg-accent rounded-full" style="width: 100%"></div>
                    </div>
                    <span class="text-[10px] text-accent font-medium w-14">Completed</span>
                </div>
                
                <div class="flex justify-end mt-1">
                    <span class="px-2 py-0.5 rounded text-[8px] border border-white/10 text-textmuted uppercase tracking-wider">Thriller</span>
                </div>
            </div>
        </div>

        <!-- Item 4 (Completed) -->
        <div class="bg-cardbg border border-white/5 rounded-2xl p-3 flex gap-4 items-center opacity-80">
            <div class="w-20 h-16 rounded-xl bg-darkbg border border-white/5 flex items-center justify-center shrink-0 relative overflow-hidden">
                <div class="absolute inset-0 bg-darkbg opacity-60"></div>
                <div class="w-6 h-6 rounded-full border border-accent text-accent flex items-center justify-center z-10">
                    <i class="fa-solid fa-check text-xs"></i>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start mb-0.5">
                    <h4 class="font-bold text-sm truncate text-white">Forbidden Love</h4>
                    <span class="text-[10px] text-textmuted shrink-0 ml-2">1 week ago</span>
                </div>
                <p class="text-xs text-textmuted truncate mb-2">Ep 8 - "Goodbye"</p>
                
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-1 bg-darkbg rounded-full overflow-hidden">
                        <div class="h-full bg-accent rounded-full" style="width: 100%"></div>
                    </div>
                    <span class="text-[10px] text-accent font-medium w-14">Completed</span>
                </div>
                
                <div class="flex justify-end mt-1">
                    <span class="px-2 py-0.5 rounded text-[8px] border border-white/10 text-textmuted uppercase tracking-wider">Drama</span>
                </div>
            </div>
        </div>

        <!-- Item 5 (In Progress) -->
        <div class="bg-cardbg border border-white/5 rounded-2xl p-3 flex gap-4 items-center">
            <div class="w-20 h-16 rounded-xl bg-darkbg border border-white/5 flex flex-col items-center justify-center shrink-0">
                <i class="fa-regular fa-image text-textmuted text-sm mb-1"></i>
                <span class="text-[8px] uppercase tracking-widest text-textmuted">Thumb</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start mb-0.5">
                    <h4 class="font-bold text-sm truncate text-white">Night Shift</h4>
                    <span class="text-[10px] text-textmuted shrink-0 ml-2">1 week ago</span>
                </div>
                <p class="text-xs text-textmuted truncate mb-2">Ep 1 - "First Night"</p>
                
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-1 bg-darkbg rounded-full overflow-hidden">
                        <div class="h-full bg-accent rounded-full" style="width: 20%"></div>
                    </div>
                    <span class="text-[10px] text-textmuted w-14">20% watched</span>
                </div>
                
                <div class="flex justify-end mt-1">
                    <span class="px-2 py-0.5 rounded text-[8px] border border-white/10 text-textmuted uppercase tracking-wider">Thriller</span>
                </div>
            </div>
        </div>

    </div>
</div>
