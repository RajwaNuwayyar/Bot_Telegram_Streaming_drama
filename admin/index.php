<?php
/**
 * Main Entry Point - Dashboard Admin Drama Stream
 */
require_once __DIR__ . '/includes/auth.php';

// Cek autentikasi admin
requireAdminLogin();

// Routing Halaman
$page = isset($_GET['page']) ? $_GET['page'] : 'overview';
$allowed_pages = [
    'overview',    // Beranda & Statistik Pendapatan
    'users',       // Pendataan Pelanggan Masuk
    'vip',         // Pemantauan Transaksi VIP & QRIS
    'dramas',      // Manajemen Katalog Drama & Episode
    'affiliate',   // Program Affiliate & Penarikan Saldo
    'requests',    // Permintaan Film dari Penonton
    'settings'     // Pengaturan Koneksi Database
];

if (!in_array($page, $allowed_pages)) {
    $page = 'overview';
}

// Render Layout & Cek Izin Hak Akses Role
include __DIR__ . '/includes/header.php';

if (!canAccessPage($page)) {
    include __DIR__ . '/pages/403.php';
} else {
    include __DIR__ . "/pages/{$page}.php";
}

include __DIR__ . '/includes/footer.php';

