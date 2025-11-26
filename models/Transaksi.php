<?php
abstract class Transaksi {
    protected $conn;
    protected $table = "transaksi";

    public $id_transaksi;
    public $id_produk;
    public $jenis_transaksi;
    public $jumlah;
    public $tanggal;
    public $keterangan;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Abstract methods
    abstract public function validateStock();
    abstract public function save();

    // Ambil semua transaksi
    public function readAll() {
        $query = "SELECT t.*, p.nama_produk, p.ukuran, p.kode_produk
                  FROM " . $this->table . " t
                  JOIN produk p ON t.id_produk = p.id_produk
                  ORDER BY t.tanggal DESC, t.id_transaksi DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Insert ke database
    protected function insertToDatabase() {
        $query = "INSERT INTO " . $this->table . " 
                  SET id_produk=:id_produk, jenis_transaksi=:jenis_transaksi, 
                      jumlah=:jumlah, tanggal=:tanggal, keterangan=:keterangan";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_produk', $this->id_produk);
        $stmt->bindParam(':jenis_transaksi', $this->jenis_transaksi);
        $stmt->bindParam(':jumlah', $this->jumlah);
        $stmt->bindParam(':tanggal', $this->tanggal);
        $stmt->bindParam(':keterangan', $this->keterangan);
        return $stmt->execute();
    }

    // Hapus transaksi
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id_transaksi=:id_transaksi";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_transaksi', $this->id_transaksi);
        return $stmt->execute();
    }

    // Untuk laporan
    public function readLaporan($start_date, $end_date) {
        $query = "SELECT t.*, p.nama_produk, p.ukuran, p.kode_produk
                  FROM " . $this->table . " t
                  JOIN produk p ON t.id_produk = p.id_produk
                  WHERE t.tanggal BETWEEN :start_date AND :end_date
                  ORDER BY t.tanggal ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        return $stmt;
    }
}
?>