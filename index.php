<?php
session_start();
include 'config/database.php';

$stmt = $pdo->query("SELECT * FROM products WHERE status = 'available' LIMIT 6");
$recommended_products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmFresh - Marketplace Hasil Tani</title>
    <!-- Bootstrap 5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- BoxIcons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href= "assets/css/index.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class='bx bx-leaf fs-3'></i>
                FarmFresh
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class='bx bx-menu fs-3'></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class='bx bx-log-out'></i> Keluar
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class='bx bx-log-in'></i> Masuk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class='bx bx-user-plus'></i> Daftar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title animate__animated animate__fadeInUp">
                        Selamat Datang di FarmFresh
                    </h1>
                    <p class="hero-text animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                        Temukan hasil tani segar langsung dari petani lokal. Kualitas terbaik dengan harga terjangkau.
                    </p>
                    <a href="products.php" class="btn btn-success btn-lg hero-btn animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                        <i class='bx bx-store-alt me-2'></i>
                        Jelajahi Produk
                    </a>
                </div>
                <div class="col-lg-6">
                    <div class="farm-animation">
                        <div class="farm-item tree">
                            <i class='bx bxs-tree'></i>
                        </div>
                        <div class="farm-item fruit">
                            <i class='bx bxs-apple'></i>
                        </div>
                        <div class="farm-item vegetable">
                            <i class='bx bxs-carrot'></i>
                        </div>
                        <div class="farm-item leaf">
                            <i class='bx bx-leaf'></i>
                        </div>
                        <div class="farm-circle"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recommended Products Section -->
    <div class="container my-5">
        <h2 class="text-center mb-4">Rekomendasi Produk</h2>
        <div class="row g-4">
            <?php foreach($recommended_products as $product): ?>
            <div class="col-md-4">
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/uploads/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                    <div class="product-info p-3">
                        <h5 class="product-title"><?php echo $product['name']; ?></h5>
                        <p class="product-desc"><?php echo substr($product['description'], 0, 100); ?>...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="product-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="container">
                    <p>&copy; <?= date('Y') ?> FarmFresh. Semua hak dilindungi.</p>
        <p>Email: support@farmfresh.com | Telepon: 081234567890</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
