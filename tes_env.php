<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tes Lingkungan Vercel</title>
    <style>body { font-family: sans-serif; padding: 20px; }</style>
</head>
<body>
    <h1>🔍 Tes Isolasi Server</h1>
    <p>Jika kamu melihat tulisan ini, berarti PHP BERJALAN.</p>
    <hr>

    <?php
    // Tampilkan semua error
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    echo "<h3>1. Cek Environment Variables</h3>";
    
    // Ambil variabel
    $host = getenv('DB_HOST');
    $user = getenv('DB_USERNAME');
    $pass = getenv('DB_PASSWORD');
    $port = getenv('DB_PORT');
    
    // Cek keberadaan variabel
    if ($host) {
        echo "<p style='color:green'>✅ <b>DB_HOST:</b> Terbaca ($host)</p>";
    } else {
        echo "<p style='color:red'>❌ <b>DB_HOST:</b> TIDAK TERBACA! (Cek Settings Vercel)</p>";
    }

    if ($pass) {
        echo "<p style='color:green'>✅ <b>DB_PASSWORD:</b> Terbaca (Disembunyikan)</p>";
    } else {
        echo "<p style='color:red'>❌ <b>DB_PASSWORD:</b> TIDAK TERBACA!</p>";
    }

    echo "<hr><h3>2. Tes Koneksi Langsung (Tanpa Class)</h3>";

    if ($host && $user && $pass) {
        try {
            // Gunakan port 6543 (Pooler) atau 5432 (Direct)
            $p = $port ? $port : '5432';
            $dsn = "pgsql:host=$host;port=$p;dbname=postgres";
            
            echo "Mencoba connect ke: <i>$dsn</i> ...<br><br>";
            
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "<h2 style='color:green'>🎉 KONEKSI DATABASE SUKSES!</h2>";
            echo "Masalahmu bukan di koneksi, tapi di file kodingan lain (mungkin config/database.php).";
            
        } catch (PDOException $e) {
            echo "<h2 style='color:red'>💀 KONEKSI GAGAL</h2>";
            echo "<strong>Error:</strong> " . $e->getMessage();
        }
    } else {
        echo "Tidak bisa tes koneksi karena variabel environment belum lengkap.";
    }
    ?>
</body>
</html>