<?php
// Proxy untuk mengambil file (poster) dari Telegram
require_once __DIR__ . '/../../database/koneksi.php';

$file_id = isset($_GET['fid']) ? trim($_GET['fid']) : '';

if (empty($file_id)) {
    // Tampilkan placeholder jika file_id kosong
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="300"><rect width="100%" height="100%" fill="#141C2B"/><text x="50%" y="50%" font-family="sans-serif" font-size="20" fill="#8A99AF" text-anchor="middle" dy=".3em">No Poster</text></svg>';
    exit;
}

// Jika fid sudah merupakan URL langsung (HTTP/HTTPS)
if (preg_match('/^https?:\/\//i', $file_id)) {
    header("Location: " . $file_id);
    exit;
}

// Ambil Token secara dinamis dari .env
$bot_token = '';
$envPath = __DIR__ . '/../../.env';
if (file_exists($envPath)) {
    $envContent = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envContent as $line) {
        $line = trim($line);
        if (strpos($line, 'BOT_TOKEN=') === 0) {
            $bot_token = trim(substr($line, 10));
            break;
        }
    }
}
if (empty($bot_token)) {
    $bot_token = "8975561353:AAGyjm4yTqVfw9emU1-lui3qXMew48xNec8";
}

// 1. Dapatkan file_path dari file_id
$url = "https://api.telegram.org/bot" . $bot_token . "/getFile?file_id=" . $file_id;
$response = @file_get_contents($url);
if ($response) {
    $data = json_decode($response, true);
    if ($data['ok'] && isset($data['result']['file_path'])) {
        $file_path = $data['result']['file_path'];
        $file_url = "https://api.telegram.org/file/bot" . $bot_token . "/" . $file_path;
        
        // Redirect ke gambar aslinya (Telegram proxy image fetch)
        header("Location: " . $file_url);
        exit;
    }
}

// Fallback
header('Content-Type: image/svg+xml');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="300"><rect width="100%" height="100%" fill="#141C2B"/><text x="50%" y="50%" font-family="sans-serif" font-size="20" fill="#8A99AF" text-anchor="middle" dy=".3em">Error</text></svg>';
exit;
?>
