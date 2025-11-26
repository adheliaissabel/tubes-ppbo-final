<?php
session_start();
require_once '../config/database.php';
require_once '../models/Produk.php';
require_once '../models/TransaksiMasuk.php';
require_once '../models/TransaksiKeluar.php';
require_once '../models/Kategori.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$db = (new Database())->getConnection();
$produk = new Produk($db);
$kategori = new Kategori($db);
$masuk = new TransaksiMasuk($db);
$keluar = new TransaksiKeluar($db);

// ambil data seperti di dashboard.php
// ...
// lalu
include '../views/dashboard.php';