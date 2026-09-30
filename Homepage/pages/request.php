<!-- Request Drama View -->
<div class="px-5 pt-6 pb-4">
    <!-- Header -->
    <div class="flex justify-start items-center gap-3 mb-6">
        <a href="?page=home" class="w-8 h-8 rounded-full bg-cardbg border border-white/5 flex items-center justify-center text-textmuted hover:text-white transition-colors">
            <i class="fa-solid fa-chevron-left text-sm"></i>
        </a>
        <h1 class="text-xl font-bold">Request Drama</h1>
    </div>

    <!-- Info Banner -->
    <div class="bg-cardbg border border-accent/20 rounded-2xl p-4 mb-8 flex gap-4 items-start">
        <div class="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center shrink-0 text-accent">
            <i class="fa-solid fa-circle-info"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-white mb-1">Can't find what you're looking for?</h3>
            <p class="text-xs text-textmuted mb-2 leading-relaxed">Submit a request and our admin will try to add it to the catalog as soon as possible. VIP members get priority processing!</p>
            <div class="flex items-center gap-2">
                <span class="bg-darkbg px-2 py-1 rounded border border-white/5 text-[9px] uppercase tracking-wider text-textmuted font-semibold">Max 2 requests/day</span>
            </div>
        </div>
    </div>

    <!-- Request Form -->
    <h3 class="text-sm font-bold mb-4 text-textmuted uppercase tracking-wider">Submit Request</h3>
    <form class="flex flex-col gap-4 mb-8" onsubmit="event.preventDefault(); alert('Request submitted successfully!'); window.location.href='?page=home';">
        
        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-white ml-1">Drama Title <span class="text-red-500">*</span></label>
            <input type="text" required class="w-full bg-cardbg border border-white/5 rounded-xl py-3 px-4 text-sm text-white placeholder-textmuted/50 focus:outline-none focus:border-accent/50 transition-colors" placeholder="e.g. Queen of Tears">
        </div>

        <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-white ml-1">Source / App (Optional)</label>
            <input type="text" class="w-full bg-cardbg border border-white/5 rounded-xl py-3 px-4 text-sm text-white placeholder-textmuted/50 focus:outline-none focus:border-accent/50 transition-colors" placeholder="e.g. Netflix, Viu, Iqiyi">
        </div>

        <div class="flex flex-col gap-1.5 mb-2">
            <label class="text-xs font-semibold text-white ml-1">Additional Notes (Optional)</label>
            <textarea rows="3" class="w-full bg-cardbg border border-white/5 rounded-xl py-3 px-4 text-sm text-white placeholder-textmuted/50 focus:outline-none focus:border-accent/50 transition-colors resize-none" placeholder="Any specific year or version?"></textarea>
        </div>

        <button type="submit" class="w-full bg-accent hover:bg-accentdark text-darkbg font-bold py-3.5 rounded-xl shadow-[0_4px_14px_rgba(0,208,182,0.3)] transition-colors">
            Submit Request
        </button>
    </form>

    <!-- My Requests History -->
    <h3 class="text-sm font-bold mb-4 text-textmuted uppercase tracking-wider">My Recent Requests</h3>
    <div class="flex flex-col gap-3 pb-8">
        <!-- Request Item 1 -->
        <div class="bg-cardbg border border-white/5 rounded-2xl p-4 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-bold text-sm text-white mb-0.5">Vincenzo (2021)</h4>
                    <p class="text-[10px] text-textmuted">Requested 2 days ago</p>
                </div>
                <span class="bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 px-2 py-0.5 rounded text-[9px] uppercase tracking-wider font-bold">Pending</span>
            </div>
        </div>
        
        <!-- Request Item 2 -->
        <div class="bg-cardbg border border-white/5 rounded-2xl p-4 flex flex-col gap-3">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-bold text-sm text-white mb-0.5 line-through decoration-white/20">The Glory</h4>
                    <p class="text-[10px] text-textmuted">Requested 1 week ago</p>
                </div>
                <span class="bg-accent/10 text-accent border border-accent/20 px-2 py-0.5 rounded text-[9px] uppercase tracking-wider font-bold">Completed</span>
            </div>
            <div class="bg-darkbg rounded-lg p-2.5 flex items-center justify-between border border-white/5">
                <span class="text-xs text-white"><i class="fa-solid fa-check text-accent mr-1"></i> Already available in catalog</span>
                <a href="#" class="text-accent text-[10px] font-bold hover:underline">WATCH NOW</a>
            </div>
        </div>
    </div>
</div>
