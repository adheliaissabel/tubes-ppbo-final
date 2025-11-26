<?php

class Database {
    // 1. Properti Private (Encapsulation-penerapan konsep OOP)
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    
    public $conn;

    public function __construct() {
        // 7. konfigurasi host, port, DB, username, dan password. 
    
        $this->host = getenv('MYSQLHOST') ? getenv('MYSQLHOST') : 'localhost';
        $this->port = getenv('MYSQLPORT') ? getenv('MYSQLPORT') : '3306';
        $this->db_name = getenv('MYSQLDATABASE') ? getenv('MYSQLDATABASE') : 'gudang_fashion'; // Samakan nama DB
        $this->username = getenv('MYSQLUSER') ? getenv('MYSQLUSER') : 'root';
        $this->password = getenv('MYSQLPASSWORD') ? getenv('MYSQLPASSWORD') : 'mkjw4004'; // Password lokal kamu
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            // Data Source Name (DSN)
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            // 5. Inisialisasi PDO
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Set Error Mode ke Exception (Penting untuk Debugging)
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Opsional: Set fetch mode default ke Associative Array (Biar coding lebih rapi nanti)
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $exception) {
            // Best Practice: Jangan echo error mentah ke user di production, tapi untuk kuliah ini oke.
            echo "Database Connection Error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>