<?php
// Halaman 403 Forbidden - Akses Terbatas untuk Staff Admin
$adminInfo = getAdminInfo();
?>
<div class="py-12 flex items-center justify-center">
    <div class="max-w-lg w-full text-center space-y-6 glass-panel p-8 sm:p-10 rounded-3xl border border-rose-500/20 relative overflow-hidden shadow-2xl">
        <!-- Glowing Ambient Orb -->
        <div class="absolute -top-20 -right-20 w-60 h-60 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Lock Icon Badge -->
        <div class="mx-auto w-20 h-20 rounded-3xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-3xl shadow-xl shadow-rose-500/10">
            <i class="fas fa-shield-halved"></i>
        </div>

        <div class="space-y-2">
            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-rose-500/15 text-rose-300 border border-rose-500/30 uppercase tracking-wider">
                403 • Akses Terbatas
            </span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-white font-heading tracking-wide">
                Khusus Super Admin (Pemilik)
            </h2>
            <p class="text-xs text-slate-400 leading-relaxed max-w-sm mx-auto">
                Halaman ini memuat data keuangan sensitif, pencairan dana kas, atau manajemen sistem yang hanya dapat dikelola oleh pemilik akun <strong class="text-amber-300">Super Admin</strong>.
            </p>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-900/80 border border-white/5 text-xs text-slate-300 flex items-center justify-between">
            <span>Akun Anda Saat Ini:</span>
            <span class="font-bold text-sky-400 flex items-center gap-1.5">
                <i class="fas fa-user-shield text-xs"></i>
                <?= htmlspecialchars($adminInfo['full_name']); ?> (Staff Admin)
            </span>
        </div>

        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="index.php?page=overview" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition border border-white/10 flex items-center justify-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
            </a>
            <a href="index.php?page=dramas" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#00D0B6] hover:bg-[#00D0B6]/90 text-slate-950 text-xs font-bold transition flex items-center justify-center gap-2 shadow-lg shadow-[#00D0B6]/20">
                <i class="fas fa-film"></i>
                <span>Buka Katalog Drama</span>
            </a>
        </div>
    </div>
</div>
