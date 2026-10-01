<?php
require_once '../database/koneksi.php';
require_once 'midtrans_config.php'; // Include config untuk mengambil Server Key

// Gunakan key dari config file
$serverKey = $midtrans_server_key;

// Contoh data simulasi yang akan membeli VIP
$user_id = 1; // ID User di tabel users
$plan_id = 1; // ID Paket VIP di tabel vip_plans
$amount = 3000; // Harga VIP 1 Hari (Sesuai seed_data.sql)

try {
    // === TAMBAHAN BARU: Auto-create Dummy User jika belum ada ===
    $cekUser = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $cekUser->execute([$user_id]);
    if (!$cekUser->fetch()) {
        $buatUser = $pdo->prepare("INSERT INTO users (id, telegram_user_id, username, first_name) VALUES (?, 123456789, 'dummy_user', 'User')");
        $buatUser->execute([$user_id]);
    }
    // ============================================================

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

            echo "<h2>Scan QR Code di bawah ini untuk membayar</h2>";
            echo "<p>Order ID: <b>$order_id</b></p>";
            echo "<p>Total Bayar: <b>Rp " . number_format($amount, 0, ',', '.') . "</b></p>";
            echo "<img src='$qr_url' alt='QR Code' style='width:300px; height:300px;'>";
        } else {
            $pdo->rollBack();
            echo "Gagal mendapatkan URL QR Code dari response Midtrans.";
        }
    } else {
        $pdo->rollBack();
        echo "Midtrans API Error:<br><pre>" . print_r($result, true) . "</pre>";
    }
} catch (Exception $e) {
    // Jika ada yang error, batalkan semua query ke DB
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Terjadi kesalahan: " . $e->getMessage());
}
?>
