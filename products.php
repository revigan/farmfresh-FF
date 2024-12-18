<?php
session_start();
require_once 'config/database.php';

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

// Handle filter
$category_id = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$query = "SELECT products.*, categories.name as category_name 
          FROM products 
          JOIN categories ON products.category_id = categories.id 
          WHERE products.status = 'available'";

if ($category_id) {
    $query .= " AND products.category_id = :category_id";
}
if ($search) {
    $query .= " AND (products.name LIKE :search OR products.description LIKE :search)";
}

// Add sorting
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY products.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY products.price DESC";
        break;
    default:
        $query .= " ORDER BY products.created_at DESC";
}

$stmt = $pdo->prepare($query);

if ($category_id) {
    $stmt->bindValue(':category_id', $category_id);
}
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}

$stmt->execute();
$products = $stmt->fetchAll();

include 'includes/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">


<div class="container-fluid py-5">
    <!-- Hero Section dengan gradient modern -->
    <div class="hero-section mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold text-white mb-2">Produk Segar Berkualitas</h2>
                <p class="text-white-50 mb-0">Temukan berbagai produk segar dan berkualitas untuk kebutuhan sehari-hari Anda</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="hero-icon">
                    <i class='bx bx-store-alt'></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section dengan design modern -->
    <div class="filter-card mb-5">
        <form method="GET" class="row g-4">
            <div class="col-md-4">
                <div class="form-floating search-input">
                    <select name="category" class="form-select custom-select" id="categorySelect">
                        <option value="">Semua Kategori</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $category_id == $category['id'] ? 'selected' : '' ?>>
                                <?= $category['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label><i class='bx bx-category me-2'></i>Kategori</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-floating search-input">
                    <input type="text" name="search" class="form-control" id="searchInput" 
                           placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                    <label><i class='bx bx-search me-2'></i>Cari Produk</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating search-input">
                    <select name="sort" class="form-select custom-select" id="sortSelect">
                        <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Terbaru</option>
                        <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Harga Terendah</option>
                        <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Harga Tertinggi</option>
                    </select>
                    <label><i class='bx bx-sort me-2'></i>Urutkan</label>
                </div>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn filter-btn h-100 w-100">
                    <i class='bx bx-filter-alt'></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Products Grid dengan animasi -->
    <div class="row g-4">
        <?php foreach($products as $product): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/uploads/products/<?= $product['image'] ?>" 
                             alt="<?= $product['name'] ?>">
                        <div class="product-overlay">
                            <div class="product-category">
                                <?= $product['category_name'] ?>
                            </div>
                            <?php if($product['stock'] < 1): ?>
                                <div class="product-status">
                                    Stok Habis
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="product-content">
                        <h5 class="product-title"><?= $product['name'] ?></h5>
                        <p class="product-description">
                            <?= substr($product['description'], 0, 100) ?>...
                        </p>
                        <div class="product-footer">
                            <div class="product-price">
                                <h5>Rp <?= number_format($product['price'], 0, ',', '.') ?></h5>
                                <small>Stok: <?= $product['stock'] ?></small>
                            </div>
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <button onclick="addToCart(<?= $product['id'] ?>)" 
                                        class="btn-add-cart <?= $product['stock'] < 1 ? 'disabled' : '' ?>">
                                    <i class='bx bx-cart-add'></i>
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="btn-login-buy">
                                    Login untuk Membeli
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if(empty($products)): ?>
            <div class="col-12">
    <div class="empty-state text-center">
        <!-- Ganti gambar dengan icon Bootstrap -->
        <i class="bi bi-file-earmark-x display-1 text-secondary"></i>
        <h4>Tidak ada produk ditemukan</h4>
        <p>Coba ubah filter atau kata kunci pencarian</p>
        <a href="products.php" class="btn btn-primary">
            <i class="bx bx-refresh me-2"></i>Reset Filter
        </a>
    </div>
</div>

        <?php endif; ?>
    </div>
</div>

<link rel="stylesheet" href="assets/css/products.css">
<script src="assets/js/products.js"></script>

<?php include 'includes/footer.php'; ?>
