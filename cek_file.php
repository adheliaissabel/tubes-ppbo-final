<?php
echo "<h1>🕵️ MENGINTIP ISI FOLDER SERVER</h1>";

$root = __DIR__; // Folder utama
echo "<b>Posisi Root:</b> $root<br><hr>";

// 1. Cek isi folder Root (Mencari folder config)
echo "<h3>📂 Isi Folder Utama:</h3>";
$files = scandir($root);
echo "<pre>";
print_r($files);
echo "</pre>";

// 2. Cek isi folder config (Jika ada)
// Kita coba cari folder 'config' atau 'Config'
$config_path = $root . '/config';
if (!is_dir($config_path)) {
    $config_path = $root . '/Config'; // Coba huruf besar
}

if (is_dir($config_path)) {
    echo "<h3>📂 Isi Folder Config (" . basename($config_path) . "):</h3>";
    $files_config = scandir($config_path);
    echo "<pre>";
    print_r($files_config);
    echo "</pre>";
} else {
    echo "<h3 style='color:red'>❌ Folder 'config' TIDAK DITEMUKAN!</h3>";
    echo "Mungkin namanya salah ketik (misal: 'Config') atau belum ter-upload.";
}
?>