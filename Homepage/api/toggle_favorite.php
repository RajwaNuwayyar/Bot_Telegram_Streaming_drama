<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../database/koneksi.php';

// Ambil input dari JSON payload atau POST/GET
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$drama_id = isset($input['drama_id']) ? (int)$input['drama_id'] : (isset($_REQUEST['drama_id']) ? (int)$_REQUEST['drama_id'] : 0);
$tg_user_id = $input['tg_user_id'] ?? ($_REQUEST['tg_user_id'] ?? ($_SESSION['telegram_user_id'] ?? null));

if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'Database tidak terhubung']);
    exit;
}

if ($drama_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID Drama tidak valid']);
    exit;
}

if (empty($tg_user_id)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Akses ditolak. Silakan buka aplikasi melalui bot Telegram resmi @TreadLessBot.']);
    exit;
}

try {
    // 1. Dapatkan user_id dari database
    $userStmt = $pdo->prepare("SELECT id FROM users WHERE telegram_user_id = ? LIMIT 1");
    $userStmt->execute([$tg_user_id]);
    $userId = $userStmt->fetchColumn();

    if (!$userId) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Akun Telegram belum terdaftar. Buka bot Telegram terlebih dahulu.']);
        exit;
    }

    // 2. Pastikan drama valid
    $dramaStmt = $pdo->prepare("SELECT id, title FROM dramas WHERE id = ? LIMIT 1");
    $dramaStmt->execute([$drama_id]);
    $drama = $dramaStmt->fetch(PDO::FETCH_ASSOC);

    if (!$drama) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Drama tidak ditemukan di katalog']);
        exit;
    }

    // 3. Cek apakah sudah difavoritkan
    $checkStmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND drama_id = ?");
    $checkStmt->execute([$userId, $drama_id]);
    $isFav = (bool)$checkStmt->fetchColumn();

    if ($isFav) {
        // Hapus dari favorit
        $delStmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND drama_id = ?");
        $delStmt->execute([$userId, $drama_id]);
        $newStatus = false;
        $message = "Dihapus dari favorit";
        $action = "removed";
    } else {
        // Tambahkan ke favorit
        $addStmt = $pdo->prepare("INSERT INTO favorites (user_id, drama_id, created_at) VALUES (?, ?, NOW())");
        $addStmt->execute([$userId, $drama_id]);
        $newStatus = true;
        $message = "Berhasil ditambahkan ke favorit!";
        $action = "added";
    }

    // Hitung total favorit user sekarang
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
    $countStmt->execute([$userId]);
    $totalFavs = (int)$countStmt->fetchColumn();

    echo json_encode([
        'success'         => true,
        'is_favorite'     => $newStatus,
        'action'          => $action,
        'message'         => $message,
        'drama_title'     => $drama['title'],
        'favorites_count' => $totalFavs
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server: ' . $e->getMessage()]);
}
