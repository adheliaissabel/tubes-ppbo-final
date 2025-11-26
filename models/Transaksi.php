<?php
/**
 * Abstract Class Transaksi (Parent Class)
 * 
 * Base class untuk semua jenis transaksi
 * Berisi method umum yang dipakai transaksi masuk & keluar
 * 
 * @author Tim SIGUDA
 * @version 2.0
 */
abstract class Transaksi {
    protected $conn;
    protected $table = "transaksi";

    public $id_transaksi;
    public $id_produk;
    public $jenis_transaksi;
    public $jumlah;
    public $tanggal;
    public $keterangan;

    /**
     * Constructor
     * @param PDO $db Koneksi database
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Method abstract yang harus diimplementasi oleh child class
     * Digunakan untuk validasi stok sebelum transaksi
     * 
     * @return bool True jika valid, False jika tidak valid
     */
    abstract public function validateStock();

    /**
     * Method abstract untuk menyimpan transaksi
     * Setiap child class punya logic berbeda
     * 
     * @return bool True jika berhasil, False jika gagal
     */
    abstract public function save();

    /**
     * Mengambil semua data transaksi dengan join ke produk
     * 
     * @return PDOStatement Result set transaksi
     */
    public function readAll() {
        $query = "SELECT t.*, p.nama_produk, p.ukuran, p.kode_produk
                  FROM " . $this->table . " t
                  JOIN produk p ON t.id_produk = p.id_produk
                  ORDER BY t.tanggal DESC, t.id_transaksi DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Membaca laporan transaksi berdasarkan range tanggal
     * 
     * @param string $start_date Tanggal mulai
     * @param string $end_date Tanggal akhir
     * @return PDOStatement Result set laporan
     */
    public function readLaporan($start_date, $end_date) {
        $query = "SELECT t.*, p.nama_produk, p.ukuran, k.nama_kategori
                  FROM " . $this->table . " t
                  JOIN produk p ON t.id_produk = p.id_produk
                  JOIN kategori k ON p.id_kategori = k.id_kategori
                  WHERE t.tanggal BETWEEN :start AND :end
                  ORDER BY t.tanggal ASC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start_date);
        $stmt->bindParam(':end', $end_date);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Menghapus data transaksi
     * 
     * @return bool True jika berhasil, False jika gagal
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id_transaksi = :id_transaksi";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_transaksi', $this->id_transaksi);
        return $stmt->execute();
    }

    /**
     * Method protected untuk INSERT transaksi ke database
     * Digunakan oleh child class lewat parent::insertToDatabase()
     * 
     * @return bool True jika berhasil, False jika gagal
     */
    protected function insertToDatabase() {
        $query = "INSERT INTO " . $this->table . " 
                  SET id_produk=:id_produk, jenis_transaksi=:jenis_transaksi, 
                      jumlah=:jumlah, tanggal=:tanggal, keterangan=:keterangan";
        
        $stmt = $this->conn->prepare($query);

        // Sanitasi input
        $this->id_produk = htmlspecialchars(strip_tags($this->id_produk));
        $this->jenis_transaksi = htmlspecialchars(strip_tags($this->jenis_transaksi));
        $this->jumlah = htmlspecialchars(strip_tags($this->jumlah));
        $this->keterangan = htmlspecialchars(strip_tags($this->keterangan));

        $stmt->bindParam(':id_produk', $this->id_produk);
        $stmt->bindParam(':jenis_transaksi', $this->jenis_transaksi);
        $stmt->bindParam(':jumlah', $this->jumlah);
        $stmt->bindParam(':tanggal', $this->tanggal);
        $stmt->bindParam(':keterangan', $this->keterangan);

        return $stmt->execute();
    }
}
?>