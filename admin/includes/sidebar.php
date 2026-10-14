<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'overview';
?>
<!-- Sidebar Navigation Component -->
<aside id="sidebar" class="fixed top-0 bottom-0 left-0 z-50 w-64 bg-[#0B101D] border-r border-white/5 flex flex-col justify-between transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0">
    
    <!-- Top Brand & Navigation Links -->
    <div class="flex-1 overflow-y-auto py-6 px-4">
        <!-- Brand Logo Header -->
        <div class="flex items-center justify-between px-2 mb-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#00D0B6] to-sky-400 flex items-center justify-center shadow-lg shadow-[#00D0B6]/20">
                    <i class="fas fa-play text-slate-950 text-base ml-0.5"></i>
                </div>
                <div>
                    <h2 class="text-base font-extrabold font-heading text-white tracking-wide">DramaStream</h2>
                    <span class="text-[10px] font-semibold tracking-wider uppercase px-2 py-0.5 rounded-full bg-[#00D0B6]/10 text-[#00D0B6] border border-[#00D0B6]/20">Admin Panel</span>
                </div>
            </div>
            <!-- Mobile Close Button -->
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white p-1">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Navigation Menu Sections -->
        <nav class="space-y-6">
            <!-- Group 1: Ringkasan & Pelanggan -->
            <div>
                <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Utama</p>
                <div class="space-y-1">
                    <a href="index.php?page=overview" class="nav-link flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all <?= ($page === 'overview') ? 'active text-[#00D0B6]' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/50'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-chart-pie w-5 text-center text-sm"></i>
                            <span>Dashboard Utama</span>
                        </div>
                    </a>

                    <a href="index.php?page=users" class="nav-link flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all <?= ($page === 'users') ? 'active text-[#00D0B6]' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/50'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-users w-5 text-center text-sm"></i>
                            <span>Pelanggan Masuk</span>
                        </div>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">Users</span>
                    </a>
                </div>
            </div>

            <!-- Group 2: Monetisasi & Konten -->
            <div>
                <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Monetisasi & Konten</p>
                <div class="space-y-1">
                    <a href="index.php?page=vip" class="nav-link flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all <?= ($page === 'vip') ? 'active text-[#00D0B6]' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/50'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-crown w-5 text-center text-sm text-amber-400"></i>
                            <span>Transaksi & VIP</span>
                        </div>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">QRIS</span>
                    </a>

                    <a href="index.php?page=dramas" class="nav-link flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all <?= ($page === 'dramas') ? 'active text-[#00D0B6]' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/50'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-film w-5 text-center text-sm"></i>
                            <span>Katalog Drama</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Group 3: Komunitas & Interaksi -->
            <div>
                <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Komunitas & Bot</p>
                <div class="space-y-1">
                    <a href="index.php?page=affiliate" class="nav-link flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all <?= ($page === 'affiliate') ? 'active text-[#00D0B6]' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/50'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-share-nodes w-5 text-center text-sm"></i>
                            <span>Affiliate & Penarikan</span>
                        </div>
                    </a>

                    <a href="index.php?page=requests" class="nav-link flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all <?= ($page === 'requests') ? 'active text-[#00D0B6]' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/50'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-inbox w-5 text-center text-sm"></i>
                            <span>Request Film</span>
                        </div>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">New</span>
                    </a>
                </div>
            </div>

            <!-- Group 4: Konfigurasi -->
            <div>
                <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Sistem</p>
                <div class="space-y-1">
                    <a href="index.php?page=settings" class="nav-link flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm transition-all <?= ($page === 'settings') ? 'active text-[#00D0B6]' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/50'; ?>">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-sliders-h w-5 text-center text-sm"></i>
                            <span>Pengaturan Database</span>
                        </div>
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <!-- Bottom Quick Status Card -->
    <div class="p-4 border-t border-white/5">
        <div class="p-3.5 rounded-2xl bg-gradient-to-b from-slate-900 to-slate-950 border border-white/5 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-300">Status Server</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            </div>
            <p class="text-[11px] text-slate-400">Drama Bot v5.0 Active</p>
            <a href="../Homepage/index.php" target="_blank" class="mt-1 flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-[#00D0B6]/10 text-[#00D0B6] hover:bg-[#00D0B6]/20 transition text-xs font-semibold">
                <i class="fas fa-external-link-alt text-[10px]"></i>
                <span>Buka Mini App</span>
            </a>
        </div>
    </div>
</aside>
