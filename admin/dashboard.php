<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get statistics
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'consumer'")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'")->fetchColumn();

// Get orders being processed
$orders_processing = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'processing'")->fetchColumn();

// Get products with low stock
$low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 10")->fetchColumn();

// Get new reviews count
$new_reviews = $pdo->query("SELECT COUNT(*) FROM reviews WHERE DATE(created_at) = CURDATE()")->fetchColumn();

// Get active customers (ordered in last 30 days)
$active_customers = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

// Get recent orders
$stmt = $pdo->query("SELECT orders.*, users.name as user_name 
                     FROM orders 
                     JOIN users ON orders.user_id = users.id 
                     ORDER BY orders.created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll();

// Get monthly sales data for chart
$stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                            SUM(total_amount) as total 
                     FROM orders 
                     WHERE payment_status = 'paid' 
                     GROUP BY month 
                     ORDER BY month DESC 
                     LIMIT 6");
$monthly_sales = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-primary position-relative overflow-hidden mb-4">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="animate__animated animate__fadeInLeft">
                    <div class="d-inline-flex align-items-center bg-white rounded-pill p-1 mb-3">
                        <div class="badge bg-primary rounded-pill px-3 py-2">Admin Dashboard</div>
                        <span class="text-muted px-3">Selamat Datang Kembali! ✨</span>
                    </div>
                    <h1 class="display-4 fw-bold text-white mb-3">
                        Hai, <?= $_SESSION['user_name'] ?>! 
                        <span class="wave">👋</span>
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        Kelola toko Anda dengan mudah dan pantau perkembangan bisnis Anda
                    </p>
                    <div class="d-flex gap-3">
                        <a href="products.php" class="btn btn-light btn-lg px-4">
                            <i class='bx bx-package me-2'></i>Produk
                        </a>
                        <a href="orders.php" class="btn btn-outline-light btn-lg px-4">
                            <i class='bx bx-shopping-bag me-2'></i>Pesanan
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="position-relative">
                    <!-- Animated Elements -->
                    <div class="dashboard-elements">
                        <div class="element element-1 animate__animated animate__zoomIn">
                            <div class="card border-0 shadow-lg">
                                <div class="card-body d-flex align-items-center p-3">
                                    <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                                        <i class='bx bx-chart text-primary fs-4'></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Pendapatan</small>
                                        <h6 class="mb-0">Rp <?= number_format($total_revenue, 0, ',', '.') ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="element element-2 animate__animated animate__zoomIn" style="animation-delay: 0.2s;">
                            <div class="card border-0 shadow-lg">
                                <div class="card-body d-flex align-items-center p-3">
                                    <div class="bg-success bg-opacity-10 rounded-3 p-3 me-3">
                                        <i class='bx bx-shopping-bag text-success fs-4'></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Total Pesanan</small>
                                        <h6 class="mb-0"><?= number_format($total_orders) ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="element element-3 animate__animated animate__zoomIn" style="animation-delay: 0.4s;">
                            <div class="card border-0 shadow-lg">
                                <div class="card-body d-flex align-items-center p-3">
                                    <div class="bg-warning bg-opacity-10 rounded-3 p-3 me-3">
                                        <i class='bx bx-user text-warning fs-4'></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Pelanggan</small>
                                        <h6 class="mb-0"><?= number_format($total_users) ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Background Elements -->
    <div class="position-absolute top-0 end-0 mt-5 me-5">
        <div class="bg-circles"></div>
    </div>
    <div class="position-absolute bottom-0 start-0 mb-5 ms-5">
        <div class="bg-circles"></div>
    </div>
</div>

<!-- Admin Statistics Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-4 animate__animated animate__fadeInUp" style="animation-delay: 0.6s">
            <div class="card-header bg-transparent border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Statistik Admin</h5>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class='bx bx-calendar me-1'></i> Hari Ini
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Hari Ini</a></li>
                            <li><a class="dropdown-item" href="#">Minggu Ini</a></li>
                            <li><a class="dropdown-item" href="#">Bulan Ini</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- Pesanan Diproses -->
                    <div class="col-md-3">
                        <div class="p-3 bg-primary-subtle rounded-4">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-white rounded-3 p-3">
                                    <i class='bx bx-loader-circle fs-4 text-primary rotate-animation'></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Pesanan Diproses</h6>
                                    <h4 class="mb-0 counter"><?= number_format($orders_processing) ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stok Menipis -->
                    <div class="col-md-3">
                        <div class="p-3 bg-warning-subtle rounded-4">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-white rounded-3 p-3">
                                    <i class='bx bx-error-circle fs-4 text-warning pulse-animation'></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Stok Menipis</h6>
                                    <h4 class="mb-0 counter"><?= number_format($low_stock) ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review Baru -->
                    <div class="col-md-3">
                        <div class="p-3 bg-success-subtle rounded-4">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-white rounded-3 p-3">
                                    <i class='bx bx-star fs-4 text-success sparkle-animation'></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Review Baru</h6>
                                    <h4 class="mb-0 counter"><?= number_format($new_reviews) ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pelanggan Aktif -->
                    <div class="col-md-3">
                        <div class="p-3 bg-info-subtle rounded-4">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-white rounded-3 p-3">
                                    <i class='bx bx-user-check fs-4 text-info bounce-animation'></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Pelanggan Aktif</h6>
                                    <h4 class="mb-0 counter"><?= number_format($active_customers) ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background-color: #3498db;
    border-radius: 15px;
    padding: 20px;
}

/* Waving Hand Animation */
.wave {
    animation: wave 2.5s infinite;
    display: inline-block;
    transform-origin: 70% 70%;
}

@keyframes wave {
    0% { transform: rotate(0deg); }
    10% { transform: rotate(14deg); }
    20% { transform: rotate(-8deg); }
    30% { transform: rotate(14deg); }
    40% { transform: rotate(-4deg); }
    50% { transform: rotate(10deg); }
    60% { transform: rotate(0deg); }
    100% { transform: rotate(0deg); }
}

/* Dashboard Elements Animation */
.dashboard-elements {
    position: relative;
    height: 400px;
}

.element {
    position: absolute;
    transition: all 0.3s ease;
}

.element-1 {
    top: 20%;
    right: 10%;
    animation: float 6s ease-in-out infinite;
}

.element-2 {
    top: 50%;
    left: 5%;
    animation: float 8s ease-in-out infinite;
}

.element-3 {
    bottom: 15%;
    right: 15%;
    animation: float 7s ease-in-out infinite;
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
}

/* Background Circles */
.bg-circles {
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.2); opacity: 0.2; }
    100% { transform: scale(1); opacity: 0.5; }
}

/* Hover Effects */
.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

/* Base Animations */
@keyframes wave {
    0% { transform: rotate(0deg); }
    10% { transform: rotate(14deg); }
    20% { transform: rotate(-8deg); }
    30% { transform: rotate(14deg); }
    40% { transform: rotate(-4deg); }
    50% { transform: rotate(10deg); }
    60% { transform: rotate(0deg); }
    100% { transform: rotate(0deg); }
}

@keyframes typing {
    from { width: 0 }
    to { width: 100% }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@keyframes rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes progress-fill {
    0% { width: 0; }
    100% { width: 100%; }
}

/* Applied Animations */
.wave-emoji {
    display: inline-block;
    animation: wave 2.5s infinite;
    transform-origin: 70% 70%;
}

.typing-text {
    overflow: hidden;
    white-space: nowrap;
    border-right: 2px solid #0d6efd;
    animation: typing 3.5s steps(40, end),
               blink-caret .75s step-end infinite;
}

.pulse-button {
    animation: pulse 2s infinite;
}

.rotate-icon {
    transition: transform 0.3s ease;
}

.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.progress-animate .progress-bar {
    animation: progress-fill 1.5s ease-in-out;
}

.counter {
    opacity: 0;
    animation: fadeIn 0.5s ease forwards;
    animation-delay: 1s;
}

/* Card hover effects */
.card {
    transition: all 0.3s ease;
}

.stats-icon:hover {
    animation: rotate 1s linear;
}

/* Custom styling */
.stats-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1) !important; }
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1) !important; }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1) !important; }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1) !important; }

/* Responsive adjustments */
@media (max-width: 768px) {
    .typing-text {
        white-space: normal;
        animation: none;
        border-right: none;
    }
    
    .hover-lift:hover {
        transform: none;
    }
}

/* Add animate.css classes */
.animate__animated {
    animation-duration: 1s;
    animation-fill-mode: both;
}

.animate__fadeIn {
    animation-name: fadeIn;
}

.animate__fadeInUp {
    animation-name: fadeInUp;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

/* Tambahkan animasi baru */
@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

@keyframes sparkle {
    0% { opacity: 1; }
    50% { opacity: 0.5; transform: scale(0.8); }
    100% { opacity: 1; }
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.rotate-animation {
    animation: rotate 2s linear infinite;
}

.pulse-animation {
    animation: pulse 1.5s ease infinite;
}

.sparkle-animation {
    animation: sparkle 2s ease infinite;
}

.bounce-animation {
    animation: bounce 2s ease infinite;
}

/* Styling untuk statistik cards */
.stats-icon {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.rounded-4 {
    border-radius: 15px;
}

/* Counter animation */
.counter {
    opacity: 0;
    animation: fadeIn 0.5s ease forwards;
    animation-delay: 1s;
}

/* Hover effect */
.card:hover .stats-icon {
    transform: scale(1.1);
    transition: transform 0.3s ease;
}

.d-flex {
    position: relative;
    z-index: 10;
}
</style>

<script>
// Add any necessary JavaScript for animations
document.addEventListener('DOMContentLoaded', function() {
    // Counter animation
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        counter.style.opacity = '1';
    });
});
</script>

<?php include 'includes/footer.php'; ?>

