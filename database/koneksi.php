<?php
// Konfigurasi Database (Mendukung Local XAMPP/Laragon & Docker Container)
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db   = getenv('DB_NAME') ?: 'drama_bot';

// Jika host diset ke 'db' tapi berjalan di luar Docker (host 'db' tidak dapat di-resolve di OS lokal),
// fallback otomatis ke localhost
if ($host === 'db' && gethostbyname('db') === 'db') {
    $host = 'localhost';
    if ($user === 'bot_user') {
        $user = 'root';
        $pass = '';
    }
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Fallback cadangan: jika host selain localhost gagal, coba localhost root
    if ($host !== 'localhost' && $host !== '127.0.0.1') {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=$db;charset=utf8mb4", 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e2) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    } else {
        die("Koneksi database gagal: " . $e->getMessage());
    }
}
?>

