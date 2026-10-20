<?php
// Halaman Pengaturan Database, Konfigurasi Bot, dan Manajemen Administrator
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$db = getDb();
$isDbConnected = isDbConnected();
$adminInfo = getAdminInfo();
$actionResult = null;

// Tangani aksi manajemen admin & profil (POST)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['admin_action'])) {
    $action = trim($_POST['admin_action']);

    if ($action === 'register_admin') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $role = trim($_POST['role'] ?? 'admin');

        if ($password !== $confirmPassword) {
            $actionResult = ['success' => false, 'message' => 'Konfirmasi password baru tidak cocok!'];
        } else {
            $actionResult = registerNewAdmin($username, $password, $fullName, $role);
        }
    } elseif ($action === 'update_profile') {
        $adminId = (int)$adminInfo['id'];
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $oldPassword = trim($_POST['old_password'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_new_password'] ?? '');

        if (!empty($newPassword) && $newPassword !== $confirmPassword) {
            $actionResult = ['success' => false, 'message' => 'Konfirmasi password baru tidak cocok!'];
        } else {
            $actionResult = updateAdminProfile($adminId, $fullName, $username, $oldPassword, $newPassword);
            if ($actionResult['success']) {
                $adminInfo = getAdminInfo(); // refresh session data
            }
        }
    } elseif ($action === 'toggle_status') {
        $targetId = (int)($_POST['target_admin_id'] ?? 0);
        $actionResult = toggleAdminStatus($targetId);
    } elseif ($action === 'delete_admin') {
        $targetId = (int)($_POST['target_admin_id'] ?? 0);
        $actionResult = deleteAdminAccount($targetId);
    }
}

// Daftar tabel yang diperiksa
$expectedTables = [
    'admins'                => 'Data akun kredensial dan hak akses administrator (BCrypt Secured)',
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
    } catch (Exception $e) {}
}

$allAdmins = getAllAdminsList();
?>

<div class="space-y-6">

    <!-- Notifikasi Aksi -->
    <?php if ($actionResult): ?>
    <div class="p-4 rounded-xl <?= $actionResult['success'] ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-300' : 'bg-rose-500/10 border border-rose-500/20 text-rose-300'; ?> flex items-center justify-between animate-slide-up">
        <div class="flex items-center gap-3">
            <i class="fas <?= $actionResult['success'] ? 'fa-check-circle text-emerald-400' : 'fa-exclamation-circle text-rose-400'; ?> text-base"></i>
            <span class="text-xs font-semibold"><?= htmlspecialchars($actionResult['message']); ?></span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-xs opacity-70 hover:opacity-100">✕</button>
    </div>
    <?php endif; ?>

    <!-- Section 1: Profil Saya & Ubah Kredensial -->
    <div class="glass-panel p-6 rounded-2xl border border-white/5 relative overflow-hidden" id="profile-section">
        <div class="flex items-center gap-4 mb-6 pb-4 border-b border-white/5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#00D0B6] to-sky-400 text-slate-950 flex items-center justify-center text-xl font-bold shadow-lg shadow-[#00D0B6]/20">
                <?= strtoupper(substr($adminInfo['full_name'], 0, 1)); ?>
            </div>
            <div>
                <h3 class="font-bold text-white text-base font-heading">Profil Saya &amp; Kredensial Login</h3>
                <p class="text-xs text-slate-400">Ubah nama, username, atau perbarui password login akun Anda saat ini</p>
            </div>
            <div class="ml-auto hidden sm:block">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#00D0B6]/10 text-[#00D0B6] border border-[#00D0B6]/20 uppercase">
                    <?= htmlspecialchars($adminInfo['role']); ?>
                </span>
            </div>
        </div>

        <form method="POST" action="" class="space-y-4 text-xs">
            <input type="hidden" name="admin_action" value="update_profile">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Nama Lengkap *</label>
                    <input type="text" name="full_name" required value="<?= htmlspecialchars($adminInfo['full_name']); ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6] transition">
                </div>
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Username Login *</label>
                    <input type="text" name="username" required value="<?= htmlspecialchars($adminInfo['username']); ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-[#00D0B6] transition">
                </div>
            </div>

            <div class="p-4 rounded-xl bg-slate-900/60 border border-white/5 space-y-3 mt-2">
                <div class="flex items-center gap-2 text-slate-200 font-semibold">
                    <i class="fas fa-key text-amber-400"></i>
                    <span>Ubah Password Akun (Kosongkan jika tidak ingin mengganti password)</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Password Saat Ini</label>
                        <input type="password" name="old_password" placeholder="••••••••" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-amber-400 transition">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Password Baru</label>
                        <input type="password" name="new_password" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-amber-400 transition">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_new_password" placeholder="Ulangi password baru" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-amber-400 transition">
                    </div>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#00D0B6] hover:bg-[#00D0B6]/90 text-slate-950 font-bold transition flex items-center gap-2 shadow-lg shadow-[#00D0B6]/20">
                    <i class="fas fa-save text-xs"></i>
                    <span>Simpan Perubahan Profil</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Section 2: Manajemen Akun Administrator (Multi-Admin) -->
    <div class="glass-panel rounded-2xl overflow-hidden border border-white/5">
        <div class="p-6 border-b border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-white text-base font-heading">Manajemen Akun Administrator (Multi-Admin)</h3>
                <p class="text-xs text-slate-400">Kelola daftar akun yang memiliki akses login ke Dashboard Admin</p>
            </div>
            <div>
                <button onclick="openModal('registerAdminModal')" class="px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-400 text-white text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-sky-500/20">
                    <i class="fas fa-user-plus"></i>
                    <span>+ Daftarkan Admin Baru</span>
                </button>
            </div>
        </div>

        <!-- Table View Admins -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm custom-table">
                <thead>
                    <tr>
                        <th>Administrator</th>
                        <th>Role Akses</th>
                        <th>Status Akun</th>
                        <th>Terakhir Login</th>
                        <th>Terdaftar Sejak</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($allAdmins)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-500 text-xs">
                            Tidak ada akun admin terdata.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($allAdmins as $adm): 
                            $isSelf = (int)$adm['id'] === (int)$adminInfo['id'];
                        ?>
                        <tr class="hover:bg-white/[0.02] transition">
                            <!-- Administrator info -->
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl <?= $adm['role'] === 'superadmin' ? 'bg-gradient-to-tr from-amber-500 to-amber-300 text-slate-950' : 'bg-slate-800 text-slate-300'; ?> font-bold text-xs flex items-center justify-center shadow">
                                        <?= strtoupper(substr($adm['full_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold text-white"><?= htmlspecialchars($adm['full_name']); ?></p>
                                            <?php if ($isSelf): ?>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#00D0B6]/15 text-[#00D0B6] border border-[#00D0B6]/30">Anda</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-slate-400 font-mono">@<?= htmlspecialchars($adm['username']); ?></p>
                                    </div>
                                </div>
                            </td>

                            <!-- Role -->
                            <td>
                                <?php if ($adm['role'] === 'superadmin'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-300 border border-amber-500/20">
                                        <i class="fas fa-crown text-[10px]"></i> Super Admin
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-500/10 text-sky-300 border border-sky-500/20">
                                        <i class="fas fa-user-shield text-[10px]"></i> Staff Admin
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Status -->
                            <td>
                                <?php if ($adm['is_active']): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Dinonaktifkan
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Terakhir Login -->
                            <td class="text-xs text-slate-400">
                                <?= !empty($adm['last_login_at']) ? timeAgo($adm['last_login_at']) : '<span class="italic text-slate-600">Belum pernah login</span>'; ?>
                            </td>

                            <!-- Terdaftar Sejak -->
                            <td class="text-xs text-slate-400">
                                <?= date('d M Y', strtotime($adm['created_at'])); ?>
                            </td>

                            <!-- Aksi Menu -->
                            <td class="text-right">
                                <?php if ($isSelf): ?>
                                    <span class="text-xs text-slate-600 italic">Akun aktif Anda</span>
                                <?php else: ?>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Toggle Status -->
                                        <form method="POST" action="" onsubmit="return confirm('Ubah status keaktifan akun admin ini?');">
                                            <input type="hidden" name="admin_action" value="toggle_status">
                                            <input type="hidden" name="target_admin_id" value="<?= $adm['id']; ?>">
                                            <button type="submit" class="p-2 rounded-lg bg-slate-800 <?= $adm['is_active'] ? 'hover:bg-amber-500/20 hover:text-amber-400 text-slate-400' : 'hover:bg-emerald-500/20 hover:text-emerald-400 text-emerald-400'; ?> transition" title="<?= $adm['is_active'] ? 'Nonaktifkan Akun' : 'Aktifkan Akun'; ?>">
                                                <i class="fas <?= $adm['is_active'] ? 'fa-user-lock' : 'fa-user-check'; ?> text-xs"></i>
                                            </button>
                                        </form>

                                        <!-- Hapus Akun -->
                                        <form method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun admin <?= htmlspecialchars($adm['username']); ?>?');">
                                            <input type="hidden" name="admin_action" value="delete_admin">
                                            <input type="hidden" name="target_admin_id" value="<?= $adm['id']; ?>">
                                            <button type="submit" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-500/20 hover:text-rose-400 text-slate-400 transition" title="Hapus Admin">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Status Koneksi Database MySQL & Panduan -->
    <div class="glass-panel p-6 rounded-2xl border border-white/5">
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

        <div class="mt-6 pt-6 border-t border-white/5 space-y-4">
            <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Panduan Konfigurasi Database:</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-900/80 border border-white/5 space-y-1">
                    <p class="font-bold text-white">1. Jalankan MySQL / XAMPP</p>
                    <p class="text-slate-400">Pastikan service MySQL di XAMPP / server lokal sudah dalam kondisi <strong class="text-emerald-400">Running</strong> (Port 3306).</p>
                </div>

                <div class="p-4 rounded-xl bg-slate-900/80 border border-white/5 space-y-1">
                    <p class="font-bold text-white">2. Import Skema Database</p>
                    <p class="text-slate-400">File SQL skema lengkap tersedia di <span class="text-[#00D0B6] font-mono">database/schema_v5_final.sql</span> untuk struktur tabel otomatis.</p>
                </div>

                <div class="p-4 rounded-xl bg-slate-900/80 border border-white/5 space-y-1">
                    <p class="font-bold text-white">3. Sesuaikan Konfigurasi</p>
                    <p class="text-slate-400">Jika nama database, username, atau password berbeda, cukup ubah file <span class="text-[#00D0B6] font-mono">admin/config/database.php</span>.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Pemeriksaan Struktur Tabel Database -->
    <div class="glass-panel rounded-2xl overflow-hidden border border-white/5">
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
                                <i class="fas fa-check text-[10px]"></i> Ada &amp; Siap
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

<!-- Modal Pendaftaran Admin Baru -->
<div id="registerAdminModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-[#111827] border border-white/10 rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('registerAdminModal')" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base">Daftarkan Administrator Baru</h3>
                <p class="text-xs text-slate-400">Buat kredensial login admin baru untuk tim Anda</p>
            </div>
        </div>

        <form method="POST" action="" class="space-y-4 text-xs">
            <input type="hidden" name="admin_action" value="register_admin">

            <div>
                <label class="block text-slate-300 font-semibold mb-1">Nama Lengkap *</label>
                <input type="text" name="full_name" required placeholder="Contoh: Budi Santoso" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-sky-400">
            </div>

            <div>
                <label class="block text-slate-300 font-semibold mb-1">Username Login *</label>
                <input type="text" name="username" required placeholder="Contoh: budi_admin" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-sky-400">
                <p class="text-[10px] text-slate-500 mt-1">Hanya huruf, angka, dan underscore (_)</p>
            </div>

            <div>
                <label class="block text-slate-300 font-semibold mb-1">Peran / Hak Akses</label>
                <select name="role" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-sky-400">
                    <option value="admin">Staff Admin (Akses Kelola)</option>
                    <option value="superadmin">Super Admin (Akses Penuh)</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Password *</label>
                    <input type="password" name="password" required placeholder="Min 6 karakter" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-sky-400">
                </div>
                <div>
                    <label class="block text-slate-300 font-semibold mb-1">Konfirmasi Password *</label>
                    <input type="password" name="confirm_password" required placeholder="Ulangi password" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-white/10 text-white focus:outline-none focus:border-sky-400">
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('registerAdminModal')" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-sky-500 hover:bg-sky-400 text-white font-bold shadow-lg shadow-sky-500/20">Daftarkan Admin</button>
            </div>
        </form>
    </div>
</div>
