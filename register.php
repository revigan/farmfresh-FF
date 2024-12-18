<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $error = 'Email sudah terdaftar!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, 'consumer')");
        if ($stmt->execute([$name, $email, $password])) {
            header('Location: login.php');
            exit();
        } else {
            $error = 'Terjadi kesalahan!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - FarmFresh</title>
    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- BoxIcons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="register-card animate-in">
                    <div class="register-header">
                        <div class="brand-icon">
                            <i class='bx bx-leaf'></i>
                        </div>
                        <h4 class="mb-0">Buat Akun Baru</h4>
                        <p class="mb-0">Bergabunglah dengan FarmFresh</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if($error): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class='bx bx-error-circle me-2'></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="form-floating mb-3">
                                <input type="text" name="name" class="form-control" id="name" placeholder="Nama Lengkap" required>
                                <label for="name">
                                    <i class='bx bx-user me-2'></i>Nama Lengkap
                                </label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required>
                                <label for="email">
                                    <i class='bx bx-envelope me-2'></i>Email
                                </label>
                            </div>
                            <div class="form-floating mb-4">
                                <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                                <label for="password">
                                    <i class='bx bx-lock-alt me-2'></i>Password
                                </label>
                            </div>
                            <button type="submit" class="btn btn-register btn-primary w-100 mb-3">
                                <i class='bx bx-user-plus me-2'></i>Daftar Sekarang
                            </button>
                        </form>
                        <p class="text-center mb-0">
                            Sudah punya akun? 
                            <a href="login.php" class="login-link">Masuk disini</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Tambahkan di login.php dan register.php setelah div container -->
<div class="position-fixed top-0 start-0 m-4">
    <a href="index.php" class="btn btn-light rounded-circle shadow-sm p-3" data-bs-toggle="tooltip" title="Kembali ke Beranda">
        <i class='bx bx-arrow-back fs-4'></i>
    </a>
</div>

<script src="assets/js/register.js"></script>



    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
