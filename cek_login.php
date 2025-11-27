<?php
// 1. PAKSA ERROR MUNCUL DI LAYAR (Penting untuk Vercel)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Debugging Level MAX</h1>";

// 2. CEK APAKAH VARIABEL VERCEL TERBACA?
$host = getenv('DB_HOST');
$user = getenv('DB_USERNAME');
$pass = getenv('DB_PASSWORD');
$port = getenv('DB_PORT');
$db_name = getenv('DB_DATABASE');

echo "<h3>1. Cek Environment Variable</h3>";
echo "DB_HOST: " . ($host ? "✅ Ada ($host)" : "❌ KOSONG (Cek Settings Vercel)") . "<br>";
echo "DB_USER: " . ($user ? "✅ Ada ($user)" : "❌ KOSONG") . "<br>";
echo "DB_PASS: " . ($pass ? "✅ Ada (Disembunyikan)" : "❌ KOSONG") . "<br>";
echo "DB_PORT: " . ($port ? "✅ Ada ($port)" : "❌ KOSONG (Default ke 5432)") . "<br>";

if (!$host || !$user || !$pass) {
    die("<br><h2 style='color:red'>STOP: Variabel Environment Belum Masuk!</h2>Silakan ke Vercel > Settings > Environment Variables, lalu Redeploy.");
}

// 3. TES KONEKSI DATABASE MANUAL (Tanpa file config/database.php)
echo "<hr><h3>2. Tes Koneksi Database</h3>";
try {
    // Kita coba koneksi langsung di sini untuk memastikan tidak ada masalah di class Database.php
    $dsn = "pgsql:host=$host;port=" . ($port ? $port : '5432') . ";dbname=$db_name";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h3 style='color:green'>✅ KONEKSI SUKSES!</h3>";
} catch (PDOException $e) {
    echo "<h3 style='color:red'>❌ KONEKSI GAGAL</h3>";
    echo "Pesan Error: " . $e->getMessage() . "<br>";
    die(); // Matikan proses jika koneksi gagal
}

// 4. CEK USER LOGIN
echo "<hr><h3>3. Cek User 'dosen'</h3>";
$username_input = 'dosen';
$password_input = 'admin123';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u");
$stmt->bindParam(':u', $username_input);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<h3 style='color:red'>❌ User '$username_input' TIDAK DITEMUKAN di database.</h3>";
    echo "Cek isi tabel users di HeidiSQL.";
} else {
    echo "✅ User ditemukan!<br>";
    echo "Hash di DB: " . $user['password'] . "<br>";
    echo "Panjang Hash: " . strlen($user['password']) . " karakter.<br><br>";

    if (password_verify($password_input, $user['password'])) {
        echo "<h1 style='color:green'>🎉 PASSWORD COCOK!</h1>";
    } else {
        echo "<h1 style='color:red'>💀 PASSWORD TIDAK COCOK</h1>";
        echo "Hash di database bukan hasil enkripsi dari 'admin123'.<br>";
        
        $new_hash = password_hash($password_input, PASSWORD_DEFAULT);
        echo "<br><b>Solusi:</b> Jalankan query ini di HeidiSQL:<br>";
        echo "<textarea rows='3' cols='80'>UPDATE users SET password = '$new_hash' WHERE username = '$username_input';</textarea>";
    }
}
?>