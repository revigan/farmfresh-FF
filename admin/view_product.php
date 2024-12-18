<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$id = $_GET['id'];

// Get product details
$stmt = $pdo->prepare("
    SELECT products.*, categories.name as category_name 
    FROM products 
    JOIN categories ON products.category_id = categories.id 
    WHERE products.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get product reviews
$stmt = $pdo->prepare("
    SELECT reviews.*, users.name as user_name 
    FROM reviews 
    JOIN users ON reviews.user_id = users.id 
    WHERE reviews.product_id = ? 
    ORDER BY reviews.created_at DESC
");
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex align-items-center">
                    <h3 class="mb-0 me-3">
                        <i class='bx bx-package text-primary'></i> Detail Produk
                    </h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 mt-2">
                            <li class="breadcrumb-item">
                                <a href="products.php" class="text-decoration-none">
                                    <i class='bx bx-store'></i> Produk
                                </a>
                            </li>
                            <li class="breadcrumb-item active"><?= $product['name'] ?></li>
                        </ol>
                    </nav>
                </div>
                <a href="products.php" class="btn btn-light">
                    <i class='bx bx-arrow-back me-2'></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Product Details Card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="position-relative">
                    <img src="../assets/uploads/products/<?= $product['image'] ?>" 
                         class="card-img-top rounded-top" 
                         alt="<?= $product['name'] ?>"
                         style="height: 300px; object-fit: cover;">
                    <div class="position-absolute top-0 end-0 p-3">
                        <span class="badge bg-<?= $product['status'] == 'available' ? 'success' : 
                            ($product['status'] == 'hidden' ? 'secondary' : 'danger') ?> rounded-pill">
                            <?= ucfirst($product['status']) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <h4 class="card-title mb-3 fw-bold"><?= $product['name'] ?></h4>
                    <p class="card-text text-muted"><?= $product['description'] ?></p>
                    
                    <div class="product-info mt-4">
                        <div class="info-item d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">
                                <i class='bx bx-category me-2'></i>Kategori
                            </span>
                            <span class="badge bg-light text-dark"><?= $product['category_name'] ?></span>
                        </div>
                        <div class="info-item d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">
                                <i class='bx bx-money me-2'></i>Harga
                            </span>
                            <span class="fw-bold text-primary fs-5">
                                Rp <?= number_format($product['price'], 0, ',', '.') ?>
                            </span>
                        </div>
                        <div class="info-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class='bx bx-box me-2'></i>Stok
                            </span>
                            <span class="badge bg-<?= $product['stock'] > 10 ? 'success' : 
                                ($product['stock'] > 0 ? 'warning' : 'danger') ?> rounded-pill">
                                <?= $product['stock'] ?> <?= $product['unit'] ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-0 p-4">
                    <button type="button" class="btn btn-primary w-100" 
                            onclick="editProduct(<?= $product['id'] ?>)">
                        <i class='bx bx-edit me-2'></i>Edit Produk
                    </button>
                </div>
            </div>
        </div>

        <!-- Reviews Card -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class='bx bx-star text-warning me-2'></i>Ulasan Produk
                    </h5>
                </div>
                <div class="card-body">
                    <?php if(count($reviews) > 0): ?>
                        <div class="reviews-list">
                            <?php foreach($reviews as $review): ?>
                                <div class="review-item border-bottom mb-4 pb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="review-avatar bg-light rounded-circle p-2 me-3">
                                                <i class='bx bx-user fs-4'></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1"><?= $review['user_name'] ?></h6>
                                                <div class="text-warning">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <i class='bx <?= $i <= $review['rating'] ? 'bxs-star' : 'bx-star' ?>'></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <i class='bx bx-calendar me-1'></i>
                                            <?= date('d/m/Y', strtotime($review['created_at'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-0 text-muted"><?= $review['comment'] ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="empty-reviews mb-3">
                                <i class='bx bx-message-square-x text-muted' style="font-size: 4rem;"></i>
                            </div>
                            <h6 class="text-muted mb-2">Belum Ada Ulasan</h6>
                            <p class="text-muted mb-0">Produk ini belum memiliki ulasan dari pembeli.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 15px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.badge {
    padding: 0.6em 1em;
    font-weight: 500;
}

.product-info .info-item {
    padding: 0.8rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.product-info .info-item:last-child {
    border-bottom: none;
}

.review-avatar {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
}

.review-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.btn {
    padding: 0.7rem 1.5rem;
    border-radius: 8px;
}

.empty-reviews {
    opacity: 0.7;
}

.breadcrumb-item + .breadcrumb-item::before {
    font-family: "boxicons";
    content: "\ea6e";
}
</style>

<script>
function editProduct(id) {
    window.location.href = 'edit_product.php?id=' + id;
}
</script>

<?php include 'includes/footer.php'; ?>
