<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$id = $_GET['id'];

// Get product data
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">
                                <i class='bx bx-edit text-primary' ></i> Edit Produk
                            </h3>
                            <p class="text-muted mb-0">Edit informasi produk <?= htmlspecialchars($product['name']) ?></p>
                        </div>
                        <a href="products.php" class="btn btn-light">
                            <i class='bx bx-arrow-back me-2'></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class='bx bx-error-circle me-2'></i>
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class='bx bx-check-circle me-2'></i>
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0">Informasi Produk</h5>
                </div>
                <div class="card-body">
                    <form action="actions/update_product.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        
                        <div class="row g-4">
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" name="name" class="form-control" id="productName"
                                           value="<?= htmlspecialchars($product['name']) ?>" required>
                                    <label for="productName">Nama Produk</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="category_id" class="form-select" id="productCategory" required>
                                        <?php foreach($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>" 
                                                <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="productCategory">Kategori</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="status" class="form-select" id="productStatus">
                                        <option value="available" <?= $product['status'] == 'available' ? 'selected' : '' ?>>
                                            Available
                                        </option>
                                        <option value="hidden" <?= $product['status'] == 'hidden' ? 'selected' : '' ?>>
                                            Hidden
                                        </option>
                                        <option value="sold_out" <?= $product['status'] == 'sold_out' ? 'selected' : '' ?>>
                                            Sold Out
                                        </option>
                                    </select>
                                    <label for="productStatus">Status</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="number" name="price" class="form-control" id="productPrice"
                                           value="<?= $product['price'] ?>" required>
                                    <label for="productPrice">Harga (Rp)</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="number" name="stock" class="form-control" id="productStock"
                                           value="<?= $product['stock'] ?>" required>
                                    <label for="productStock">Stok</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" name="unit" class="form-control" id="productUnit"
                                           value="<?= htmlspecialchars($product['unit']) ?>" required>
                                    <label for="productUnit">Satuan</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea name="description" class="form-control" id="productDescription" 
                                              style="height: 120px" required><?= htmlspecialchars($product['description']) ?></textarea>
                                    <label for="productDescription">Deskripsi</label>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="card-title mb-0">Gambar Produk</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <?php if($product['image']): ?>
                            <img src="../assets/uploads/products/<?= $product['image'] ?>" 
                                 alt="Current Image" class="img-preview rounded">
                        <?php else: ?>
                            <div class="no-image-placeholder">
                                <i class='bx bx-image'></i>
                                <p>Tidak ada gambar</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Upload Gambar Baru</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="form-text">Format: JPG, PNG, GIF (Max. 2MB)</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-save me-2'></i> Simpan Perubahan
                        </button>
                        <a href="products.php" class="btn btn-light">
                            <i class='bx bx-x me-2'></i> Batal
                        </a>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-color: #3498db;
    --primary-dark: #2980b9;
}

.card {
    border-radius: 15px;
    transition: none;
}

.card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    transition: none;
}

.form-control, .form-select {
    border-radius: 8px;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

.form-floating > .form-control,
.form-floating > .form-select {
    height: calc(3.5rem + 2px);
    line-height: 1.25;
}

.form-floating > textarea.form-control {
    height: 120px;
}

.img-preview {
    max-width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.img-preview:hover {
    transform: scale(1.02);
}

.no-image-placeholder {
    width: 100%;
    height: 200px;
    background: #f8f9fa;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
}

.no-image-placeholder i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.btn {
    padding: 0.6rem 1.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

.alert {
    border: none;
    border-radius: 10px;
}

.form-text {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}
</style>

<?php include 'includes/footer.php'; ?>
