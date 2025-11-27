<?php
class Admin {
    private $conn;
    private $table = "users";

    private $id;
    private $username;
    private $password;
    private $nama_lengkap;
    private $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getNamaLengkap() { return $this->nama_lengkap; }
    public function getRole() { return $this->role; }

    public function login($usernameInput, $passwordInput) {
        $query = "SELECT id, username, password, nama_lengkap, role 
                  FROM " . $this->table . " 
                  WHERE username = :username LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $usernameInput);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // --- MULAI AREA DEBUGGING (MATA-MATA) ---
            // Kode ini akan memunculkan kotak laporan di layar login
            echo "<div style='background:#fff0f0; border:2px solid red; padding:20px; margin:20px; z-index:9999; position:relative; color:black;'>";
            echo "<h3>🕵️ DIAGNOSA LOGIN</h3>";
            echo "Username Input: <b>" . htmlspecialchars($usernameInput) . "</b><br>";
            echo "Username Database: <b>" . $row['username'] . "</b><br>";
            echo "Password Input: <b>" . htmlspecialchars($passwordInput) . "</b><br>";
            echo "<hr>";
            
            // Cek Hash
            $hash_db = $row['password'];
            $panjang_hash = strlen($hash_db);
            echo "Hash di Database: <br><textarea cols='60' rows='2'>$hash_db</textarea><br>";
            echo "Panjang Hash: <b>$panjang_hash</b> karakter.<br>";
            
            if ($panjang_hash < 60) {
                 echo "<b style='color:red'>[FATAL] Hash Kependekan! Wajib 60 karakter.</b><br>"; 
            } else {
                 echo "<b style='color:green'>[OK] Panjang Hash Valid.</b><br>";
            }

            // Cek Verifikasi
            $cek = password_verify($passwordInput, $hash_db);
            if ($cek) {
                echo "<h2 style='color:green'>✅ STATUS: COCOK! (Harusnya Login Sukses)</h2>";
            } else {
                echo "<h2 style='color:red'>❌ STATUS: GAGAL!</h2>";
                echo "Hash di database BUKAN hasil enkripsi dari '$passwordInput'.<br>";
                echo "<b>Solusi:</b> Copy hash baru ini dan update di HeidiSQL:<br>";
                $new_hash = password_hash($passwordInput, PASSWORD_DEFAULT);
                echo "<textarea cols='60' rows='2'>$new_hash</textarea>";
            }
            echo "</div>";
            // --- SELESAI DEBUGGING ---

            if(password_verify($passwordInput, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->nama_lengkap = $row['nama_lengkap'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }
}
?>