<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$username_dicari = 'dosen';
$password_dicoba = 'admin123';

echo "<h2>Mode Debugging Login</h2>";

// 1. Cek Koneksi & Data Mentah
$stmt = $db->prepare("SELECT * FROM users WHERE username = :u");
$stmt->bindParam(':u', $username_dicari);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "❌ User '$username_dicari' TIDAK DITEMUKAN di database.<br>";
} else {
    echo "✅ User ditemukan!<br>";
    echo "Username DB: [" . $user['username'] . "]<br>";
    echo "Password Hash di DB: [" . $user['password'] . "]<br>";
    echo "Panjang Hash: " . strlen($user['password']) . " karakter (Harus 60)<br><br>";

    // 2. Tes Verifikasi Password
    echo "Mencoba mencocokkan dengan password: '$password_dicoba'...<br>";
    if (password_verify($password_dicoba, $user['password'])) {
        echo "🎉 <b>HASIL: COCOK!</b> Harusnya login berhasil.";
    } else {
        echo "💀 <b>HASIL: TIDAK COCOK.</b><br>";
        echo "Kemungkinan hash di database rusak/terpotong atau salah algoritma.";
        
        // Buat hash baru yang valid untuk dicopy
        echo "<br><br><b>Solusi:</b> Copy kode di bawah ini dan jalankan di HeidiSQL:<br>";
        $new_hash = password_hash($password_dicoba, PASSWORD_DEFAULT);
        echo "<textarea cols='100' rows='3'>UPDATE users SET password = '$new_hash' WHERE username = '$username_dicari';</textarea>";
    }
}
?>