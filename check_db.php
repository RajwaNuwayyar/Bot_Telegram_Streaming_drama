<?php
require 'database/koneksi.php';

echo "<h3>Data Users:</h3><pre>";
$stmt = $pdo->query('SELECT * FROM users');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";

// Jika di databasemu ada tabel bernama watch_history atau history, bisa di-uncomment kode di bawah ini:
/*
echo "<h3>Data Riwayat Nonton:</h3><pre>";
$stmt = $pdo->query('SELECT * FROM watch_history');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "</pre>";
*/
?>
