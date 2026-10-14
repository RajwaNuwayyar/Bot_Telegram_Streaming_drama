<?php
/**
 * Konfigurasi Database Dashboard Admin Drama Stream
 * Terhubung dengan database drama_bot / MySQL
 * Dilengkapi fallback otomatis ke Demo Data jika database offline/belum dinyalakan.
 */

// Konfigurasi Database (Mendukung getenv dan default lokal)
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'drama_bot');

$pdo = null;
$is_db_connected = false;
$db_error_message = '';

try {
    // Coba koneksi PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 2, // Timeout cepat agar tidak lag jika DB mati
    ]);
    $is_db_connected = true;
} catch (PDOException $e) {
    $is_db_connected = false;
    $db_error_message = $e->getMessage();
}

/**
 * Cek apakah database sedang terhubung
 */
function isDbConnected() {
    global $is_db_connected;
    return $is_db_connected;
}

/**
 * Mengambil instance PDO
 */
function getDb() {
    global $pdo;
    return $pdo;
}
