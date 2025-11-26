<?php
// 2. MVC/Modular
session_start();
require_once '../config/database.php';
require_once '../models/Produk.php';
require_once '../models/Kategori.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$produk = new Produk($db);
$kategori = new Kategori($db);

$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch($action) {
    case 'index':
        $stmt = $produk->readAll();
        include '../views/produk/index.php';
        break;
        
    case 'create':
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // ... (kode simpan data seperti sebelumnya) ...
            $produk->id_kategori = $_POST['kategori_id'];
            $produk->kode_produk = $_POST['kode_produk'];
            $produk->nama_produk = $_POST['nama_produk'];
            $produk->ukuran = $_POST['ukuran'];
            $produk->warna = $_POST['warna'];
            $produk->stok = $_POST['stok'];
            $produk->harga_beli = $_POST['harga_beli'];
            $produk->harga_jual = $_POST['harga_jual'];
            $produk->deskripsi = $_POST['deskripsi'];
            
            if($produk->create()) {
                $_SESSION['success'] = "Produk berhasil ditambahkan";
                header("Location: ProdukController.php");
                exit();
            } else {
                $_SESSION['error'] = "Gagal menambah produk (Kode Produk mungkin duplikat)";
            }
        }
        
        // mengambil data kategori agar dropdown tidak error
        // Kita gunakan nama variabel $stmt_kategori
        $stmt_kategori = $kategori->readAll(); 
        
        include '../views/produk/create.php';
        break;
        
    case 'edit':
        if(isset($_GET['id'])) {
            // PERBAIKAN: Gunakan id_produk
            $produk->id_produk = $_GET['id'];
            $produk->readOne();
            
            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $produk->id_kategori = $_POST['kategori_id'];
                $produk->kode_produk = $_POST['kode_produk'];
                $produk->nama_produk = $_POST['nama_produk'];
                $produk->ukuran = $_POST['ukuran'];
                $produk->warna = $_POST['warna'];
                $produk->stok = $_POST['stok'];
                $produk->harga_beli = $_POST['harga_beli'];
                $produk->harga_jual = $_POST['harga_jual'];
                $produk->deskripsi = $_POST['deskripsi'];
                
                if($produk->update()) {
                    $_SESSION['success'] = "Produk berhasil diupdate";
                    header("Location: ProdukController.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Gagal mengupdate produk";
                }
            }
        }
        $stmt_kategori = $kategori->readAll();
        include '../views/produk/edit.php';
        break;
        
    case 'delete':
        if(isset($_GET['id'])) {
            // PERBAIKAN: Gunakan id_produk
            $produk->id_produk = $_GET['id'];
            
            if($produk->delete()) {
                $_SESSION['success'] = "Produk berhasil dihapus";
            } else {
                $_SESSION['error'] = "Gagal menghapus produk";
            }
        }
        header("Location: ProdukController.php");
        exit();
        
    case 'cetak':
        $stmt = $produk->readAll();
        include '../views/produk/cetak.php';
        break;
        
    default:
        $stmt = $produk->readAll();
        include '../views/produk/index.php';
        break;
}
?>