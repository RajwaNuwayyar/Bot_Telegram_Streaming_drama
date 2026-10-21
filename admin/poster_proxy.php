<?php
// Proxy untuk mengambil file poster Telegram di admin panel
$file_id = isset($_GET['fid']) ? trim($_GET['fid']) : '';

if (empty($file_id)) {
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="450"><rect width="100%" height="100%" fill="#111827"/><text x="50%" y="50%" font-family="sans-serif" font-size="16" fill="#64748B" text-anchor="middle" dy=".3em">No Poster</text></svg>';
    exit;
}

// Jika fid sudah merupakan URL web langsung
if (preg_match('/^https?:\/\//i', $file_id)) {
    header("Location: " . $file_id);
    exit;
}

// Ambil Token Bot dari file .env
$bot_token = '';
$envPath = __DIR__ . '/../.env';
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
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="450"><rect width="100%" height="100%" fill="#111827"/><text x="50%" y="50%" font-family="sans-serif" font-size="14" fill="#EF4444" text-anchor="middle" dy=".3em">BOT_TOKEN Not Configured</text></svg>';
    exit;
}

// Dapatkan file_path dari file_id Telegram
$url = "https://api.telegram.org/bot" . $bot_token . "/getFile?file_id=" . urlencode($file_id);
$ctx = stream_context_create([
    'http' => ['timeout' => 5],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]);
$response = @file_get_contents($url, false, $ctx);

if ($response) {
    $data = json_decode($response, true);
    if (!empty($data['ok']) && !empty($data['result']['file_path'])) {
        $filePath = $data['result']['file_path'];
        $fileUrl = "https://api.telegram.org/file/bot" . $bot_token . "/" . $filePath;
        
        header("Cache-Control: public, max-age=86400");
        header("Location: " . $fileUrl);
        exit;
    }
}

// Fallback jika file_id tidak valid atau gagal diambil
header('Content-Type: image/svg+xml');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="450"><rect width="100%" height="100%" fill="#111827"/><text x="50%" y="45%" font-family="sans-serif" font-size="14" fill="#94A3B8" text-anchor="middle">Poster Telegram</text><text x="50%" y="55%" font-family="sans-serif" font-size="11" fill="#64748B" text-anchor="middle">Gagal Memuat Gambar</text></svg>';
exit;
