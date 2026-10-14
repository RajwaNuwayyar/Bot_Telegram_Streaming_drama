<?php
// Halaman Pengaturan Database & Konfigurasi Bot
$db = getDb();
$isDbConnected = isDbConnected();

// Daftar tabel yang diperiksa
$expectedTables = [
    'users'                 => 'Menyimpan data akun Telegram & status VIP pelanggan',
    'vip_plans'             => 'Pilihan paket langganan VIP (7 hari, 30 hari, dll)',
    'vip_purchases'         => 'Riwayat transaksi pembelian VIP pelanggan',
    'qris_payments'         => 'Data invoice dan callback gateway QRIS',
    'dramas'                => 'Data judul drama, genre, dan episode gratis',
    'episodes'              => 'Konten episode terhubung ke channel Telegram',
    'affiliate_levels'      => 'Tingkatan komisi referral affiliate',
    'affiliate_commissions' => 'Catatan komisi per transaksi VIP',
    'affiliate_withdrawals' => 'Permintaan pencairan dana komisi mitra',
    'film_requests'         => 'Permintaan request film dari user',
    'app_settings'          => 'Pengaturan global sistem & rate koin'
];

$tableStatuses = [];
if ($db) {
    try {
        $stmt = $db->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($expectedTables as $tbl => $desc) {
            $tableStatuses[$tbl] = in_array($tbl, $existingTables);
        }
    } catch (Exception $e) {
        // failed query
    }
}
?>

<div class="space-y-6">

    <!-- Status Box -->
    <div class="glass-panel p-6 rounded-2xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl <?= $isDbConnected ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'; ?> flex items-center justify-center text-xl">
                    <i class="fas <?= $isDbConnected ? 'fa-database' : 'fa-plug-circle-xmark'; ?>"></i>
                </div>
                <div>
                    <h3 class="font-bold text-white text-base">Status Koneksi Database MySQL</h3>
                    <p class="text-xs text-slate-400">Database: <span class="font-mono text-slate-200"><?= DB_NAME; ?></span> | Host: <span class="font-mono text-slate-200"><?= DB_HOST; ?></span></p>
                </div>
            </div>
            <div>
                <?php if ($isDbConnected): ?>
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Terhubung ke Database
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        Offline (Mode Demo Interaktif)
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Connection Details & Instructions -->
        <div class="mt-6 pt-6 border-t border-white/5 space-y-4">
            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Panduan Menghubungkan ke Database Mini App:</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-900/80 border border-white/5 space-y-1">
                    <p class="font-bold text-white">1. Jalankan MySQL / XAMPP</p>
                    <p class="text-slate-400">Pastikan service MySQL di XAMPP / server lokal Anda sudah dalam kondisi <strong class="text-emerald-400">Running</strong> (Port 3306).</p>
                </div>

                <div class="p-4 rounded-xl bg-slate-900/80 border border-white/5 space-y-1">
                    <p class="font-bold text-white">2. Import Skema Database</p>
                    <p class="text-slate-400">File SQL skema lengkap tersedia di <span class="text-[#00D0B6] font-mono">database/schema_v5_final.sql</span> untuk struktur tabel otomatis.</p>
                </div>

                <div class="p-4 rounded-xl bg-slate-900/80 border border-white/5 space-y-1">
                    <p class="font-bold text-white">3. Sesuaikan Konfigurasi</p>
                    <p class="text-slate-400">Jika nama database, username, atau password berbeda, cukup ubah file <span class="text-[#00D0B6] font-mono">Dashboard Admin/config/database.php</span>.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Health Check Grid -->
    <div class="glass-panel rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-white/5">
            <h3 class="font-bold text-white text-base font-heading">Pemeriksaan Struktur Tabel Database</h3>
            <p class="text-xs text-slate-400">Status ketersediaan tabel-tabel utama di database <code class="text-[#00D0B6]">drama_bot</code></p>
        </div>

        <div class="divide-y divide-white/5">
            <?php foreach ($expectedTables as $table => $desc): 
                $exists = $tableStatuses[$table] ?? false;
            ?>
            <div class="p-4 flex items-center justify-between hover:bg-white/[0.02] transition">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 text-xs">
                        <i class="fas fa-table"></i>
                    </div>
                    <div>
                        <p class="font-mono text-xs font-bold text-white"><?= $table; ?></p>
                        <p class="text-[11px] text-slate-400"><?= $desc; ?></p>
                    </div>
                </div>

                <div>
                    <?php if ($isDbConnected): ?>
                        <?php if ($exists): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <i class="fas fa-check text-[10px]"></i> Ada & Siap
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                <i class="fas fa-exclamation-triangle text-[10px]"></i> Belum Terbuat
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-800 text-slate-400 border border-white/5">
                            <i class="fas fa-eye text-[10px]"></i> Demo Mode
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>
