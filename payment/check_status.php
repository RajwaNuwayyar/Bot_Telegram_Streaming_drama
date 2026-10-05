<?php
require_once '../database/koneksi.php';

header('Content-Type: application/json');

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

if (empty($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT gateway_status FROM qris_payments WHERE gateway_ref_id = ?");
    $stmt->execute([$order_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($payment) {
        echo json_encode(['success' => true, 'status' => $payment['gateway_status']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
