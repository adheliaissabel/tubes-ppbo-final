<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../config/database.php';
require_once '../models/Produk.php';
require_once '../models/Kategori.php';
require_once '../models/TransaksiMasuk.php';
require_once '../models/TransaksiKeluar.php';

$database = new Database();
$db = $database->getConnection();

// Instansiasi class
$produk = new Produk($db);
$kategori = new Kategori($db);
$transaksiMasuk = new TransaksiMasuk($db);
$transaksiKeluar = new TransaksiKeluar($db);

// Hitung total data
$total_produk = $produk->readAll()->rowCount();
$total_kategori = $kategori->readAll()->rowCount();

// Ambil semua transaksi
$data_masuk = $transaksiMasuk->readAll()->fetchAll(PDO::FETCH_ASSOC);
$data_keluar = $transaksiKeluar->readAll()->fetchAll(PDO::FETCH_ASSOC);

// Gabungkan semua transaksi untuk dashboard
$all_transaksi = array_merge($data_masuk, $data_keluar);

// Urutkan berdasarkan tanggal DESC
usort($all_transaksi, function($a, $b) {
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});

$total_transaksi = count($all_transaksi);

// Hitung total nilai stok
$stmt_nilai = $produk->readAll();
$total_nilai_stok = 0;
while($row = $stmt_nilai->fetch(PDO::FETCH_ASSOC)) {
    $harga_hitung = isset($row['harga_beli']) && $row['harga_beli'] > 0 ? $row['harga_beli'] : ($row['harga'] ?? 0);
    $total_nilai_stok += ($row['stok'] * $harga_hitung);
}

// Ambil stok menipis (<10)
$stmt_low = $produk->getLowStock(10);
$low_stock_data = $stmt_low ? $stmt_low->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIGUDA PPBO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<?php include 'layouts/navbar.php'; ?>

<div class="container">
    <!-- Welcome Card -->
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

    <!-- Summary Cards -->
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
                <div class="card-footer bg-primary border-0">
                    <a href="../controllers/ProdukController.php" class="text-white text-decoration-none small">Lihat Detail <i class="bi bi-arrow-right"></i></a>
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
                <div class="card-footer bg-success border-0">
                    <a href="../controllers/KategoriController.php" class="text-white text-decoration-none small">Lihat Detail <i class="bi bi-arrow-right"></i></a>
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
                <div class="card-footer bg-warning border-0">
                    <a href="../controllers/TransaksiController.php" class="text-white text-decoration-none small">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3 h-100 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Nilai Aset Stok</h6>
                        <h4 class="mt-2 mb-0">Rp <?php echo number_format($total_nilai_stok, 0, ',', '.'); ?></h4>
                    </div>
                    <i class="bi bi-cash-coin display-4 opacity-50"></i>
                </div>
                <div class="card-footer bg-info border-0">
                    <small class="text-white">Estimasi nilai modal</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Stok Menipis & 5 Transaksi Terakhir -->
    <div class="row">
        <!-- Stok Menipis -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle"></i> Stok Menipis (&lt; 10)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Produk</th>
                                    <th class="text-center">Sisa Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($low_stock_data) > 0): ?>
                                    <?php foreach($low_stock_data as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['kode_produk'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                        <td class="text-center"><span class="badge bg-danger rounded-pill"><?php echo $row['stok']; ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada stok menipis</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5 Transaksi Terakhir -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="bi bi-clock-history"></i> 5 Transaksi Terakhir</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Jenis</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $count = 0;
                                if(count($all_transaksi) > 0):
                                    foreach($all_transaksi as $row):
                                        if($count >= 5) break;
                                        $count++;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['kode_produk'] ?? $row['id_transaksi']); ?></td>
                                    <td>
                                        <?php if($row['jenis_transaksi'] == 'masuk'): ?>
                                            <span class="badge bg-success"><i class="bi bi-arrow-down"></i> Masuk</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-arrow-up"></i> Keluar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                </tr>
                                <?php 
                                    endforeach; 
                                else:
                                ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">Belum ada transaksi</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
