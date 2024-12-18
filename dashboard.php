<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's recent orders
$stmt = $pdo->prepare("
    SELECT orders.*, COUNT(order_items.id) as total_items 
    FROM orders 
    LEFT JOIN order_items ON orders.id = order_items.order_id 
    WHERE orders.user_id = ? 
    GROUP BY orders.id 
    ORDER BY orders.created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_orders = $stmt->fetchAll();

// Get recommended products
$stmt = $pdo->query("
    SELECT products.*, categories.name as category_name 
    FROM products 
    JOIN categories ON products.category_id = categories.id 
    WHERE products.status = 'available' 
    ORDER BY RAND() 
    LIMIT 6
");
$recommended_products = $stmt->fetchAll();

include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/dashboard.css">

<!-- Hero Section with Wave -->
<div class="hero-section position-relative overflow-hidden mb-5">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-white">
                <h1 class="display-4 fw-bold mb-3">
                    Selamat Datang, <?php echo $_SESSION['user_name']; ?>! 
                    <span class="wave-emoji">👋</span>
                </h1>
                <p class="lead opacity-75 mb-4">Temukan produk segar berkualitas untuk kebutuhan Anda</p>
                <a href="products.php" class="btn btn-light btn-lg rounded-pill px-5 hover-scale">
                    <i class='bx bx-shopping-bag me-2'></i>Mulai Belanja
                </a>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-animation">
                    <div class="floating-cart">
                        <i class='bx bxs-shopping-bag text-white display-1'></i>
                        <div class="floating-item item-1">🥕</div>
                        <div class="floating-item item-2">🥬</div>
                        <div class="floating-item item-3">🍎</div>
                        <div class="floating-item item-4">🥑</div>
                    </div>
                    <div class="decoration-circle circle-1"></div>
                    <div class="decoration-circle circle-2"></div>
                    <div class="decoration-circle circle-3"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="wave-shape">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
</div>

<div class="container py-4">
    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <!-- Stat Card 1 -->
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-icon orders">
                        <i class='bx bx-shopping-bag'></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Total Pesanan</span>
                        <div class="stat-value">
                            <h3 class="counter"><?php echo count($recent_orders); ?></h3>
                            <span class="badge-trend up">
                                <i class='bx bx-up-arrow-alt'></i>12%
                            </span>
                        </div>
                    </div>
                </div>
                <div class="stat-progress">
                    <div class="progress-bar" style="width: <?php echo min(count($recent_orders) * 10, 100); ?>%"></div>
                </div>
            </div>
        </div>
        <!-- Tambahkan card statistik lainnya di sini -->
    </div>

    <!-- Recent Orders -->
    <div class="orders-card mb-5">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1">Pesanan Terakhir</h5>
                    <p class="text-muted small mb-0">5 transaksi terbaru Anda</p>
                </div>
                <a href="orders.php" class="btn btn-light rounded-pill px-4 hover-scale">
                    Lihat Semua<i class='bx bx-right-arrow-alt ms-2'></i>
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if($recent_orders): ?>
                <div class="table-responsive">
                    <table class="table custom-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Tanggal</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_orders as $order): ?>
                            <tr>
                                <td class="order-id">#<?php echo $order['id']; ?></td>
                                <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?php echo $order['total_items']; ?> items
                                    </span>
                                </td>
                                <td class="fw-medium">
                                    Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $order['order_status']; ?>">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" 
                                       class="btn btn-sm btn-light rounded-pill hover-scale">
                                        Detail<i class='bx bx-right-arrow-alt ms-1'></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <img src="assets/img/empty-order.svg" alt="No Orders">
                    <h6>Belum Ada Pesanan</h6>
                    <p>Mulai belanja untuk membuat pesanan pertama Anda</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recommended Products -->
    <div class="recommended-section mb-5">
        <div class="section-header mb-4">
            <h5 class="fw-bold mb-0">Rekomendasi Produk</h5>
            <a href="products.php" class="btn btn-light rounded-pill hover-scale">
                Lihat Semua<i class='bx bx-right-arrow-alt ms-2'></i>
            </a>
        </div>
        <div class="row g-4">
            <?php foreach($recommended_products as $product): ?>
            <div class="col-md-4">
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/uploads/products/<?php echo $product['image']; ?>" 
                             alt="<?php echo $product['name']; ?>">
                        <span class="category-badge">
                            <?php echo $product['category_name']; ?>
                        </span>
                    </div>
                    <div class="product-info">
                        <h5 class="product-title"><?php echo $product['name']; ?></h5>
                        <p class="product-desc">
                            <?php echo substr($product['description'], 0, 100); ?>...
                        </p>
                        <div class="product-footer">
                            <div class="product-price">
                                Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                            </div>
                            <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                                    class="btn btn-primary rounded-pill px-4 hover-scale">
                                <i class='bx bx-cart-add me-1'></i>Tambah
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<script src="assets/js/dashboard.js"></script>

<?php include 'includes/footer.php'; ?>
