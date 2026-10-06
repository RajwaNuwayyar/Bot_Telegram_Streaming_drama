<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../database/koneksi.php';

$telegram_user_id = isset($_SESSION['telegram_user_id']) ? $_SESSION['telegram_user_id'] : null;
$episode_id = isset($_GET['episode_id']) ? (int)$_GET['episode_id'] : 0;

if (!$telegram_user_id || !$episode_id) {
    echo json_encode(['success' => false, 'error' => 'Missing data']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE telegram_user_id = ?");
    $stmt->execute([$telegram_user_id]);
    $user_id = $stmt->fetchColumn();

    if (!$user_id) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }

    $stmtInsert = $pdo->prepare("
        INSERT INTO watch_history (user_id, episode_id, progress_seconds, is_completed, last_watched_at) 
        VALUES (?, ?, 0, 0, NOW()) 
        ON DUPLICATE KEY UPDATE last_watched_at = NOW()
    ");
    $stmtInsert->execute([$user_id, $episode_id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
