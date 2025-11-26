<?php
require_once 'Transaksi.php';
require_once 'Produk.php';

/**
 * Class TransaksiMasuk (Child Class)
 * 
 * Mengelola transaksi barang masuk dari supplier
 * Mewarisi class Transaksi dan override method save()
 * 
 * @author Tim SIGUDA
 * @version 1.0
 */
class TransaksiMasuk extends Transaksi {
    
    /**
     * POLYMORPHISM: Override method validateStock()
     * Untuk transaksi masuk, tidak perlu cek stok (selalu valid)
     * 
     * @return bool Selalu true karena barang masuk tidak perlu validasi stok
     */
    public function validateStock() {
        // Barang masuk tidak perlu cek stok, selalu boleh
        return true;
    }
    
    /**
     * POLYMORPHISM: Override method save()
     * Menyimpan transaksi masuk dan menambah stok produk
     * 
     * @return bool True jika berhasil, False jika gagal
     */
    public function save() {
        // Set jenis transaksi otomatis
        $this->jenis_transaksi = 'masuk';
        
        // Validasi jumlah harus positif
        if($this->jumlah <= 0) {
            return false;
        }
        
        // Simpan transaksi ke database
        if($this->insertToDatabase()) {
            // Update stok produk (TAMBAH)
            $produk = new Produk($this->conn);
            $produk->id_produk = $this->id_produk;
            return $produk->updateStok($this->jumlah, 'tambah');
        }
        
        return false;
    }
}
?>