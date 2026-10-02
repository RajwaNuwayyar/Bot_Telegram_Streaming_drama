<?php
// Load environment variables dari file .env di root directory
$envPath = __DIR__ . '/../.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Abaikan komentar
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            // Hilangkan tanda kutip jika ada
            $value = trim($value, '"\''); 
            $_ENV[$name] = $value;
        }
    }
}

// Ambil server key dari $_ENV, pastikan fallback menangani jika key tidak ada
$midtrans_server_key = isset($_ENV['MIDTRANS_SERVER_KEY']) ? $_ENV['MIDTRANS_SERVER_KEY'] : '';

if (empty($midtrans_server_key)) {
    die("Error: MIDTRANS_SERVER_KEY tidak ditemukan di file .env");
}
?>
