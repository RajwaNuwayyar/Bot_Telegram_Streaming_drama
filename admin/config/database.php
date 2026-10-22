<?php
/**
 * Konfigurasi Database Dashboard Admin Drama Stream
 * Terhubung dengan database drama_bot / MySQL
 * Dilengkapi fallback otomatis ke Demo Data jika database offline/belum dinyalakan.
 */

// Konfigurasi Database (Otomatis Mendukung Docker & Local XAMPP/Laragon)
$envHost = getenv('DB_HOST');
$envUser = getenv('DB_USER');
$envPass = getenv('DB_PASS');
$envName = getenv('DB_NAME');

// Deteksi otomatis apakah berjalan di dalam container Docker tanpa DNS delay
$isDockerNetwork = file_exists('/.dockerenv') || (getenv('DOCKER_CONTAINER') !== false) || (getenv('IS_DOCKER') !== false);

if (!defined('DB_HOST')) {
    define('DB_HOST', $envHost ?: ($isDockerNetwork ? 'db' : '127.0.0.1'));
}
if (!defined('DB_USER')) {
    define('DB_USER', $envUser ?: ($isDockerNetwork ? 'bot_user' : 'root'));
}
if (!defined('DB_PASS')) {
    define('DB_PASS', ($envPass !== false && $envPass !== null) ? $envPass : ($isDockerNetwork ? 'bot_password' : ''));
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $envName ?: 'drama_bot');
}

$pdo = null;
$is_db_connected = false;
$db_error_message = '';

try {
    // 1. Coba koneksi primer sesuai konfigurasi deteksi
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3,
    ]);
    $is_db_connected = true;
} catch (PDOException $e) {
    // 2. Fallback cerdas: jika host pertama gagal, coba alternatifnya (XAMPP vs Docker)
    if (DB_HOST !== 'localhost' && DB_HOST !== '127.0.0.1') {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=" . DB_NAME . ";charset=utf8mb4", 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 3,
            ]);
            $is_db_connected = true;
        } catch (PDOException $e2) {
            $is_db_connected = false;
            $db_error_message = $e2->getMessage();
        }
    } else {
        try {
            $pdo = new PDO("mysql:host=db;dbname=" . DB_NAME . ";charset=utf8mb4", 'bot_user', 'bot_password', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 3,
            ]);
            $is_db_connected = true;
        } catch (PDOException $e3) {
            $is_db_connected = false;
            $db_error_message = $e->getMessage();
        }
    }
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
