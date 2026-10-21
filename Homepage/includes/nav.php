<?php
// Ambil page dari variable global yang di-set di index.php
global $page;
if (empty($page)) {
    $page = isset($_GET['page']) ? $_GET['page'] : 'home';
}
?>
<!-- Floating Island Bottom Navigation (Rounded Oval / Pill Glassmorphism) -->
<div class="fixed bottom-4 inset-x-0 z-50 flex justify-center pointer-events-none px-4">
    <nav class="glass-nav pointer-events-auto w-full max-w-sm rounded-full py-2.5 px-3 transition-all duration-300">
        <div class="flex items-center justify-between">
            <!-- 1. Home -->
            <a href="?page=home" class="nav-tab-item flex-1 flex flex-col items-center justify-center py-1 relative group transition-all duration-200 active:scale-90 <?= $page == 'home' ? 'nav-active text-accent' : 'text-textmuted hover:text-white' ?>">
                <?php if ($page == 'home'): ?>
                    <div class="nav-glow absolute inset-0 -m-1 rounded-full pointer-events-none"></div>
                <?php endif; ?>
                <div class="relative z-10 flex flex-col items-center">
                    <i class="fa-solid fa-house text-lg mb-0.5 transition-transform duration-200 group-hover:scale-110"></i>
                    <span data-i18n="nav_home" class="text-[10px] font-semibold tracking-wide leading-none">Home</span>
                </div>
            </a>

            <!-- 2. History -->
            <a href="?page=history" class="nav-tab-item flex-1 flex flex-col items-center justify-center py-1 relative group transition-all duration-200 active:scale-90 <?= $page == 'history' ? 'nav-active text-accent' : 'text-textmuted hover:text-white' ?>">
                <?php if ($page == 'history'): ?>
                    <div class="nav-glow absolute inset-0 -m-1 rounded-full pointer-events-none"></div>
                <?php endif; ?>
                <div class="relative z-10 flex flex-col items-center">
                    <i class="fa-solid fa-clock-rotate-left text-lg mb-0.5 transition-transform duration-200 group-hover:scale-110"></i>
                    <span data-i18n="nav_history" class="text-[10px] font-semibold tracking-wide leading-none">History</span>
                </div>
            </a>

            <!-- 3. VIP -->
            <a href="?page=vip" class="nav-tab-item flex-1 flex flex-col items-center justify-center py-1 relative group transition-all duration-200 active:scale-90 <?= $page == 'vip' ? 'nav-active text-accent' : 'text-textmuted hover:text-white' ?>">
                <?php if ($page == 'vip'): ?>
                    <div class="nav-glow absolute inset-0 -m-1 rounded-full pointer-events-none"></div>
                <?php endif; ?>
                <div class="relative z-10 flex flex-col items-center">
                    <i class="fa-solid fa-crown text-lg mb-0.5 transition-transform duration-200 group-hover:scale-110"></i>
                    <span data-i18n="nav_vip" class="text-[10px] font-semibold tracking-wide leading-none">VIP</span>
                </div>
            </a>

            <!-- 4. Profile -->
            <a href="?page=profile" class="nav-tab-item flex-1 flex flex-col items-center justify-center py-1 relative group transition-all duration-200 active:scale-90 <?= $page == 'profile' ? 'nav-active text-accent' : 'text-textmuted hover:text-white' ?>">
                <?php if ($page == 'profile'): ?>
                    <div class="nav-glow absolute inset-0 -m-1 rounded-full pointer-events-none"></div>
                <?php endif; ?>
                <div class="relative z-10 flex flex-col items-center">
                    <i class="<?= $page == 'profile' ? 'fa-solid' : 'fa-regular' ?> fa-user text-lg mb-0.5 transition-transform duration-200 group-hover:scale-110"></i>
                    <span data-i18n="nav_profile" class="text-[10px] font-semibold tracking-wide leading-none">Profile</span>
                </div>
            </a>
        </div>
    </nav>
</div>
