<?php
$host = "localhost";
$user = "root";
$pass = ""; // Sesuaikan password database Anda
$db   = "bot_drama"; // Ganti dengan nama database Anda yang sebenarnya

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
