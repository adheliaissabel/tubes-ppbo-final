<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>HALO DARI DASHBOARD!</h1>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Belum Login') . "<br>";
echo "Nama: " . ($_SESSION['nama_lengkap'] ?? '-') . "<br>";

// Tes Panggil Database
echo "<h3>Mencoba panggil database...</h3>";
require_once '../config/database.php';
echo "File database.php ditemukan.<br>";

$database = new Database();
$db = $database->getConnection();
echo "<h2 style='color:green'>KONEKSI SUKSES!</h2>";
?>