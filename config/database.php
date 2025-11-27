<?php
class Database {
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        // Ambil data dari Environment Variable Vercel
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT');
        $db_name = getenv('DB_DATABASE');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');

        try {
            // Gunakan PGSQL driver
            $dsn = "pgsql:host=" . $host . ";port=" . ($port ? $port : '5432') . ";dbname=" . $db_name;
            
            $this->conn = new PDO($dsn, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $exception) {
            // Jika error koneksi, tampilkan di layar (Hanya untuk debugging)
            die("<h1>Database Connection Error:</h1> " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>