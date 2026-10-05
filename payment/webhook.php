<?php
require_once '../database/koneksi.php';

$json_result = file_get_contents('php://input');
$result = json_decode($json_result, true);

if ($result) {
    $order_id = $result['order_id']; // Sesuai gateway_ref_id di tabel qris_payments
    $transaction_status = $result['transaction_status'];
    $fraud_status = isset($result['fraud_status']) ? $result['fraud_status'] : '';

    $status_gateway = 'pending';
    $status_vip = 'pending';

    // Menentukan status berdasarkan callback Midtrans
    if ($transaction_status == 'capture' || $transaction_status == 'settlement') {
        if ($fraud_status == 'challenge') {
            $status_gateway = 'challenge';
            $status_vip = 'pending';
        } else {
            $status_gateway = 'success';
            $status_vip = 'paid';
        }
    } else if ($transaction_status == 'cancel' || $transaction_status == 'deny' || $transaction_status == 'expire') {
        $status_gateway = 'failed';
        $status_vip = 'failed'; // Bisa juga diset expired sesuai kebutuhan
    }

    try {
        $pdo->beginTransaction();

        // Cari transaksi berdasarkan order_id Midtrans
        $stmt = $pdo->prepare("SELECT vip_purchase_id, gateway_status FROM qris_payments WHERE gateway_ref_id = ? FOR UPDATE");
        $stmt->execute([$order_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Cegah overwrite jika transaksi SUDAH sukses/paid
            if (($row['gateway_status'] == 'success' || $row['gateway_status'] == 'paid') && $status_gateway == 'failed') {
                $pdo->rollBack();
                http_response_code(200);
                echo "Transaksi sudah sukses sebelumnya, mengabaikan status failed/expire.";
                exit;
            }

            $vip_purchase_id = $row['vip_purchase_id'];
            $now = date('Y-m-d H:i:s');

            // 1. Update status di tabel qris_payments
            $stmtUpdQris = $pdo->prepare("UPDATE qris_payments SET gateway_status = ?, callback_payload = ?, paid_at = IF(? = 'success', ?, paid_at) WHERE gateway_ref_id = ?");
            $stmtUpdQris->execute([$status_gateway, $json_result, $status_gateway, $now, $order_id]);

            // 2. Update status di tabel vip_purchases
            $stmtUpdVip = $pdo->prepare("UPDATE vip_purchases SET status = ?, paid_at = IF(? = 'paid', ?, paid_at) WHERE id = ?");
            $stmtUpdVip->execute([$status_vip, $status_vip, $now, $vip_purchase_id]);

            // 3. Tambahkan logika pengaktifan VIP di tabel users jika pembayaran sukses (paid)
            if ($status_vip == 'paid') {
                // Ambil info paket VIP (durasi) dan User ID
                $stmtPlan = $pdo->prepare("SELECT vp.duration_days, v.user_id FROM vip_purchases v JOIN vip_plans vp ON v.plan_id = vp.id WHERE v.id = ?");
                $stmtPlan->execute([$vip_purchase_id]);
                $planData = $stmtPlan->fetch(PDO::FETCH_ASSOC);

                if ($planData) {
                    $duration = (int)$planData['duration_days'];
                    $user_id = $planData['user_id'];
                    $vip_start = $now;
                    $vip_end = date('Y-m-d H:i:s', strtotime("+$duration days"));

                    // Update periode aktif di tabel vip_purchases
                    $stmtSetVipDate = $pdo->prepare("UPDATE vip_purchases SET vip_start_at = ?, vip_end_at = ? WHERE id = ?");
                    $stmtSetVipDate->execute([$vip_start, $vip_end, $vip_purchase_id]);

                    // Cek apakah user saat ini masih punya durasi VIP yang aktif
                    $stmtUser = $pdo->prepare("SELECT vip_until FROM users WHERE id = ?");
                    $stmtUser->execute([$user_id]);
                    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
                    
                    if ($userData && !empty($userData['vip_until']) && strtotime($userData['vip_until']) > time()) {
                        // Jika masih aktif, akumulasikan/tambahkan sisa harinya
                        $vip_end = date('Y-m-d H:i:s', strtotime($userData['vip_until'] . " +$duration days"));
                    }

                    // Update vip_until di tabel users
                    $stmtUpdUser = $pdo->prepare("UPDATE users SET vip_until = ? WHERE id = ?");
                    $stmtUpdUser->execute([$vip_end, $user_id]);
                }
            }

            $pdo->commit();
            http_response_code(200);
            echo "Webhook sukses dan database telah diperbarui.";
        } else {
            $pdo->rollBack();
            http_response_code(404);
            echo "Order ID tidak ditemukan di database.";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo "Error DB: " . $e->getMessage();
    }
} else {
    http_response_code(400);
    echo "Invalid JSON Payload.";
}
?>
