<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../database/koneksi.php';

// Parse .env for Bot Token and Admin ID
$env = file_get_contents(__DIR__ . '/../../.env');
$lines = explode("\n", $env);
$botToken = '';
$adminId = '';
foreach ($lines as $line) {
    if (strpos(trim($line), 'BOT_TOKEN=') === 0) {
        $botToken = trim(explode('=', $line, 2)[1]);
    }
    if (strpos(trim($line), 'ADMIN_USER_ID=') === 0) {
        $adminId = trim(explode('=', $line, 2)[1]);
    }
}

$telegram_user_id = isset($_SESSION['telegram_user_id']) ? $_SESSION['telegram_user_id'] : null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$title = isset($data['title']) ? trim($data['title']) : '';
$source = isset($data['source']) ? trim($data['source']) : '';
$notes = isset($data['notes']) ? trim($data['notes']) : '';

if (empty($title)) {
    echo json_encode(['success' => false, 'error' => 'Title is required']);
    exit;
}

try {
    $user = null;
    if ($telegram_user_id) {
        $stmt = $pdo->prepare("SELECT id, username, first_name, vip_until FROM users WHERE telegram_user_id = ?");
        $stmt->execute([$telegram_user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit;
    }
    
    $user_id = $user['id'];
    
    // Check daily limit (max 2 per day)
    $stmtLimit = $pdo->prepare("SELECT COUNT(*) FROM film_requests WHERE user_id = ? AND DATE(created_at) = CURDATE()");
    $stmtLimit->execute([$user_id]);
    $dailyRequests = $stmtLimit->fetchColumn();
    
    if ($dailyRequests >= 2) {
        echo json_encode(['success' => false, 'error' => 'Anda telah mencapai batas 2 request per hari.']);
        exit;
    }

    // Check VIP priority
    $is_priority = 0;
    $is_vip = false;
    if ($user['vip_until'] && strtotime($user['vip_until']) > time()) {
        $is_priority = 1;
        $is_vip = true;
    }

    // Insert into DB
    $stmtInsert = $pdo->prepare("INSERT INTO film_requests (user_id, requested_title, source_app, notes, is_priority) VALUES (?, ?, ?, ?, ?)");
    $stmtInsert->execute([$user_id, $title, $source, $notes, $is_priority]);
    
    // Send to Telegram Admin
    if ($botToken && $adminId) {
        $usernameText = $user['username'] ? "@" . $user['username'] : $user['first_name'];
        $priorityText = $is_vip ? "⭐ VIP Member (Prioritas)" : "⚪ Member Reguler";
        
        $message = "📬 *REQUEST DRAMA BARU!*\n━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "👤 *User:* " . htmlspecialchars($usernameText) . " (ID: $telegram_user_id)\n";
        $message .= "🎬 *Drama:* " . htmlspecialchars($title) . "\n";
        $message .= "📱 *Source:* " . ($source ? htmlspecialchars($source) : "-") . "\n";
        $message .= "📝 *Notes:* " . ($notes ? htmlspecialchars($notes) : "-") . "\n";
        $message .= "⭐ *Priority:* " . $priorityText . "\n\n";
        $message .= "Total request hari ini oleh user ini: " . ($dailyRequests + 1);
        
        $url = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
        $postData = [
            'chat_id' => $adminId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_exec($ch);
        curl_close($ch);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
