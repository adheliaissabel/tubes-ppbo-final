<?php
session_start();

// 1. Tampilkan Error (Wajib)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Cek Login
if(!isset($_SESSION['user_id'])) {
    // Jika belum login, lempar ke halaman depan
    echo "<script>window.location.href='../index.php';</script>";
    exit();
}

// ==========================================
// BAGIAN 1: DATABASE CLASS (LANGSUNG DISINI)
// ==========================================
// Kita taruh class Database di sini supaya tidak perlu 'require_once' yang bikin error
class Database {
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        // Ambil data environment variable
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT') ? getenv('DB_PORT') : '5432';
        $db_name = getenv('DB_DATABASE');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');

        // Jika env kosong (Localhost), pakai nilai default (Opsional)
        if (!$host) $host = 'aws-0-ap-southeast-1.pooler.supabase.com';
        if (!$db_name) $db_name = 'postgres';

        try {
            $dsn = "pgsql:host=" . $host . ";port=" . $port . ";dbname=" . $db_name;
            $this->conn = new PDO($dsn, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            die("<div style='background:red; color:white; padding:20px;'>
                <h1>Koneksi Database Gagal</h1>
                <p>Error: " . $exception->getMessage() . "</p>
                </div>");
        }
        return $this->conn;
    }
}

// ==========================================
// BAGIAN 2: LOGIKA DASHBOARD
// ==========================================

try {
    // Koneksi
    $database = new Database();
    $db = $database->getConnection();

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
    $total_transaksi = count($all_transaksi); // Ini hitung yg diambil aja (sample)

    // Hitung total row transaksi asli (untuk kartu statistik)
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