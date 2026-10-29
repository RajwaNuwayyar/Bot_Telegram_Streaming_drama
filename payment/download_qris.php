<?php
require_once __DIR__ . '/../database/koneksi.php';

$order_id = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';
$custom_url = isset($_GET['url']) ? trim($_GET['url']) : '';

$qr_url = '';

// 1. Cari URL QRIS dari database jika ada order_id
if (!empty($order_id) && $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT qris_image_url FROM qris_payments WHERE gateway_ref_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$order_id]);
        $qr_url = $stmt->fetchColumn();

        // Fallback jika order_id berupa angka ID pembelian
        if (!$qr_url && is_numeric($order_id)) {
            $stmt2 = $pdo->prepare("SELECT qris_image_url FROM qris_payments WHERE vip_purchase_id = ? ORDER BY id DESC LIMIT 1");
            $stmt2->execute([(int)$order_id]);
            $qr_url = $stmt2->fetchColumn();
        }
    } catch (Exception $e) {
        $qr_url = '';
    }
}

// 2. Jika tidak ditemukan di database tapi ada parameter URL
if (empty($qr_url) && !empty($custom_url)) {
    // Validasi URL demi keamanan (hanya izinkan domain terpercaya)
    if (preg_match('/^https?:\/\/api\.(sandbox\.)?midtrans\.com\//i', $custom_url) || preg_match('/^https?:\/\//i', $custom_url)) {
        $qr_url = $custom_url;
    }
}

// Jika URL tetap tidak ditemukan
if (empty($qr_url)) {
    header('Content-Type: text/plain');
    http_response_code(404);
    echo "QRIS tidak ditemukan atau pesanan tidak valid.";
    exit;
}

// 3. Ambil data gambar QRIS dari Midtrans
$ch = curl_init($qr_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_USERAGENT, 'DramaBot/1.0');
$imageData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($httpCode !== 200 || empty($imageData)) {
    header('Content-Type: text/plain');
    http_response_code(502);
    echo "Gagal mengunduh gambar QRIS dari penyedia pembayaran.";
    exit;
}

// 4. Bersihkan nama file untuk attachment download
$safeFileName = 'QRIS-' . (!empty($order_id) ? preg_replace('/[^A-Za-z0-9_-]/', '', $order_id) : 'PAYMENT') . '.png';

// 5. Kirim headers download attachment
header('Content-Description: File Transfer');
header('Content-Type: ' . (!empty($contentType) ? $contentType : 'image/png'));
header('Content-Disposition: attachment; filename="' . $safeFileName . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . strlen($imageData));

// Flush output
echo $imageData;
exit;
