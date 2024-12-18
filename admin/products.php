<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Build query with search and filter
$query = "SELECT products.*, categories.name as category_name 
          FROM products 
          LEFT JOIN categories ON products.category_id = categories.id 
          WHERE 1=1";

if ($search) {
    $query .= " AND (products.name LIKE :search OR products.description LIKE :search)";
}
if ($category) {
    $query .= " AND products.category_id = :category";
}
$query .= " ORDER BY products.id DESC";

$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
if ($category) {
    $stmt->bindValue(':category', $category, PDO::PARAM_INT);
}
$stmt->execute();
$products = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Update tampilan dengan tema biru -->
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">
                                <i class='bx bx-package text-primary'></i> Kelola Produk
                            </h3>
                            <p class="text-muted mb-0">Kelola semua produk yang tersedia di toko</p>
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class='bx bx-plus-circle me-2'></i> Tambah Produk
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <div class="search-box">
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 bg-white">
                                        <i class='bx bx-search text-primary'></i>
                                    </span>
                                    <input type="text" name="search" class="form-control border-start-0" 
                                           placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="category" class="form-select">
                                <option value="">Semua Kategori</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class='bx bx-filter-alt me-2'></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($products)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="text-center py-4">
                                        <div class="empty-state">
                                            <i class='bx bx-package text-muted empty-icon'></i>
                                            <h5 class="text-muted mt-3">Tidak ada produk</h5>
                                            <p class="text-muted mb-0">Belum ada produk yang ditambahkan</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($products as $product): ?>
                                <tr data-product-id="<?= $product['id'] ?>">
                                    <td class="ps-4">
                                        <img src="../assets/uploads/products/<?= $product['image'] ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>"
                                             class="product-image">
                                    </td>
                                    <td>
                                        <h6 class="product-name mb-1">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </h6>
                                        <p class="product-desc mb-0">
                                            <?= substr(htmlspecialchars($product['description']), 0, 50) ?>...
                                        </p>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= htmlspecialchars($product['category_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-primary">
                                            Rp <?= number_format($product['price'], 0, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $product['stock'] > 10 ? 'success' : 
                                            ($product['stock'] > 0 ? 'warning' : 'danger') ?>-subtle text-<?= 
                                            $product['stock'] > 10 ? 'success' : ($product['stock'] > 0 ? 'warning' : 'danger') ?>">
                                            <?= $product['stock'] ?> <?= $product['unit'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $product['status'] == 'available' ? 'success' : 
                                            ($product['status'] == 'hidden' ? 'secondary' : 'danger') ?>-subtle text-<?= 
                                            $product['status'] == 'available' ? 'success' : 
                                            ($product['status'] == 'hidden' ? 'secondary' : 'danger') ?>">
                                            <?= ucfirst($product['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-light btn-sm" 
                                                    onclick="viewProduct(<?= $product['id'] ?>)">
                                                <i class='bx bx-show'></i>
                                            </button>
                                            <button type="button" class="btn btn-light btn-sm" 
                                                    onclick="editProduct(<?= $product['id'] ?>)">
                                                <i class='bx bx-edit'></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="deleteProduct(<?= $product['id'] ?>)">
                                                <i class='bx bx-trash'></i> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal tetap sama, hanya update style -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class='bx bx-plus-circle'></i> Tambah Produk Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/add_product.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="price" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stock" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Satuan</label>
                            <input type="text" name="unit" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gambar Produk</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="available">Available</option>
                                <option value="hidden">Hidden</option>
                                <option value="sold_out">Sold Out</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-success">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSS tambahan -->
<style>
:root {
    --primary-color: #3498db;
    --primary-dark: #2980b9;
    --primary-light: #ebf5fb;
}

.card {
    border-radius: 15px;
    transition: none;
}

.card:hover {
    box-shadow: none;
}

.text-primary {
    color: var(--primary-color) !important;
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-primary:hover {
    background-color: var(--primary-dark);
    border-color: var(--primary-dark);
}

.badge.bg-primary {
    background-color: var(--primary-color) !important;
}

.badge.bg-primary-subtle {
    background-color: var(--primary-light) !important;
    color: var(--primary-color) !important;
}

.table th {
    color: var(--primary-dark);
}

.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.product-image:hover {
    transform: scale(1.1);
}

.btn-light:hover {
    background-color: var(--primary-light);
    color: var(--primary-color);
}

.modal-header {
    background: var(--primary-color) !important;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

.table tbody tr:hover {
    background-color: var(--primary-light);
}

.btn-group .btn {
    padding: 0.5rem;
    margin: 0 2px;
    transition: all 0.3s ease;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
}

/* Status badges */
.badge.bg-success-subtle {
    background-color: #e8f8f5 !important;
    color: #27ae60 !important;
}

.badge.bg-warning-subtle {
    background-color: #fef9e7 !important;
    color: #f1c40f !important;
}

.badge.bg-danger-subtle {
    background-color: #fdedec !important;
    color: #e74c3c !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn-group .btn {
        width: 100%;
        margin: 2px 0;
    }
}

/* Mobile Responsive Improvements */
@media (max-width: 768px) {
    /* Container spacing */
    .container-fluid {
        padding: 1rem;
    }

    /* Card adjustments */
    .card {
        margin-bottom: 1rem;
    }

    .card-body {
        padding: 1rem;
    }

    /* Table responsive */
    .table-responsive {
        border: 0;
    }

    /* Table adjustments */
    .table td, .table th {
        padding: 0.75rem;
    }

    /* Product image */
    .product-image {
        width: 50px;
        height: 50px;
    }

    /* Button groups */
    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }

    .btn-group .btn {
        width: 100%;
        margin: 0;
        padding: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Search and filter section */
    .search-box {
        margin-bottom: 1rem;
    }

    .form-select, 
    .form-control {
        margin-bottom: 0.5rem;
    }

    /* Header section */
    .card-body .d-flex {
        flex-direction: column;
        gap: 1rem;
    }

    .card-body .btn {
        width: 100%;
    }

    /* Table content */
    .table td {
        white-space: normal;
        min-width: 120px;
    }

    .table td:first-child {
        padding-left: 1rem;
    }

    .table td:last-child {
        padding-right: 1rem;
    }

    /* Product description */
    .product-desc {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Modal adjustments */
    .modal-dialog {
        margin: 0.5rem;
    }

    .modal-body {
        padding: 1rem;
    }

    /* Badge adjustments */
    .badge {
        display: inline-block;
        width: 100%;
        margin: 0.25rem 0;
        text-align: center;
    }
}

/* Small mobile devices */
@media (max-width: 576px) {
    /* Further reduce spacing */
    .container-fluid {
        padding: 0.5rem;
    }

    /* Stack form elements */
    .row.g-3 > [class*='col-'] {
        padding: 0.5rem;
    }

    /* Adjust button sizes */
    .btn {
        padding: 0.5rem;
        font-size: 0.875rem;
    }

    /* Reduce heading sizes */
    h3 {
        font-size: 1.25rem;
    }

    /* Adjust table cell padding */
    .table td, .table th {
        padding: 0.5rem;
    }
}

/* Remove all hover effects */
.table tbody tr:hover,
.btn:hover,
.dropdown-item:hover,
.card:hover,
.form-control:hover,
.form-select:hover {
    background-color: transparent !important;
    transform: none !important;
    box-shadow: none !important;
    color: inherit !important;
}

/* Remove all transitions */
* {
    transition: none !important;
}

/* Empty state styling */
.empty-state {
    padding: 2rem 1rem;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    /* Empty state adjustments */
    .empty-state {
        padding: 3rem 1rem;
    }
    
    .empty-icon {
        font-size: 3rem;
    }
    
    .empty-state h5 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        font-size: 0.9rem;
        padding: 0 1rem;
    }

    /* Table adjustments */
    .table td[colspan="7"] {
        padding: 0 !important;
    }

    /* Container spacing */
    .container-fluid {
        padding: 1rem;
    }

    /* Card adjustments */
    .card {
        margin-bottom: 1rem;
    }

    .card-body {
        padding: 1rem;
    }

    /* Button groups */
    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }

    .btn-group .btn {
        width: 100%;
        margin: 0;
        padding: 0.5rem;
    }

    /* Search and filter section */
    .row.g-3 {
        margin: 0;
    }

    .col-md-6, 
    .col-md-4, 
    .col-md-2 {
        padding: 0.5rem;
    }

    /* Form controls */
    .form-control,
    .form-select,
    .btn {
        height: 40px;
        font-size: 0.9rem;
    }
}

/* Small mobile devices */
@media (max-width: 576px) {
    /* Empty state further adjustments */
    .empty-state {
        padding: 2rem 0.5rem;
    }
    
    .empty-icon {
        font-size: 2.5rem;
    }

    /* Reduce container padding */
    .container-fluid {
        padding: 0.5rem;
    }

    /* Adjust heading sizes */
    h3 {
        font-size: 1.25rem;
    }

    /* Stack form elements */
    .row.g-3 > [class*='col-'] {
        padding: 0.25rem;
    }
}

.table-responsive {
    overflow-x: auto; /* Enable horizontal scrolling */
    -ms-overflow-style: none;  /* Hide scrollbar for Internet Explorer and Edge */
    scrollbar-width: none;  /* Hide scrollbar for Firefox */
}

.table-responsive::-webkit-scrollbar {
    display: none; /* Hide scrollbar for Chrome, Safari, and Opera */
}
</style>

<script>
function viewProduct(id) {
    window.location.href = 'view_product.php?id=' + id;
}

function editProduct(id) {
    window.location.href = 'edit_product.php?id=' + id;
}

function deleteProduct(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Produk yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading
            Swal.fire({
                title: 'Menghapus Produk...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Kirim request hapus
            fetch(`actions/delete_product.php?id=${id}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Refresh halaman atau hapus baris produk
                        document.querySelector(`tr[data-product-id="${id}"]`).remove();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menghapus produk'
                });
                console.error('Error:', error);
            });
        }
    });
}

function addToCart(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', 1);

    fetch('actions/add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count in navbar
            const cartBadge = document.querySelector('.cart-count');
            if (cartBadge) {
                cartBadge.textContent = data.cart_count;
            }
            
            // Show success message
            Swal.fire({
                title: 'Berhasil!',
                text: 'Produk berhasil ditambahkan ke keranjang',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Lihat Keranjang',
                cancelButtonText: 'Lanjut Belanja'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'cart.php';
                }
            });
        } else {
            Swal.fire({
                title: 'Gagal!',
                text: data.message,
                icon: 'error'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Terjadi kesalahan saat menambahkan ke keranjang',
            icon: 'error'
        });
    });
}
</script>

<?php include 'includes/footer.php'; ?>
