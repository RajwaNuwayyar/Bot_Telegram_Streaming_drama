<?php
session_start();
header('Content-Type: application/json');

require_once '../../database/koneksi.php';

// Cek autentikasi
$telegram_user_id = isset($_SESSION['telegram_user_id']) ? $_SESSION['telegram_user_id'] : null;
if (!$telegram_user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Ambil input JSON
$input = json_decode(file_get_contents('php://input'), true);
$amount = isset($input['amount']) ? (int)$input['amount'] : 0;
$dana_number = isset($input['dana_number']) ? trim($input['dana_number']) : '';

if ($amount < 50000 || empty($dana_number)) {
    echo json_encode(['success' => false, 'message' => 'Nominal minimal Rp 50.000 dan nomor DANA harus diisi.']);
    exit;
}

try {
    // Ambil data user
    $stmt = $pdo->prepare("SELECT id, username, first_name FROM users WHERE telegram_user_id = ?");
    $stmt->execute([$telegram_user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $user_id = $user['id'];
    $display_name = $user['username'] ? '@'.$user['username'] : $user['first_name'];

    // Panggil Stored Procedure
    // CALL sp_request_withdrawal(p_user_id, p_amount_coin, p_bank_name, p_account_number, p_account_holder, @ok, @msg)
    $stmt = $pdo->prepare("CALL sp_request_withdrawal(?, ?, ?, ?, ?, @ok, @msg)");
    $stmt->execute([
        $user_id,
        $amount, // karena 1 coin = 1 rupiah
        'DANA',
        $dana_number,
        $display_name // kita gunakan nama display sebagai account_holder
    ]);
    $stmt->closeCursor();

    // Ambil hasil OUT
    $res = $pdo->query("SELECT @ok AS ok, @msg AS msg")->fetch(PDO::FETCH_ASSOC);

    if ($res['ok']) {
        // Cari ID withdrawal yang baru saja dibuat (terakhir oleh user ini yang pending)
        $stmt_wd = $pdo->prepare("SELECT id FROM affiliate_withdrawals WHERE user_id = ? AND status = 'diproses' ORDER BY id DESC LIMIT 1");
        $stmt_wd->execute([$user_id]);
        $wd_id = $stmt_wd->fetchColumn();

        // Load config untuk BOT_TOKEN & ADMIN_USER_ID
        $env_file = __DIR__ . '/../../.env';
        $bot_token = '';
        $admin_id = '';
        if (file_exists($env_file)) {
            $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($name, $value) = explode('=', $line, 2) + [NULL, NULL];
                if ($name === 'BOT_TOKEN') $bot_token = trim($value);
                if ($name === 'ADMIN_USER_ID') $admin_id = trim($value);
            }
        }

        if (!empty($bot_token) && !empty($admin_id)) {
            $msg_text = "🚨 *PERMINTAAN PENARIKAN (WD)* 🚨\n\n"
                      . "🆔 *ID Penarikan:* `#WD_{$wd_id}`\n"
                      . "👤 *User:* {$display_name} (ID: {$telegram_user_id})\n"
                      . "💰 *Jumlah:* Rp " . number_format($amount, 0, ',', '.') . "\n"
                      . "🏦 *Metode:* DANA\n"
                      . "💳 *Nomor:* {$dana_number}\n\n"
                      . "_⚠️ Balas pesan ini dengan BUKTI TRANSFER, atau kirim foto dengan caption_ `#WD_{$wd_id}` _untuk menyelesaikan penarikan._";

            // Kirim pesan ke Admin via API Telegram langsung
            $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
            $data = [
                'chat_id' => $admin_id,
                'text' => $msg_text,
                'parse_mode' => 'Markdown'
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_exec($ch);
            curl_close($ch);
        }

        echo json_encode(['success' => true, 'message' => $res['msg']]);
    } else {
        echo json_encode(['success' => false, 'message' => $res['msg']]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
