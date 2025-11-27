<?php
session_start();

// 1. PAKSA ERROR MUNCUL (Supaya tidak layar putih)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Cek Login
if(!isset($_SESSION['user_id'])) {
    // Jika belum login, tendang ke depan
    header("Location: ../index.php");
    exit();
}

// =========================================================
// BAGIAN 1: KONEKSI DATABASE (LANGSUNG DI SINI)
// =========================================================
// Kita tidak pakai 'require_once' supaya tidak error 'File Not Found'

try {
    // A. Coba ambil data dari Settingan Vercel
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT');
    $db_name = getenv('DB_DATABASE');
    $username = getenv('DB_USERNAME');
    $password = getenv('DB_PASSWORD');

    // B. JIKA KOSONG (CADANGAN/FALLBACK)
    // Masukkan data Supabase kamu di sini sebagai jaga-jaga
    if (!$host) {
        $host = 'aws-0-ap-southeast-1.pooler.supabase.com';
        $port = '6543'; 
        $db_name = 'postgres';
        $username = 'postgres.hkxkszzflgtlrezvfyqv'; // Username panjang kamu
        $password = 'siguda-passw'; // <--- GANTI INI DENGAN PASSWORD ASLI!
    }

    // C. Proses Koneksi
    $dsn = "pgsql:host=" . $host . ";port=" . ($port ? $port : '5432') . ";dbname=" . $db_name;
    
    $db = new PDO($dsn, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("<div style='padding: 20px; background: red; color: white;'>
            <h1>GAGAL KONEKSI DATABASE</h1>
            <p>" . $e->getMessage() . "</p>
            <p>Pastikan Environment Variable di Vercel sudah diisi atau data cadangan di kodingan benar.</p>
         </div>");
}

// =========================================================
// BAGIAN 2: LOGIKA DASHBOARD
// =========================================================

try {
    // 1. Hitung Total Produk
    $stmt = $db->query("SELECT COUNT(*) as total FROM produk");
    $total_produk = $stmt->fetch()['total'];

    // 2. Hitung Total Kategori
    $stmt = $db->query("SELECT COUNT(*) as total FROM kategori");
    $total_kategori = $stmt->fetch()['total'];

    // 3. Ambil Transaksi (Gabung Tabel Produk)
    $query_transaksi = "SELECT t.*, p.nama_produk, p.kode_produk 
                        FROM transaksi t
                        LEFT JOIN produk p ON t.id_produk = p.id_produk
                        ORDER BY t.tanggal DESC LIMIT 50";
    $stmt = $db->query($query_transaksi);
    $all_transaksi = $stmt->fetchAll();
    
    // Hitung total row transaksi asli
    $stmt_count_trans = $db->query("SELECT COUNT(*) as total FROM transaksi");
    $total_transaksi_real = $stmt_count_trans->fetch()['total'];

    // 4. Hitung Nilai Aset
    $query_aset = "SELECT SUM(stok * harga) as total_aset FROM produk";
    $stmt = $db->query($query_aset);
    $aset = $stmt->fetch();
    $total_nilai_stok = $aset['total_aset'] ?? 0;

    // 5. Stok Menipis
    $query_low = "SELECT * FROM produk WHERE stok < 10 ORDER BY stok ASC LIMIT 5";
    $stmt = $db->query($query_low);
    $low_stock_data = $stmt->fetchAll();

} catch (Exception $e) {
    die("Error Query Dashboard: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIGUDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"><i class="bi bi-box-seam"></i> SIGUDA</a>
    <div class="ms-auto">
        <a class="btn btn-danger btn-sm" href="../logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h4>Halo, <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User'); ?>!</h4>
                    <p class="text-muted mb-0">Role: <strong><?php echo ucfirst($_SESSION['role'] ?? 'Staff'); ?></strong></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100 shadow-sm">
                <div class="card-body">
                    <h6>Total Produk</h6>
                    <h2><?php echo $total_produk; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100 shadow-sm">
                <div class="card-body">
                    <h6>Total Kategori</h6>
                    <h2><?php echo $total_kategori; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100 shadow-sm">
                <div class="card-body">
                    <h6>Total Transaksi</h6>
                    <h2><?php echo $total_transaksi_real; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100 shadow-sm">
                <div class="card-body">
                    <h6>Nilai Aset</h6>
                    <h4>Rp <?php echo number_format($total_nilai_stok, 0, ',', '.'); ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="text-danger mb-0">Stok Menipis</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead><tr><th>Produk</th><th>Sisa</th></tr></thead>
                        <tbody>
                            <?php foreach($low_stock_data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td><span class="badge bg-danger"><?php echo $row['stok']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="text-primary mb-0">Transaksi Terakhir</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead><tr><th>Produk</th><th>Jenis</th><th>Tgl</th></tr></thead>
                        <tbody>
                            <?php 
                            $i=0; 
                            foreach($all_transaksi as $row): 
                                if($i++ >= 5) break; 
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
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>