<?php
// Endpoint untuk mengirimkan tagihan transaksi & foto QRIS langsung ke chat bot Telegram pengguna
header('Content-Type: application/json');

require_once __DIR__ . '/../database/koneksi.php';

// Ambil konfigurasi BOT_TOKEN dari .env
$botToken = '';
$botUsername = 'TreadLessBot';
$webAppUrl = 'https://t.me/TreadLessBot/myapp';

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            if ($key === 'BOT_TOKEN') $botToken = $val;
            if ($key === 'BOT_USERNAME') $botUsername = ltrim($val, '@');
            if ($key === 'WEB_APP_URL') $webAppUrl = $val;
        }
    }
}

// Fallback jika tidak ada di .env
if (empty($botToken)) {
    $botToken = getenv('BOT_TOKEN');
}

if (empty($botToken)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Token bot Telegram belum dikonfigurasi pada server.']);
    exit;
}

$order_id = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';
$tg_user_id = isset($_GET['tg_user_id']) ? trim($_GET['tg_user_id']) : '';

if (empty($order_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter order_id wajib disertakan.']);
    exit;
}

// Cari transaksi di database
$txData = null;
try {
    $stmt = $pdo->prepare("
        SELECT vp.id as purchase_id, vp.amount, vp.status as vp_status,
               qp.gateway_ref_id, qp.qris_image_url, qp.expired_at,
               u.telegram_user_id, u.first_name, u.username,
               p.name as plan_name, p.duration_days
        FROM qris_payments qp
        JOIN vip_purchases vp ON qp.vip_purchase_id = vp.id
        JOIN users u ON vp.user_id = u.id
        LEFT JOIN vip_plans p ON vp.plan_id = p.id
        WHERE qp.gateway_ref_id = ?
        ORDER BY qp.id DESC LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $txData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fallback jika order_id berupa angka ID pembelian
    if (!$txData && is_numeric($order_id)) {
        $stmt2 = $pdo->prepare("
            SELECT vp.id as purchase_id, vp.amount, vp.status as vp_status,
                   qp.gateway_ref_id, qp.qris_image_url, qp.expired_at,
                   u.telegram_user_id, u.first_name, u.username,
                   p.name as plan_name, p.duration_days
            FROM vip_purchases vp
            LEFT JOIN qris_payments qp ON qp.vip_purchase_id = vp.id
            JOIN users u ON vp.user_id = u.id
            LEFT JOIN vip_plans p ON vp.plan_id = p.id
            WHERE vp.id = ?
            ORDER BY vp.id DESC LIMIT 1
        ");
        $stmt2->execute([(int)$order_id]);
        $txData = $stmt2->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database query error: ' . $e->getMessage()]);
    exit;
}

if (!$txData) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Data transaksi tidak ditemukan di database.']);
    exit;
}

// Tentukan Telegram chat ID target
$targetTelegramID = !empty($txData['telegram_user_id']) ? $txData['telegram_user_id'] : $tg_user_id;

if (empty($targetTelegramID) || !is_numeric($targetTelegramID)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Telegram User ID tidak ditemukan untuk transaksi ini.']);
    exit;
}

$qrImageUrl = !empty($txData['qris_image_url']) ? $txData['qris_image_url'] : '';
$orderRef = !empty($txData['gateway_ref_id']) ? $txData['gateway_ref_id'] : ('TRX-' . $txData['purchase_id']);
$planName = !empty($txData['plan_name']) ? $txData['plan_name'] : 'Paket VIP';
$amountFormatted = number_format((float)$txData['amount'], 0, ',', '.');

$expiredTime = '15:00';
if (!empty($txData['expired_at'])) {
    $expiredTime = date('H:i', strtotime($txData['expired_at']));
} else {
    $expiredTime = date('H:i', strtotime('+15 minutes'));
}

// Susun teks caption pesan bot Telegram
$caption = "💳 *TAGIHAN PEMBAYARAN QRIS*\n";
$caption .= "━━━━━━━━━━━━━━━━━━━━━━━━\n";
$caption .= "🆔 *ID Transaksi:* `" . $orderRef . "`\n";
$caption .= "📦 *Paket:* " . $planName . "\n";
$caption .= "💰 *Total Bayar:* *Rp " . $amountFormatted . "*\n";
$caption .= "⏳ *Batas Waktu:* " . $expiredTime . " WIB (15 Menit)\n";
$caption .= "━━━━━━━━━━━━━━━━━━━━━━━━\n";
if (!empty($qrImageUrl)) {
    $caption .= "🔗 *Link Gambar QRIS:*\n" . $qrImageUrl . "\n";
    $caption .= "━━━━━━━━━━━━━━━━━━━━━━━━\n";
}
$caption .= "\n*Langkah Pembayaran & Simpan QRIS:*\n";
$caption .= "1. *Ketuk foto QRIS di atas*, lalu klik ikon opsi (titik 3) dan pilih *Simpan ke Galeri*.\n";
$caption .= "2. Buka aplikasi *GoPay, OVO, DANA, ShopeePay, BCA, atau Mobile Banking*.\n";
$caption .= "3. Pilih menu *Scan / Bayar QRIS* lalu unggah gambar QRIS dari galeri HP Anda.\n";
$caption .= "4. Periksa nama merchant: *DRAMABOT VIP* & nominal: *Rp " . $amountFormatted . "*.\n";
$caption .= "5. Selesaikan pembayaran Anda.\n\n";
$caption .= "Setelah berhasil, status akun VIP Anda akan langsung aktif secara otomatis! 🚀";

// Susun tombol inline keyboard
$inlineKeyboard = [
    [
        ['text' => '🔄 Cek Status Pembayaran', 'callback_data' => 'check_pay:' . $orderRef]
    ]
];

if (!empty($qrImageUrl)) {
    $inlineKeyboard[] = [
        ['text' => '🔗 Buka Link Gambar QRIS', 'url' => $qrImageUrl]
    ];
}

$inlineKeyboard[] = [
    ['text' => '📱 Buka Mini App', 'url' => $webAppUrl]
];

$keyboardJson = json_encode(['inline_keyboard' => $inlineKeyboard]);

// Kirim ke Telegram API
$telegramApiUrl = "https://api.telegram.org/bot" . $botToken . "/";

if (!empty($qrImageUrl)) {
    // Kirim menggunakan sendPhoto
    $postFields = [
        'chat_id' => $targetTelegramID,
        'photo' => $qrImageUrl,
        'caption' => $caption,
        'parse_mode' => 'Markdown',
        'reply_markup' => $keyboardJson
    ];
    $ch = curl_init($telegramApiUrl . 'sendPhoto');
} else {
    // Fallback jika tidak ada link gambar, kirim sendMessage
    $postFields = [
        'chat_id' => $targetTelegramID,
        'text' => $caption,
        'parse_mode' => 'Markdown',
        'reply_markup' => $keyboardJson
    ];
    $ch = curl_init($telegramApiUrl . 'sendMessage');
}

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$apiResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$resData = json_decode($apiResponse, true);

if ($httpCode === 200 && isset($resData['ok']) && $resData['ok'] === true) {
    echo json_encode([
        'success' => true,
        'message' => 'Transaksi & gambar QRIS berhasil dikirim ke chat bot Telegram Anda!',
        'bot_username' => $botUsername
    ]);
} else {
    // Jika sendPhoto gagal (misal URL foto ditolak Telegram), coba fallback sendMessage
    $fallbackMsg = [
        'chat_id' => $targetTelegramID,
        'text' => $caption,
        'parse_mode' => 'Markdown',
        'reply_markup' => $keyboardJson
    ];
    $ch2 = curl_init($telegramApiUrl . 'sendMessage');
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_POST, true);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, $fallbackMsg);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    $apiResponse2 = curl_exec($ch2);
    $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);

    $resData2 = json_decode($apiResponse2, true);
    if ($httpCode2 === 200 && isset($resData2['ok']) && $resData2['ok'] === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Rincian transaksi & link QRIS berhasil dikirim ke chat bot Telegram Anda!',
            'bot_username' => $botUsername
        ]);
    } else {
        http_response_code(502);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal mengirim pesan ke chat Telegram: ' . (isset($resData['description']) ? $resData['description'] : 'Unknown error'),
            'detail' => $resData
        ]);
    }
}
