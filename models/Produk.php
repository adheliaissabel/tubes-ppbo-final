<?php
class Produk {
    private $conn;
    private $table = "produk";

    public $id_produk; // Ganti $id menjadi $id_produk
    public $id_kategori;
    public $kode_produk;
    public $nama_produk;
    public $ukuran;
    public $warna; // Kolom tidak ada di database.sql awal, sudah ditambahkan di perbaikan database.sql
    public $stok;
    public $harga_beli; // Kolom tidak ada di database.sql awal, sudah ditambahkan di perbaikan database.sql
    public $harga_jual; // Kolom tidak ada di database.sql awal, sudah ditambahkan di perbaikan database.sql
    public $deskripsi; // Kolom tidak ada di database.sql awal, sudah ditambahkan di perbaikan database.sql

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
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
        // menambahkan JOIN ke tabel kategori agar 'nama_kategori' terbaca
        $query = "SELECT p.*, k.nama_kategori 
                  FROM " . $this->table . " p
                  LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                  ORDER BY p.id_produk DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        // Query mengambil semua kolom dari produk dan nama kategori
        $query = "SELECT p.*, k.nama_kategori 
                  FROM " . $this->table . " p
                  LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                  WHERE p.id_produk = :id_produk LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_produk', $this->id_produk);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id_produk = $row['id_produk'];
            $this->id_kategori = $row['id_kategori'];
            
            // PERBAIKAN: Gunakan ?? (tanda tanya ganda) untuk mencegah error
            $this->kode_produk = $row['kode_produk'] ?? '';
            $this->nama_produk = $row['nama_produk'];
            $this->ukuran = $row['ukuran'];
            
            // Baris 74 yang error kita perbaiki di sini:
            $this->warna = $row['warna'] ?? '-'; 
            
            $this->stok = $row['stok'];
            $this->harga_beli = $row['harga_beli'] ?? 0;
            $this->harga_jual = $row['harga_jual'] ?? 0;
            
            // Baris 78 yang error kita perbaiki di sini:
            $this->deskripsi = $row['deskripsi'] ?? '';
            
            return true;
        }
        return false;
    }

    public function update() {
        // Query Update
        $query = "UPDATE " . $this->table . " 
                  SET id_kategori=:id_kategori, 
                      kode_produk=:kode_produk,
                      nama_produk=:nama_produk, 
                      ukuran=:ukuran, 
                      warna=:warna,
                      stok=:stok, 
                      harga_beli=:harga_beli, 
                      harga_jual=:harga_jual,
                      deskripsi=:deskripsi
                  WHERE id_produk=:id_produk";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitasi (Bersihkan input)
        $this->kode_produk = htmlspecialchars(strip_tags($this->kode_produk));
        $this->nama_produk = htmlspecialchars(strip_tags($this->nama_produk));
        $this->ukuran = htmlspecialchars(strip_tags($this->ukuran));
        
        // Pakai null coalescing operator (??) biar aman kalau kosong
        $this->warna = htmlspecialchars(strip_tags($this->warna ?? '-'));
        $this->deskripsi = htmlspecialchars(strip_tags($this->deskripsi ?? '-'));
        
        // Binding Parameter
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

        // Eksekusi
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        // Ganti 'id' menjadi 'id_produk'
        $query = "DELETE FROM " . $this->table . " WHERE id_produk = :id_produk";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_produk', $this->id_produk); // Ganti :id menjadi :id_produk
        return $stmt->execute();
    }

    public function updateStok($jumlah, $operasi = 'tambah') {
        // Ganti 'id' menjadi 'id_produk'
        if($operasi == 'tambah') {
            $query = "UPDATE " . $this->table . " SET stok = stok + :jumlah WHERE id_produk = :id_produk";
        } else {
            $query = "UPDATE " . $this->table . " SET stok = stok - :jumlah WHERE id_produk = :id_produk";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':jumlah', $jumlah);
        $stmt->bindParam(':id_produk', $this->id_produk); // Ganti :id menjadi :id_produk
        return $stmt->execute();
    }

    public function search($keyword) {
        // Ganti 'k.id' menjadi 'k.id_kategori' dan 'kategori_id' menjadi 'id_kategori'
        $query = "SELECT p.*, k.nama_kategori 
                  FROM " . $this->table . " p
                  LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                  WHERE p.kode_produk LIKE :keyword 
                     OR p.nama_produk LIKE :keyword
                     OR k.nama_kategori LIKE :keyword
                  ORDER BY p.nama_produk ASC";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        return $stmt;
    }

    public function getLowStock($limit = 10) {
        // Ganti 'k.id' menjadi 'k.id_kategori' dan 'kategori_id' menjadi 'id_kategori'
        $query = "SELECT p.*, k.nama_kategori 
                  FROM " . $this->table . " p
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