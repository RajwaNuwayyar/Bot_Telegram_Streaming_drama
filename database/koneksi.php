<?php
// Konfigurasi Database (Otomatis Mendukung Docker & Local XAMPP/Laragon)
$envHost = getenv('DB_HOST');
$envUser = getenv('DB_USER');
$envPass = getenv('DB_PASS');
$envDb   = getenv('DB_NAME');

// Deteksi otomatis apakah berjalan di dalam jaringan Docker (host 'db' ter-resolve)
$isDockerNetwork = (gethostbyname('db') !== 'db');

$host = $envHost ?: ($isDockerNetwork ? 'db' : 'localhost');
$user = $envUser ?: ($isDockerNetwork ? 'bot_user' : 'root');
$pass = ($envPass !== false && $envPass !== null) ? $envPass : ($isDockerNetwork ? 'bot_password' : '');
$db   = $envDb ?: 'drama_bot';

try {
    // 1. Coba koneksi primer sesuai hasil deteksi
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3,
    ]);
} catch(PDOException $e) {
    // 2. Fallback cerdas: jika host selain localhost gagal, coba localhost root (XAMPP/Laragon)
    if ($host !== 'localhost' && $host !== '127.0.0.1') {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=$db;charset=utf8mb4", 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 3,
            ]);
        } catch(PDOException $e2) {
            die("Koneksi database gagal: " . $e2->getMessage());
        }
    } else {
        // Fallback sebaliknya: jika localhost gagal tapi ada host docker 'db'
        try {
            $pdo = new PDO("mysql:host=db;dbname=$db;charset=utf8mb4", 'bot_user', 'bot_password', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 3,
            ]);
        } catch(PDOException $e3) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
}
?>
