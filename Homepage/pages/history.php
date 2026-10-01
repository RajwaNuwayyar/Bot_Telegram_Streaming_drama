<!-- History View -->
<div class="px-5 pt-6 pb-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-accent flex items-center justify-center">
                <i class="fa-solid fa-play text-darkbg text-sm ml-0.5"></i>
            </div>
            <h1 data-i18n="history_title" class="text-xl font-bold">Watch History</h1>
        </div>
        <button onclick="confirmClearAll()" data-i18n="clear_all" class="text-xs font-semibold text-accent uppercase tracking-wider hover:text-white transition-colors">Clear All</button>
    </div>

    <!-- Filter Pills -->
    <div class="flex gap-3 mb-6">
        <button onclick="filterHistory('all', this)" data-i18n="filter_all" class="history-filter-btn px-5 py-2 rounded-full bg-accent text-darkbg font-semibold text-sm">All</button>
        <button onclick="filterHistory('progress', this)" data-i18n="filter_in_progress" class="history-filter-btn px-5 py-2 rounded-full bg-cardbg border border-white/5 text-textmuted text-sm hover:text-white transition-colors">In Progress</button>
        <button onclick="filterHistory('completed', this)" data-i18n="filter_completed" class="history-filter-btn px-5 py-2 rounded-full bg-cardbg border border-white/5 text-textmuted text-sm hover:text-white transition-colors">Completed</button>
    </div>

    <!-- History List -->
    <div class="flex flex-col gap-3" id="history-list">

        <!-- Item 1 (In Progress) -->
        <div data-status="progress" class="history-item bg-cardbg border border-white/5 rounded-2xl p-3 flex gap-4 items-center cursor-pointer hover:border-white/20 transition-colors active:scale-[0.99]">
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
        <div data-status="progress" class="history-item bg-cardbg border border-white/5 rounded-2xl p-3 flex gap-4 items-center cursor-pointer hover:border-white/20 transition-colors active:scale-[0.99]">
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
        <div data-status="completed" class="history-item bg-cardbg border border-white/5 rounded-2xl p-3 flex gap-4 items-center opacity-80 cursor-pointer hover:border-white/20 hover:opacity-100 transition-all active:scale-[0.99]">
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
        <div data-status="completed" class="history-item bg-cardbg border border-white/5 rounded-2xl p-3 flex gap-4 items-center opacity-80 cursor-pointer hover:border-white/20 hover:opacity-100 transition-all active:scale-[0.99]">
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
        <div data-status="progress" class="history-item bg-cardbg border border-white/5 rounded-2xl p-3 flex gap-4 items-center cursor-pointer hover:border-white/20 transition-colors active:scale-[0.99]">
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

    <!-- Empty State (hidden by default) -->
    <div id="history-empty" class="hidden flex flex-col items-center justify-center py-16 text-center">
        <div class="w-14 h-14 rounded-full bg-cardbg border border-white/5 flex items-center justify-center mb-4">
            <i class="fa-regular fa-clock text-2xl text-textmuted"></i>
        </div>
        <p class="text-sm font-semibold text-white mb-1">Tidak ada tontonan</p>
        <p class="text-xs text-textmuted">Belum ada drama di kategori ini.</p>
    </div>
</div>

<script>
function filterHistory(status, btn) {
    // Update active button style
    document.querySelectorAll('.history-filter-btn').forEach(b => {
        b.classList.remove('bg-accent', 'text-darkbg', 'font-semibold');
        b.classList.add('bg-cardbg', 'border', 'border-white/5', 'text-textmuted');
    });
    btn.classList.add('bg-accent', 'text-darkbg', 'font-semibold');
    btn.classList.remove('bg-cardbg', 'border', 'border-white/5', 'text-textmuted');

    // Filter items
    const items = document.querySelectorAll('.history-item');
    let visible = 0;
    items.forEach(item => {
        if (status === 'all' || item.dataset.status === status) {
            item.style.display = 'flex';
            visible++;
        } else {
            item.style.display = 'none';
        }
    });

    // Tampilkan empty state jika tidak ada item
    const empty = document.getElementById('history-empty');
    if (visible === 0) {
        empty.classList.remove('hidden');
    } else {
        empty.classList.add('hidden');
    }
}

function confirmClearAll() {
    if (confirm('Hapus semua riwayat tontonan?')) {
        document.querySelectorAll('.history-item').forEach(item => item.remove());
        document.getElementById('history-empty').classList.remove('hidden');
    }
}
</script>


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
