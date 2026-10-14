<?php
/**
 * Autentikasi Admin Dashboard
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah admin sudah login
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Proteksi halaman admin
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Ambil info admin aktif
function getAdminInfo() {
    return [
        'username' => $_SESSION['admin_username'] ?? 'Super Admin',
        'role'     => $_SESSION['admin_role'] ?? 'Administrator',
        'avatar'   => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=256&q=80',
    ];
}
