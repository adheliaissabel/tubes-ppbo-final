<?php
session_start();

// 1. AKTIFKAN DEBUGGING AGAR ERROR MUNCUL
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cek Login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

try {
    // 2. CEK KETERSEDIAAN FILE SEBELUM DI-LOAD (Supaya ketahuan file mana yang hilang)
    $files_to_check = [
        '../config/database.php',
        '../models/Produk.php',
        '../models/Kategori.php',
        '../models/TransaksiMasuk.php', // <-- TERSANGKA UTAMA
        '../models/TransaksiKeluar.php' // <-- TERSANGKA UTAMA
    ];

    foreach ($files_to_check as $file) {
        if (!file_exists(__DIR__ . '/' . $file)) {
            throw new Exception("File Hilang: <b>" . $file . "</b> tidak ditemukan di folder models.");
        }
        require_once $file;
    }

    $database = new Database();
    $db = $database->getConnection();

    // 3. INSTANSIASI CLASS
    $produk = new Produk($db);
    $kategori = new Kategori($db);
    
    // Cek apakah class TransaksiMasuk ada?
    if (!class_exists('TransaksiMasuk')) {
        throw new Exception("Class <b>TransaksiMasuk</b> tidak ditemukan. Cek isi file models/TransaksiMasuk.php");
    }
    $transaksiMasuk = new TransaksiMasuk($db);
    
    // Cek apakah class TransaksiKeluar ada?
    if (!class_exists('TransaksiKeluar')) {
        throw new Exception("Class <b>TransaksiKeluar</b> tidak ditemukan. Cek isi file models/TransaksiKeluar.php");
    }
    $transaksiKeluar = new TransaksiKeluar($db);

    // 4. EKSEKUSI DATA
    $total_produk = $produk->readAll()->rowCount();
    $total_kategori = $kategori->readAll()->rowCount();

    $data_masuk = $transaksiMasuk->readAll()->fetchAll(PDO::FETCH_ASSOC);
    $data_keluar = $transaksiKeluar->readAll()->fetchAll(PDO::FETCH_ASSOC);

    $all_transaksi = array_merge($data_masuk, $data_keluar);

    usort($all_transaksi, function($a, $b) {
        return strtotime($b['tanggal']) - strtotime($a['tanggal']);
    });

    $total_transaksi = count($all_transaksi);

    $stmt_nilai = $produk->readAll();
    $total_nilai_stok = 0;
    while($row = $stmt_nilai->fetch(PDO::FETCH_ASSOC)) {
        $harga_hitung = isset($row['harga_beli']) && $row['harga_beli'] > 0 ? $row['harga_beli'] : ($row['harga'] ?? 0);
        $total_nilai_stok += ($row['stok'] * $harga_hitung);
    }

    // Cek Method getLowStock
    if (!method_exists($produk, 'getLowStock')) {
        throw new Exception("Method <b>getLowStock()</b> belum dibuat di file models/Produk.php!");
    }
    $stmt_low = $produk->getLowStock(10);
    $low_stock_data = $stmt_low ? $stmt_low->fetchAll(PDO::FETCH_ASSOC) : [];

} catch (Exception $e) {
    // TAMPILKAN ERROR DENGAN JELAS
    die("<div style='background:red; color:white; padding:20px; font-family:sans-serif;'>
        <h1>TERJADI ERROR!</h1>
        <h3>" . $e->getMessage() . "</h3>
        <p>Lokasi: " . $e->getFile() . " baris " . $e->getLine() . "</p>
        <button onclick='window.history.back()'>Kembali</button>
        </div>");
} catch (Error $e) {
    // TANGKAP FATAL ERROR PHP
    die("<div style='background:darkred; color:white; padding:20px; font-family:sans-serif;'>
        <h1>FATAL ERROR (PHP Crash)!</h1>
        <h3>" . $e->getMessage() . "</h3>
        <p>Lokasi: " . $e->getFile() . " baris " . $e->getLine() . "</p>
        </div>");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SIGUDA PPBO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php 
    $navbar_path = 'layouts/navbar.php';
    if(file_exists($navbar_path)) {
        include $navbar_path; 
    } else {
        echo "<div class='alert alert-warning'>⚠️ File Navbar tidak ditemukan di: <b>views/$navbar_path</b>. <br>Cek struktur foldermu.</div>";
    }
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h4 class="card-title">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>!</h4>
                    <p class="text-muted mb-0">Anda login sebagai <strong><?php echo ucfirst($_SESSION['role']); ?></strong></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3 h-100 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Produk</h6>
                        <h2 class="mt-2 mb-0"><?php echo $total_produk; ?></h2>
                    </div>
                    <i class="bi bi-box-seam display-4 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3 h-100 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Kategori</h6>
                        <h2 class="mt-2 mb-0"><?php echo $total_kategori; ?></h2>
                    </div>
                    <i class="bi bi-tags display-4 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3 h-100 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Transaksi</h6>
                        <h2 class="mt-2 mb-0"><?php echo $total_transaksi; ?></h2>
                    </div>
                    <i class="bi bi-arrow-left-right display-4 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3 h-100 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Nilai Aset</h6>
                        <h4 class="mt-2 mb-0">Rp <?php echo number_format($total_nilai_stok, 0, ',', '.'); ?></h4>
                    </div>
                    <i class="bi bi-cash-coin display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>