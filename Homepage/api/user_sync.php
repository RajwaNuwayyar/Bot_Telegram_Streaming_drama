<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../database/koneksi.php';

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$telegram_id = $data['id'];
$first_name = isset($data['first_name']) ? $data['first_name'] : '';
$last_name = isset($data['last_name']) ? $data['last_name'] : '';
$username = isset($data['username']) ? $data['username'] : '';
$language_code = isset($data['language_code']) ? $data['language_code'] : 'id';

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE telegram_user_id = ?");
    $stmt->execute([$telegram_id]);
    $user = $stmt->fetch();

    if ($user) {
        // Update existing user
        $updateStmt = $pdo->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, username = ?, language_code = ?, last_active_at = NOW() 
            WHERE telegram_user_id = ?
        ");
        $updateStmt->execute([$first_name, $last_name, $username, $language_code, $telegram_id]);
    } else {
        // Insert new user
        $insertStmt = $pdo->prepare("
            INSERT INTO users (telegram_user_id, first_name, last_name, username, language_code) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $insertStmt->execute([$telegram_id, $first_name, $last_name, $username, $language_code]);
    }
    
    // Set Session
    $_SESSION['telegram_user_id'] = $telegram_id;
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
