<?php
session_start();
require_once '../config/database.php';
require_once '../models/TransaksiMasuk.php';
require_once '../models/TransaksiKeluar.php';
require_once '../models/Produk.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$produk = new Produk($db);

$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch($action) {
    case 'index':
        // Gunakan salah satu child class untuk readAll (method dari parent)
        $transaksi = new TransaksiMasuk($db);
        $stmt = $transaksi->readAll();
        include '../views/transaksi/index.php';
        break;
        
    case 'create':
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $jenis = $_POST['jenis_transaksi'];
            
            // POLYMORPHISM: Pilih class sesuai jenis transaksi
            if($jenis == 'masuk') {
                $transaksi = new TransaksiMasuk($db);
            } else {
                $transaksi = new TransaksiKeluar($db);
            }
            
            // Set data transaksi
            $transaksi->id_produk = $_POST['id_produk'];
            $transaksi->jumlah = $_POST['jumlah'];
            $transaksi->tanggal = $_POST['tanggal'];
            $transaksi->keterangan = $_POST['keterangan'];
            
            // Validasi stok (POLYMORPHISM IN ACTION!)
            // - TransaksiMasuk: selalu return true
            // - TransaksiKeluar: cek stok dulu
            if(!$transaksi->validateStock()) {
                $_SESSION['error'] = "Stok tidak mencukupi untuk transaksi keluar!";
            } else {
                // Simpan transaksi (method save() BERBEDA per class)
                // - TransaksiMasuk: langsung simpan + tambah stok
                // - TransaksiKeluar: validasi stok dulu, baru kurang stok
                if($transaksi->save()) {
                    $_SESSION['success'] = "Transaksi berhasil disimpan";
                    header("Location: TransaksiController.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Gagal menyimpan transaksi";
                }
            }
        }
        
        $stmt_produk = $produk->readAll();
        $produkList = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);
        include '../views/transaksi/create.php';
        break;
        
    case 'delete':
        if(isset($_GET['id'])) {
            // Pakai salah satu child class, method delete() ada di parent
            $transaksi = new TransaksiMasuk($db);
            $transaksi->id_transaksi = $_GET['id'];
            
            if($transaksi->delete()) {
                $_SESSION['success'] = "Transaksi berhasil dihapus";
            } else {
                $_SESSION['error'] = "Gagal menghapus transaksi";
            }
        }
        header("Location: TransaksiController.php");
        exit();
        
    case 'cetak_laporan':
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        
        $transaksi = new TransaksiMasuk($db);
        $stmt = $transaksi->readLaporan($start_date, $end_date);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        include '../views/transaksi/cetak_laporan.php';
        break;
        
    default:
        $transaksi = new TransaksiMasuk($db);
        $stmt = $transaksi->readAll();
        include '../views/transaksi/index.php';
        break;
}
?>