<?php
require_once 'Transaksi.php';
require_once 'Produk.php';

/**
 * Class TransaksiKeluar (Child Class)
 * 
 * Mengelola transaksi barang keluar ke customer
 * Mewarisi class Transaksi dengan validasi stok ketat
 * 
 * @author Tim SIGUDA
 * @version 1.0
 */
class TransaksiKeluar extends Transaksi {
    
    /**
     * POLYMORPHISM: Override method validateStock()
     * Untuk transaksi keluar, HARUS cek stok tersedia
     * 
     * @return bool True jika stok cukup, False jika tidak cukup
     */
    public function validateStock() {
        $produk = new Produk($this->conn);
        $produk->id_produk = $this->id_produk;
        $produk->readOne();
        
        // Cek apakah stok mencukupi
        return ($produk->stok >= $this->jumlah);
    }
    
    /**
     * POLYMORPHISM: Override method save()
     * Menyimpan transaksi keluar dan mengurangi stok produk
     * Dilengkapi validasi stok sebelum eksekusi
     * 
     * @return bool True jika berhasil, False jika gagal
     */
    public function save() {
        // Set jenis transaksi otomatis
        $this->jenis_transaksi = 'keluar';
        
        // Validasi jumlah harus positif
        if($this->jumlah <= 0) {
            return false;
        }
        
        // PENTING: Validasi stok dulu sebelum simpan
        if(!$this->validateStock()) {
            return false; // Stok tidak cukup, transaksi gagal
        }
        
        // Simpan transaksi ke database
        if($this->insertToDatabase()) {
            // Update stok produk (KURANG)
            $produk = new Produk($this->conn);
            $produk->id_produk = $this->id_produk;
            return $produk->updateStok($this->jumlah, 'kurang');
        }
        
        return false;
    }
}
?>