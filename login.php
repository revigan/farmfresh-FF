<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['user_name'] = $user['name'];

        if ($user['user_type'] == 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    } else {
        $error = 'Email atau password salah!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FarmFresh</title>
    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- BoxIcons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card animate__animated animate__fadeIn">
                    <div class="login-header">
                        <div class="brand-icon">
                            <i class='bx bx-leaf'></i>
                        </div>
                        <h4 class="mb-0">Selamat Datang Kembali!</h4>
                        <p class="mb-0">Silakan masuk ke akun Anda</p>
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
                            <button type="submit" class="btn btn-login btn-primary w-100 mb-3">
                                <i class='bx bx-log-in me-2'></i>Masuk
                            </button>
                        </form>
                        <p class="text-center mb-0">
                            Belum punya akun? 
                            <a href="register.php" class="register-link">Daftar sekarang</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="position-fixed top-0 start-0 m-4">
    <a href="index.php" class="btn btn-light rounded-circle shadow-sm p-3" data-bs-toggle="tooltip" title="Kembali ke Beranda">
        <i class='bx bx-arrow-back fs-4'></i>
    </a>
</div>

<script src="assets/js/login.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
