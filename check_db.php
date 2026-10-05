<?php
require 'database/koneksi.php';
$stmt = $pdo->query('SELECT * FROM vip_purchases');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = $pdo->query('SELECT * FROM qris_payments');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
