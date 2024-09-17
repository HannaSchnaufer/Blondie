<?php
$host = 'localhost'; // Tempat database berada
$db   = 'pinus';     // Nama database yang digunakan
$user = 'root';      // Username untuk akses database
$pass = '';          // Password untuk akses database (kosong jika tidak ada)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>
