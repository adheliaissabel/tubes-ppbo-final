<?php
session_start();

require_once '../config/database.php';
require_once '../models/TransaksiMasuk.php';
require_once '../models/TransaksiKeluar.php';
require_once '../models/Produk.php';

// Cek session user
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Koneksi database
$database = new Database();
$db = $database->getConnection();
$produk = new Produk($db);

$action = $_GET['action'] ?? 'index';

switch ($action) {

    // ======================
    // Tampilkan semua transaksi
    // ======================
    case 'index':
        $transaksiMasuk = new TransaksiMasuk($db);
        $transaksiKeluar = new TransaksiKeluar($db);

        $stmtMasuk = $transaksiMasuk->readAll();
        $stmtKeluar = $transaksiKeluar->readAll();

        $transaksiList = array_merge(
            $stmtMasuk->fetchAll(PDO::FETCH_ASSOC),
            $stmtKeluar->fetchAll(PDO::FETCH_ASSOC)
        );

        // Urutkan berdasarkan tanggal DESC
        usort($transaksiList, function($a, $b) {
            return strtotime($b['tanggal']) - strtotime($a['tanggal']);
        });

        include '../views/transaksi/index.php';
        break;

    // ======================
    // Tambah transaksi baru
    // ======================
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $jenis = $_POST['jenis_transaksi'] ?? 'masuk';
            $transaksi = ($jenis === 'masuk') ? new TransaksiMasuk($db) : new TransaksiKeluar($db);

            $transaksi->id_produk = $_POST['id_produk'] ?? 0;
            $transaksi->jumlah = $_POST['jumlah'] ?? 0;
            $transaksi->tanggal = $_POST['tanggal'] ?? date('Y-m-d H:i:s');
            $transaksi->keterangan = $_POST['keterangan'] ?? '';
            $transaksi->jenis_transaksi = $jenis;

            // Validasi stok hanya untuk transaksi keluar
            if ($jenis === 'keluar' && !$transaksi->validateStock()) {
                $_SESSION['error'] = "Stok tidak mencukupi untuk transaksi keluar!";
            } else {
                if ($transaksi->save()) {
                    $_SESSION['success'] = "Transaksi berhasil disimpan";
                    header("Location: TransaksiController.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Gagal menyimpan transaksi";
                }
            }
        }

        // Ambil daftar produk
        $stmt_produk = $produk->readAll();
        $produkList = $stmt_produk->fetchAll(PDO::FETCH_ASSOC);

        include '../views/transaksi/create.php';
        break;

    // ======================
    // Hapus transaksi
    // ======================
    case 'delete':
        if (isset($_GET['id'], $_GET['jenis'])) {
            $jenis = $_GET['jenis'];
            $transaksi = ($jenis === 'masuk') ? new TransaksiMasuk($db) : new TransaksiKeluar($db);
            $transaksi->id_transaksi = $_GET['id'];

            if ($transaksi->delete()) {
                $_SESSION['success'] = "Transaksi berhasil dihapus";
            } else {
                $_SESSION['error'] = "Gagal menghapus transaksi";
            }
        }
        header("Location: TransaksiController.php");
        exit();

    // ======================
    // Cetak laporan transaksi
    // ======================
    case 'cetak_laporan':
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-d');

        $transaksiMasuk = new TransaksiMasuk($db);
        $transaksiKeluar = new TransaksiKeluar($db);

        $dataMasuk = $transaksiMasuk->readLaporan($start_date, $end_date)->fetchAll(PDO::FETCH_ASSOC);
        $dataKeluar = $transaksiKeluar->readLaporan($start_date, $end_date)->fetchAll(PDO::FETCH_ASSOC);

        $data = array_merge($dataMasuk, $dataKeluar);

        // Urutkan berdasarkan tanggal ASC
        usort($data, function($a, $b) {
            return strtotime($a['tanggal']) - strtotime($b['tanggal']);
        });

        include '../views/transaksi/cetak_laporan.php';
        break;

    default:
        header("Location: TransaksiController.php");
        exit();
}
?>
