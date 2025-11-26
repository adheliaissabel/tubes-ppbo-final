<?php
// 2. MVC/Modular
session_start();
require_once '../config/database.php';
require_once '../models/Kategori.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$kategori = new Kategori($db);

$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch($action) {
    case 'index':
        // Mengambil data untuk ditampilkan di view
        $stmt = $kategori->readAll();
        include '../views/kategori/index.php';
        break;
        
    case 'create':
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $kategori->nama_kategori = $_POST['nama_kategori'];
            // Deskripsi dihapus karena tidak ada di database.sql
            
            if($kategori->create()) {
                $_SESSION['success'] = "Kategori berhasil ditambahkan";
                header("Location: KategoriController.php");
                exit();
            } else {
                $_SESSION['error'] = "Gagal menambahkan kategori";
            }
        }
        include '../views/kategori/create.php';
        break;
        
    case 'edit':
        if(isset($_GET['id'])) {
            // id_kategori sesuai Model
            $kategori->id_kategori = $_GET['id'];
            $kategori->readOne();
            
            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $kategori->nama_kategori = $_POST['nama_kategori'];
                
                if($kategori->update()) {
                    $_SESSION['success'] = "Kategori berhasil diupdate";
                    header("Location: KategoriController.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Gagal mengupdate kategori";
                }
            }
        }
        include '../views/kategori/edit.php';
        break;
        
    case 'delete':
        if(isset($_GET['id'])) {
            // Gunakan id_kategori
            $kategori->id_kategori = $_GET['id'];
            
            // memeriksa apakah kategori memiliki produk
            if($kategori->countProduk() > 0) {
                $_SESSION['error'] = "Tidak dapat menghapus kategori yang memiliki produk";
            } else {
                if($kategori->delete()) {
                    $_SESSION['success'] = "Kategori berhasil dihapus";
                } else {
                    $_SESSION['error'] = "Gagal menghapus kategori";
                }
            }
        }
        header("Location: KategoriController.php");
        exit();

    default:
        $stmt = $kategori->readAll();
        include '../views/kategori/index.php';
        break;
}
?>