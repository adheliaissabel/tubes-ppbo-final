<?php
session_start();

// Jika sudah login, langsung  ke Dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: views/dashboard.php");
    exit();
}

// Proses Login saat tombol ditekan
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'config/database.php';
    require_once 'models/Admin.php';
    
    $database = new Database();
    $db = $database->getConnection();
    $admin = new Admin($db);
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    if($admin->login($username, $password)) {
        // PERBAIKAN: Menggunakan Getter karena properti di Model sekarang Private
        $_SESSION['user_id'] = $admin->getId();
        $_SESSION['username'] = $admin->getUsername();
        $_SESSION['nama_lengkap'] = $admin->getNamaLengkap();
        $_SESSION['role'] = $admin->getRole();
        
        // Redirect ke Dashboard
        header("Location: views/dashboard.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIGUDA PPBO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .card-header {
            background: transparent;
            border-bottom: none;
            padding-top: 30px;
            text-align: center;
        }
        .login-icon {
            font-size: 3rem;
            color: #0d6efd;
        }
    </style>
</head>
<body>

    <div class="card login-card bg-white">
        <div class="card-header">
            <i class="bi bi-box-seam-fill login-icon"></i>
            <h3 class="mt-2 fw-bold text-primary">SIGUDA</h3>
            <p class="text-muted">Sistem Gudang Fashion</p>
        </div>
        <div class="card-body p-4">
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger text-center py-2" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i> <?= $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukan username" required autofocus>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukan password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">MASUK SISTEM</button>
            </form>
        </div>
        <div class="card-footer text-center py-3 border-0 bg-light rounded-bottom">
            <small class="text-muted">Gunakan akun: <b>admin</b> / <b>admin123</b></small>
        </div>
    </div>

</body>
</html>