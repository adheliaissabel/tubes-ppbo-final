<?php
session_start();

// 1. Error Reporting (Supaya kalau ada error ketahuan)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Cek Login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// 3. SMART LOADER DATABASE (Mencari file database.php otomatis)
$db_path = '';
$possibilities = [
    __DIR__ . '/../config/database.php', // Prioritas 1: Kecil semua
    __DIR__ . '/../Config/database.php', // Prioritas 2: Folder Besar
    __DIR__ . '/../config/Database.php', // Prioritas 3: File Besar
];

foreach ($possibilities as $path) {
    if (file_exists($path)) {
        $db_path = $path;
        break;
    }
}

if ($db_path) {
    require_once $db_path;
} else {
    die("<div style='background:red; color:white; padding:20px;'>FATAL ERROR: File config/database.php tidak ditemukan. <br>Posisi pencarian: " . __DIR__ . "</div>");
}

// 4. Koneksi Database
try {
    $database = new Database();
    $db = $database->getConnection();

    // --- LOGIKA QUERY MANUAL (Tanpa Model biar Gak Error) ---
    
    // Hitung Total Produk
    $stmt = $db->query("SELECT COUNT(*) as total FROM produk");
    $total_produk = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Hitung Total Kategori
    $stmt = $db->query("SELECT COUNT(*) as total FROM kategori");
    $total_kategori = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Ambil Semua Transaksi (Join dengan Produk)
    $query_transaksi = "SELECT t.*, p.nama_produk, p.kode_produk 
                        FROM transaksi t
                        LEFT JOIN produk p ON t.id_produk = p.id_produk
                        ORDER BY t.tanggal DESC";
    $stmt = $db->query($query_transaksi);
    $all_transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_transaksi = count($all_transaksi);

    // Hitung Nilai Aset
    // Menggunakan logika: Jika ada harga_beli pakai itu, jika tidak pakai harga jual
    $query_aset = "SELECT SUM(stok * CASE WHEN harga_beli > 0 THEN harga_beli ELSE harga END) as total_aset FROM produk";
    $stmt = $db->query($query_aset);
    $result_aset = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_nilai_stok = $result_aset['total_aset'] ?? 0;

    // Ambil Stok Menipis (< 10)
    $query_low = "SELECT * FROM produk WHERE stok < 10 ORDER BY stok ASC LIMIT 5";
    $stmt = $db->query($query_low);
    $low_stock_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error Database Query: " . $e->getMessage());
}
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

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"><i class="bi bi-box-seam"></i> SIGUDA</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="#">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="../logout.php">Logout <i class="bi bi-box-arrow-right"></i></a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h4 class="card-title">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User'); ?>!</h4>
                    <p class="text-muted mb-0">Anda login sebagai <strong><?php echo ucfirst($_SESSION['role'] ?? 'Staff'); ?></strong></p>
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

    <div class="row">
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
                                    <th class="text-center">Sisa</th>
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
                                    <tr><td colspan="3" class="text-center text-muted py-3">Aman! Stok cukup.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="bi bi-clock-history"></i> Transaksi Terakhir</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th>Jenis</th>
                                    <th>Tgl</th>
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
                                    <td><?php echo htmlspecialchars($row['nama_produk'] ?? 'ID:'.$row['id_produk']); ?></td>
                                    <td>
                                        <?php if($row['jenis_transaksi'] == 'masuk'): ?>
                                            <span class="badge bg-success">Masuk</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Keluar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/y', strtotime($row['tanggal'])); ?></td>
                                </tr>
                                <?php endforeach; else: ?>
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