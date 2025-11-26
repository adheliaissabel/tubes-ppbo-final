<?php
class Produk {
    private $conn;
    private $table = "produk";
    public $id_produk;
    public $id_kategori;
    public $kode_produk;
    public $nama_produk;
    public $ukuran;
    public $warna;
    public $stok;
    public $harga_beli;
    public $harga_jual;
    public $deskripsi;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO {$this->table} 
                SET id_kategori=:id_kategori, kode_produk=:kode_produk, 
                    nama_produk=:nama_produk, ukuran=:ukuran, warna=:warna,
                    stok=:stok, harga_beli=:harga_beli, harga_jual=:harga_jual,
                    deskripsi=:deskripsi";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_kategori', $this->id_kategori);
        $stmt->bindParam(':kode_produk', $this->kode_produk);
        $stmt->bindParam(':nama_produk', $this->nama_produk);
        $stmt->bindParam(':ukuran', $this->ukuran);
        $stmt->bindParam(':warna', $this->warna);
        $stmt->bindParam(':stok', $this->stok);
        $stmt->bindParam(':harga_beli', $this->harga_beli);
        $stmt->bindParam(':harga_jual', $this->harga_jual);
        $stmt->bindParam(':deskripsi', $this->deskripsi);
        return $stmt->execute();
    }

    public function readAll() {
        $query = "SELECT p.*, k.nama_kategori 
                FROM {$this->table} p
                LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
                ORDER BY p.id_produk DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM {$this->table} WHERE id_produk=:id_produk LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_produk', $this->id_produk);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            foreach ($row as $key => $val) {
                if(property_exists($this, $key)) $this->$key = $val;
            }
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE {$this->table} SET 
                id_kategori=:id_kategori, kode_produk=:kode_produk,
                nama_produk=:nama_produk, ukuran=:ukuran, warna=:warna,
                stok=:stok, harga_beli=:harga_beli, harga_jual=:harga_jual,
                deskripsi=:deskripsi
                WHERE id_produk=:id_produk";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_kategori', $this->id_kategori);
        $stmt->bindParam(':kode_produk', $this->kode_produk);
        $stmt->bindParam(':nama_produk', $this->nama_produk);
        $stmt->bindParam(':ukuran', $this->ukuran);
        $stmt->bindParam(':warna', $this->warna);
        $stmt->bindParam(':stok', $this->stok);
        $stmt->bindParam(':harga_beli', $this->harga_beli);
        $stmt->bindParam(':harga_jual', $this->harga_jual);
        $stmt->bindParam(':deskripsi', $this->deskripsi);
        $stmt->bindParam(':id_produk', $this->id_produk);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM {$this->table} WHERE id_produk=:id_produk";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_produk', $this->id_produk);
        return $stmt->execute();
    }

    public function getLowStock($limit = 10) {
        $query = "SELECT p.*, k.nama_kategori 
                FROM {$this->table} p
                LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
                WHERE p.stok <= :limit
                ORDER BY p.stok ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit);
        $stmt->execute();
        return $stmt;
    }
}
?>
