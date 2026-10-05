<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../database/koneksi.php';
require_once __DIR__ . '/../includes/db_queries.php';

$telegram_user_id = isset($_SESSION['telegram_user_id']) ? $_SESSION['telegram_user_id'] : null;

try {
    $user = null;
    if ($telegram_user_id) {
        $user = getUserByTelegramId($pdo, $telegram_user_id);
    }
    
    // Fallback untuk browser testing jika belum sinkron Telegram
    if (!$user) {
        $user = $pdo->query("SELECT * FROM users ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User tidak ditemukan']);
        exit;
    }

    $success = clearUserWatchHistory($pdo, $user['id']);
    echo json_encode(['success' => (bool)$success]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
