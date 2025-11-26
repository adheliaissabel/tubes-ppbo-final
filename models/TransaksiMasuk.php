<?php
require_once 'Transaksi.php';

class TransaksiMasuk extends Transaksi {

    public function validateStock() {
        // Selalu true karena transaksi masuk menambah stok
        return true;
    }

    public function save() {
        $this->jenis_transaksi = 'masuk';
        $this->tanggal = date('Y-m-d H:i:s');
        return $this->insertToDatabase();
    }
}
?>