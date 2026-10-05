<?php
require_once '../database/koneksi.php';
require_once 'midtrans_config.php'; // Include config untuk mengambil Server Key

// Gunakan key dari config file
$serverKey = $midtrans_server_key;

// Tangkap data dari URL (GET) yang dikirim oleh Frontend
$tg_user_id = isset($_GET['tg_user_id']) ? $_GET['tg_user_id'] : 123456789;
$username = isset($_GET['username']) ? $_GET['username'] : 'dummy_user';
$first_name = isset($_GET['first_name']) ? $_GET['first_name'] : 'User';

$plan_id = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 1;

try {
    // KEAMANAN: Ambil harga ASLI dari database berdasarkan plan_id
    $cekPlan = $pdo->prepare("SELECT price FROM vip_plans WHERE id = ?");
    $cekPlan->execute([$plan_id]);
    $planData = $cekPlan->fetch(PDO::FETCH_ASSOC);

    if (!$planData) {
        die("Error: Paket VIP tidak valid atau tidak ditemukan.");
    }
    
    // Gunakan harga dari database, BUKAN dari input user (URL)
    $amount = (int)$planData['price'];

    // Cari user berdasarkan tg_user_id
    $cekUser = $pdo->prepare("SELECT id FROM users WHERE telegram_user_id = ?");
    $cekUser->execute([$tg_user_id]);
    $user = $cekUser->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_id = $user['id']; // Ambil ID internal tabel users
    } else {
        // Auto-create user jika belum ada di database
        $buatUser = $pdo->prepare("INSERT INTO users (telegram_user_id, username, first_name) VALUES (?, ?, ?)");
        $buatUser->execute([$tg_user_id, $username, $first_name]);
        $user_id = $pdo->lastInsertId();
    }

    // Memulai Transaksi Database
    $pdo->beginTransaction();

    // 1. Catat ke tabel vip_purchases
    $stmt = $pdo->prepare("INSERT INTO vip_purchases (user_id, plan_id, amount, payment_method, status) VALUES (?, ?, ?, 'qris', 'pending')");
    $stmt->execute([$user_id, $plan_id, $amount]);
    $vip_purchase_id = $pdo->lastInsertId();

    // 2. Buat order_id unik untuk Midtrans
    $order_id = "VIP-" . $vip_purchase_id . "-" . time();

    // 3. Request ke API Midtrans
    $payload = [
        "payment_type" => "qris",
        "transaction_details" => [
            "order_id" => $order_id,
            "gross_amount" => $amount
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.midtrans.com/v2/charge");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Accept: application/json",
        "Authorization: Basic " . base64_encode($serverKey . ":")
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);

    // 4. Proses response dari Midtrans
    if (isset($result['status_code']) && $result['status_code'] == '201') {
        $qr_url = '';
        foreach ($result['actions'] as $action) {
            if ($action['name'] == 'generate-qr-code') {
                $qr_url = $action['url'];
            }
        }

        if ($qr_url) {
            // Waktu kedaluwarsa QR (contoh: 15 menit)
            $expired_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Simpan detail pembayaran QRIS ke qris_payments
            $stmtQris = $pdo->prepare("INSERT INTO qris_payments (vip_purchase_id, gateway_name, gateway_ref_id, qris_payload, qris_image_url, gateway_status, expired_at) VALUES (?, 'midtrans', ?, ?, ?, 'pending', ?)");
            $stmtQris->execute([$vip_purchase_id, $order_id, json_encode($result), $qr_url, $expired_at]);
            
            // Simpan semua query
            $pdo->commit();

            // Return JSON response untuk dibaca oleh frontend Javascript (Modal)
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'order_id' => $order_id,
                'amount' => $amount,
                'qr_url' => $qr_url
            ]);
            exit;
        } else {
            $pdo->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal mendapatkan URL QR Code dari response Midtrans.']);
            exit;
        }
    } else {
        $pdo->rollBack();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Midtrans API Error']);
        exit;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    exit;
}
?>
