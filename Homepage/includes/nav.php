<?php
// Ambil page dari variable global yang di-set di index.php
global $page;
?>
<nav class="fixed bottom-0 left-0 right-0 glass-nav z-50 py-3 px-6">
    <div class="flex justify-between items-center max-w-md mx-auto">
        <a href="?page=home" class="flex flex-col items-center gap-1 transition-colors <?= $page == 'home' ? 'text-accent' : 'text-textmuted' ?>">
            <i class="fa-solid fa-house text-xl mb-0.5"></i>
            <span class="text-[10px] font-medium tracking-wide">Home</span>
            <?= $page == 'home' ? '<div class="w-1 h-1 rounded-full bg-accent mt-0.5"></div>' : '' ?>
        </a>
        <a href="?page=history" class="flex flex-col items-center gap-1 transition-colors <?= $page == 'history' ? 'text-accent' : 'text-textmuted' ?>">
            <i class="fa-regular fa-clock text-xl mb-0.5"></i>
            <span class="text-[10px] font-medium tracking-wide">History</span>
            <?= $page == 'history' ? '<div class="w-1 h-1 rounded-full bg-accent mt-0.5"></div>' : '' ?>
        </a>
        <a href="?page=vip" class="flex flex-col items-center gap-1 transition-colors <?= $page == 'vip' ? 'text-accent' : 'text-textmuted' ?>">
            <i class="fa-solid fa-crown text-xl mb-0.5"></i>
            <span class="text-[10px] font-medium tracking-wide">VIP</span>
            <?= $page == 'vip' ? '<div class="w-1 h-1 rounded-full bg-accent mt-0.5"></div>' : '' ?>
        </a>
        <a href="?page=profile" class="flex flex-col items-center gap-1 transition-colors <?= $page == 'profile' ? 'text-accent' : 'text-textmuted' ?>">
            <i class="fa-regular fa-user text-xl mb-0.5"></i>
            <span class="text-[10px] font-medium tracking-wide">Profile</span>
            <?= $page == 'profile' ? '<div class="w-1 h-1 rounded-full bg-accent mt-0.5"></div>' : '' ?>
        </a>
    </div>
</nav>
