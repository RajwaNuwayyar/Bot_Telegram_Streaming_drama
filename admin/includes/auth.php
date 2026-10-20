<?php
/**
 * Autentikasi & Manajemen Akun Administrator
 * Terintegrasi langsung dengan database MySQL (Tabel admins)
 * Dilengkapi pengamanan hash BCrypt, session regeneration, dan proteksi brute-force.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Cek apakah admin sudah terotentikasi
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Proteksi akses halaman admin
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Cek apakah admin yang sedang login adalah Super Admin
 */
function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && strtolower($_SESSION['admin_role']) === 'superadmin';
}

/**
 * Ambil data info admin yang sedang login
 */
function getAdminInfo() {
    return [
        'id'        => $_SESSION['admin_id'] ?? 1,
        'username'  => $_SESSION['admin_username'] ?? 'admin',
        'full_name' => $_SESSION['admin_full_name'] ?? 'Super Admin',
        'role'      => $_SESSION['admin_role'] ?? 'Administrator',
        'avatar'    => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=256&q=80',
    ];
}

/**
 * Proteksi Brute-Force Rate Limiting (Maks 5 percobaan gagal per 5 menit)
 */
function checkLoginThrottling() {
    $now = time();
    $attempts = $_SESSION['login_attempts'] ?? 0;
    $lastAttempt = $_SESSION['login_last_attempt'] ?? $now;

    // Reset jika sudah lewat 5 menit (300 detik)
    if (($now - $lastAttempt) > 300) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_last_attempt'] = $now;
        return ['allowed' => true];
    }

    if ($attempts >= 5) {
        $remaining = 300 - ($now - $lastAttempt);
        return [
            'allowed' => false,
            'message' => "Terlalu banyak percobaan gagal! Mohon tunggu {$remaining} detik sebelum mencoba lagi."
        ];
    }

    return ['allowed' => true];
}

function recordFailedLoginAttempt() {
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    $_SESSION['login_last_attempt'] = time();
}

function clearLoginAttempts() {
    unset($_SESSION['login_attempts'], $_SESSION['login_last_attempt']);
}

/**
 * Proses Autentikasi Login Admin
 */
function attemptAdminLogin($username, $password) {
    $throttle = checkLoginThrottling();
    if (!$throttle['allowed']) {
        return ['success' => false, 'message' => $throttle['message']];
    }

    $username = trim($username);
    $password = trim($password);

    if (empty($username) || empty($password)) {
        return ['success' => false, 'message' => 'Username dan Password wajib diisi!'];
    }

    $db = getDb();
    if (!$db) {
        return ['success' => false, 'message' => 'Koneksi database gagal. Mohon pastikan MySQL sudah berjalan!'];
    }

    try {
        // Ambil data admin berdasarkan username
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifikasi keberadaan akun dan kecocokan hash password
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            recordFailedLoginAttempt();
            return ['success' => false, 'message' => 'Username atau Password salah!'];
        }

        // Cek status keaktifan akun
        if (empty($admin['is_active'])) {
            return ['success' => false, 'message' => 'Akun Anda dinonaktifkan oleh administrator! Silakan hubungi Super Admin.'];
        }

        // Login Berhasil: Reset throttling & regenerate session untuk cegah Session Fixation
        clearLoginAttempts();
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id']        = $admin['id'];
        $_SESSION['admin_username']  = $admin['username'];
        $_SESSION['admin_full_name'] = $admin['full_name'];
        $_SESSION['admin_role']      = $admin['role'];

        // Catat timestamp login terakhir
        $upd = $db->prepare("UPDATE admins SET last_login_at = NOW() WHERE id = :id");
        $upd->execute([':id' => $admin['id']]);

        return ['success' => true, 'message' => 'Login berhasil!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()];
    }
}

/**
 * Mengambil daftar seluruh administrator terdaftar
 */
function getAllAdminsList() {
    $db = getDb();
    if ($db) {
        try {
            $stmt = $db->query("SELECT id, username, full_name, role, is_active, last_login_at, created_at FROM admins ORDER BY id ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
    return [];
}

/**
 * Pendaftaran Akun Admin Baru (Multi-Admin)
 */
function registerNewAdmin($username, $password, $fullName, $role = 'admin') {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database tidak terhubung'];

    $username = trim($username);
    $fullName = trim($fullName);
    $role = in_array(strtolower($role), ['superadmin', 'admin']) ? strtolower($role) : 'admin';

    // Validasi username
    if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return ['success' => false, 'message' => 'Username minimal 3 karakter dan hanya boleh berisi huruf, angka, dan underscore!'];
    }

    if (empty($fullName)) {
        return ['success' => false, 'message' => 'Nama lengkap administrator wajib diisi!'];
    }

    // Validasi panjang password
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password minimal 6 karakter demi keamanan!'];
    }

    try {
        // Cek apakah username sudah dipakai
        $chk = $db->prepare("SELECT id FROM admins WHERE username = :u LIMIT 1");
        $chk->execute([':u' => $username]);
        if ($chk->fetch()) {
            return ['success' => false, 'message' => "Username '{$username}' sudah terdaftar! Gunakan username lain."];
        }

        // Enkripsi password menggunakan algoritma standar industri BCrypt
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $db->prepare("INSERT INTO admins (username, password_hash, full_name, role, is_active, created_at) VALUES (:u, :p, :n, :r, 1, NOW())");
        $stmt->execute([
            ':u' => $username,
            ':p' => $passwordHash,
            ':n' => $fullName,
            ':r' => $role
        ]);

        return ['success' => true, 'message' => "Administrator baru '{$username}' ({$fullName}) berhasil didaftarkan!"];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Ubah Profil dan/atau Password Admin Sendiri
 */
function updateAdminProfile($adminId, $fullName, $username, $oldPassword = '', $newPassword = '') {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database tidak terhubung'];

    $adminId = (int)$adminId;
    $username = trim($username);
    $fullName = trim($fullName);

    if (empty($fullName) || empty($username)) {
        return ['success' => false, 'message' => 'Nama lengkap dan username tidak boleh kosong!'];
    }

    try {
        $stmt = $db->prepare("SELECT * FROM admins WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            return ['success' => false, 'message' => 'Data akun admin tidak ditemukan!'];
        }

        // Cek duplikasi username jika username diubah
        if (strtolower($username) !== strtolower($admin['username'])) {
            $chk = $db->prepare("SELECT id FROM admins WHERE username = :u AND id != :id LIMIT 1");
            $chk->execute([':u' => $username, ':id' => $adminId]);
            if ($chk->fetch()) {
                return ['success' => false, 'message' => "Username '{$username}' sudah digunakan oleh akun lain!"];
            }
        }

        // Jika ingin mengubah password
        $passwordHashToUpdate = null;
        if (!empty($newPassword)) {
            if (empty($oldPassword)) {
                return ['success' => false, 'message' => 'Password saat ini wajib dimasukkan untuk konfirmasi ganti password!'];
            }

            if (!password_verify($oldPassword, $admin['password_hash'])) {
                return ['success' => false, 'message' => 'Password saat ini salah! Perubahan dibatalkan.'];
            }

            if (strlen($newPassword) < 6) {
                return ['success' => false, 'message' => 'Password baru minimal 6 karakter!'];
            }

            $passwordHashToUpdate = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        // Update database
        if ($passwordHashToUpdate) {
            $upd = $db->prepare("UPDATE admins SET full_name = :n, username = :u, password_hash = :p, updated_at = NOW() WHERE id = :id");
            $upd->execute([':n' => $fullName, ':u' => $username, ':p' => $passwordHashToUpdate, ':id' => $adminId]);
        } else {
            $upd = $db->prepare("UPDATE admins SET full_name = :n, username = :u, updated_at = NOW() WHERE id = :id");
            $upd->execute([':n' => $fullName, ':u' => $username, ':id' => $adminId]);
        }

        // Perbarui session jika admin yang diupdate adalah yang sedang login
        if (isset($_SESSION['admin_id']) && (int)$_SESSION['admin_id'] === $adminId) {
            $_SESSION['admin_username']  = $username;
            $_SESSION['admin_full_name'] = $fullName;
        }

        return ['success' => true, 'message' => 'Profil dan kredensial admin berhasil diperbarui!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Toggle Status Aktif / Nonaktif Admin Lain
 */
function toggleAdminStatus($adminId) {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database tidak terhubung'];

    $adminId = (int)$adminId;
    if (isset($_SESSION['admin_id']) && (int)$_SESSION['admin_id'] === $adminId) {
        return ['success' => false, 'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri!'];
    }

    try {
        $stmt = $db->prepare("UPDATE admins SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $adminId]);

        $chk = $db->prepare("SELECT username, is_active FROM admins WHERE id = :id");
        $chk->execute([':id' => $adminId]);
        $row = $chk->fetch();

        $statusStr = ($row['is_active'] == 1) ? 'diaktifkan kembali' : 'dinonaktifkan';
        return ['success' => true, 'message' => "Akun admin '{$row['username']}' berhasil {$statusStr}."];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Hapus Akun Admin
 */
function deleteAdminAccount($adminId) {
    $db = getDb();
    if (!$db) return ['success' => false, 'message' => 'Database tidak terhubung'];

    $adminId = (int)$adminId;
    if (isset($_SESSION['admin_id']) && (int)$_SESSION['admin_id'] === $adminId) {
        return ['success' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri!'];
    }

    try {
        // Pastikan tidak menghapus semua admin tersisa
        $count = $db->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        if ($count <= 1) {
            return ['success' => false, 'message' => 'Tidak dapat menghapus! Harus ada minimal 1 akun administrator di sistem.'];
        }

        $stmt = $db->prepare("DELETE FROM admins WHERE id = :id");
        $stmt->execute([':id' => $adminId]);

        return ['success' => true, 'message' => "Akun administrator #{$adminId} berhasil dihapus dari sistem."];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}
