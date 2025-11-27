<?php
session_start();

// 1. Tampilkan Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Cek Login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// ==========================================
// 1. INPUT PASSWORD (CARA AMAN / NOWDOC)
// ==========================================
// Paste password kamu DI ANTARA baris 'MULAI_PASSWORD' dan 'AKHIR_PASSWORD'.
// Jangan ubah tulisan MULAI_PASSWORD/AKHIR_PASSWORD-nya.
// Jangan ada spasi di depan AKHIR_PASSWORD;

$password_supabase = <<<'AKHIR_PASSWORD'
siguda-passw
AKHIR_PASSWORD;

// ==========================================
// 2. KONEKSI DATABASE
// ==========================================
$db = null;
try {
    // Settingan Default (Supabase)
    $host = 'aws-0-ap-southeast-1.pooler.supabase.com';
    $port = '6543';
    $dbname = 'postgres';
    $user = 'postgres.hkxkszzflgtlrezvfyqv'; // Username
    $pass = trim($password_supabase); // Bersihkan spasi tidak sengaja

    // Cek jika Vercel Environment tersedia (Prioritas)
    if (getenv('DB_PASSWORD')) {
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT');
        $dbname = getenv('DB_DATABASE');
        $user = getenv('DB_USERNAME');
        $pass = getenv('DB_PASSWORD');
    }

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    // Menangkap Error Syntax maupun Koneksi
    die("<div style='background:darkred; color:white; padding:20px; font-family:sans-serif;'>
            <h1>FATAL ERROR</h1>
            <h3>" . $e->getMessage() . "</h3>
            <p>Line: " . $e->getLine() . "</p>
         </div>");
}

// ==========================================
// 3. LOGIKA DASHBOARD
// ==========================================
try {
    // Hitung-hitungan Sederhana
    $total_produk = $db->query("SELECT COUNT(*) FROM produk")->fetchColumn();
    $total_kategori = $db->query("SELECT COUNT(*) FROM kategori")->fetchColumn();
    $total_transaksi = $db->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();
    
    // Total Aset (Stok * Harga)
    $stmt = $db->query("SELECT SUM(stok * harga) FROM produk");
    $aset = $stmt->fetchColumn() ?: 0;

    // Data Tabel
    $stok_menipis = $db->query("SELECT * FROM produk WHERE stok < 10 LIMIT 5")->fetchAll();
    
    // Transaksi Terakhir (Join)
    $sql = "SELECT t.*, p.nama_produk FROM transaksi t 
            LEFT JOIN produk p ON t.id_produk = p.id_produk 
            ORDER BY t.tanggal DESC LIMIT 5";
    $transaksi_terakhir = $db->query($sql)->fetchAll();

} catch (Exception $e) {
    die("Error Query: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary mb-4 p-3">
    <div class="container">
        <span class="navbar-brand h1">SIGUDA</span>
        <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="alert alert-primary">
        Halo, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?></strong>
    </div>

    <div class="row mb-4 text-center">
        <div class="col-md-3">
            <div class="card p-3 shadow-sm border-primary">
                <h3><?php echo $total_produk; ?></h3>
                <small>Produk</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 shadow-sm border-success">
                <h3><?php echo $total_kategori; ?></h3>
                <small>Kategori</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 shadow-sm border-warning">
                <h3><?php echo $total_transaksi; ?></h3>
                <small>Transaksi</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 shadow-sm border-info">
                <h3>Rp <?php echo number_format($aset, 0, ',', '.'); ?></h3>
                <small>Aset</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white text-danger fw-bold">Stok Menipis</div>
                <ul class="list-group list-group-flush">
                    <?php foreach($stok_menipis as $item): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <?php echo htmlspecialchars($item['nama_produk']); ?>
                        <span class="badge bg-danger"><?php echo $item['stok']; ?></span>
                    </li>
                    <?php endforeach; ?>
                    <?php if(empty($stok_menipis)) echo "<li class='list-group-item text-center text-muted'>Aman</li>"; ?>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white text-primary fw-bold">Transaksi Terakhir</div>
                <ul class="list-group list-group-flush">
                    <?php foreach($transaksi_terakhir as $t): ?>
                    <li class="list-group-item">
                        <?php echo htmlspecialchars($t['nama_produk']); ?>
                        <br>
                        <small class="text-muted">
                            <?php echo $t['jenis_transaksi'] == 'masuk' ? '🟢 Masuk' : '🔴 Keluar'; ?> 
                            - <?php echo $t['tanggal']; ?>
                        </small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

</body>
</html>