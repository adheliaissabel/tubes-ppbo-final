<?php
require_once 'Transaksi.php';
require_once 'Produk.php';

class TransaksiKeluar extends Transaksi {

    public function validateStock() {
        $produk = new Produk($this->conn);
        $produk->id_produk = $this->id_produk;
        $produk->readOne();
        return ($produk->stok >= $this->jumlah);
    }

    public function save() {
        $this->jenis_transaksi = 'keluar';
        $this->tanggal = date('Y-m-d H:i:s');

        if(!$this->validateStock()) {
            return false;
        }

        // Insert transaksi keluar
        $result = $this->insertToDatabase();

        // Kurangi stok produk jika insert berhasil
        if($result) {
            $produk = new Produk($this->conn);
            $produk->id_produk = $this->id_produk;
            $produk->readOne();
            $produk->stok -= $this->jumlah;
            $produk->update();
        }

        return $result;
    }
}
?>