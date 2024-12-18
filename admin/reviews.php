<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;

// Modify query untuk filter rating
$query = "
    SELECT 
        pr.*, 
        p.name as product_name,
        p.image as product_image,
        u.name as customer_name,
        u.profile_image,
        oi.order_id
    FROM product_ratings pr
    JOIN order_items oi ON pr.order_item_id = oi.id
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    JOIN users u ON o.user_id = u.id
    WHERE 1=1
";

if ($rating_filter > 0) {
    $query .= " AND pr.rating = :rating";
}
$query .= " ORDER BY pr.created_at DESC";

$stmt = $pdo->prepare($query);
if ($rating_filter > 0) {
    $stmt->bindValue(':rating', $rating_filter, PDO::PARAM_INT);
}
$stmt->execute();
$reviews = $stmt->fetchAll();

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
                                <i class='bx bx-star' style='color: #3498db;'></i> Ulasan Produk
                            </h3>
                            <p class="text-muted mb-0">Kelola ulasan dari pelanggan</p>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="dropdown">
                                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class='bx bx-filter-alt me-1'></i> 
                                    Filter: <?= $rating_filter > 0 ? $rating_filter . ' Bintang' : 'Semua Rating'; ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <a class="dropdown-item <?= $rating_filter == 0 ? 'active' : ''; ?>" 
                                           href="reviews.php">
                                            <i class='bx bx-check me-2 <?= $rating_filter == 0 ? '' : 'invisible'; ?>'></i>
                                            Semua Rating
                                        </a>
                                    </li>
                                    <?php for($i = 5; $i >= 1; $i--): ?>
                                        <li>
                                            <a class="dropdown-item <?= $rating_filter == $i ? 'active' : ''; ?>" 
                                               href="?rating=<?= $i; ?>">
                                                <i class='bx bx-check me-2 <?= $rating_filter == $i ? '' : 'invisible'; ?>'></i>
                                                <?php for($j = 1; $j <= $i; $j++): ?>
                                                    <i class='bx bxs-star' style='color: #3498db;'></i>
                                                <?php endfor; ?>
                                                <span class="badge bg-light text-dark ms-2">
                                                    <?= array_count_values(array_column($reviews, 'rating'))[$i] ?? 0; ?>
                                                </span>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(empty($reviews)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class='bx bx-star fs-1 text-muted mb-4'></i>
                <h4 class="text-muted mb-2">Tidak Ada Ulasan</h4>
                <?php if($rating_filter > 0): ?>
                    <p class="text-muted mb-3">
                        Belum ada ulasan dengan rating <?= $rating_filter; ?> bintang
                    </p>
                    <a href="reviews.php" class="btn btn-outline-primary">
                        <i class='bx bx-reset me-2'></i>Tampilkan Semua Rating
                    </a>
                <?php else: ?>
                    <p class="text-muted mb-0">Belum ada ulasan produk dari pembeli</p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="mb-4">
            <p class="text-muted">
                <i class='bx bx-list-ul me-2'></i>
                Menampilkan <?= count($reviews); ?> ulasan
                <?= $rating_filter > 0 ? "dengan rating $rating_filter bintang" : ''; ?>
            </p>
        </div>
        <div class="row g-4">
            <?php foreach($reviews as $review): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-transparent border-0 pt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <?php if(!empty($review['profile_image'])): ?>
                                        <img src="../assets/uploads/profiles/<?= $review['profile_image'] ?>" 
                                             alt="<?= htmlspecialchars($review['customer_name']) ?>"
                                             class="rounded-circle me-3"
                                             style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center"
                                             style="width: 45px; height: 45px; font-weight: 600;">
                                            <?= strtoupper(substr($review['customer_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($review['customer_name']) ?></h6>
                                        <div class="d-flex align-items-center">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i class='bx <?= $i <= $review['rating'] ? 'bxs-star' : 'bx-star text-muted' ?>' style='color: #3498db;'></i>
                                            <?php endfor; ?>
                                            <small class="text-muted ms-2">
                                                <?= date('d M Y', strtotime($review['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <a href="order_detail.php?id=<?= $review['order_id'] ?>" 
                                   class="btn btn-sm btn-light">
                                    #<?= $review['order_id'] ?>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex">
                                <img src="../assets/uploads/products/<?= $review['product_image'] ?>" 
                                     alt="<?= htmlspecialchars($review['product_name']) ?>"
                                     class="rounded"
                                     style="width: 80px; height: 80px; object-fit: cover;">
                                <div class="ms-3">
                                    <h6 class="mb-3"><?= htmlspecialchars($review['product_name']) ?></h6>
                                    <?php if($review['review']): ?>
                                        <p class="text-muted mb-0">
                                            <?= nl2br(htmlspecialchars($review['review'])) ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-muted mb-0 fst-italic">
                                            Tidak ada ulasan tertulis
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 text-muted">
                            <small>
                                <i class='bx bx-time-five'></i> 
                                <?= date('H:i', strtotime($review['created_at'])) ?> WIB
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    :root {
        --primary-color: #3498db; /* Updated primary color */
        --primary-light: rgba(52, 152, 219, 0.1);
    }

    /* Basic styles without hover effects */
    .card {
        border-radius: 15px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        transition: none !important;
    }

    .dropdown-menu {
        border: none;
        border-radius: 12px;
    }

    .dropdown-item {
        padding: 0.7rem 1.2rem;
        transition: none !important;
    }

    .dropdown-item.active {
        background-color: var(--primary-light);
        color: var(--primary-color);
    }

    /* Remove all hover effects */
    .dropdown-item:hover {
        background-color: transparent !important;
        color: inherit !important;
    }

    .btn:hover {
        transform: none !important;
        background-color: inherit !important;
        color: inherit !important;
    }

    .card:hover {
        transform: none !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }

    .order-link:hover {
        color: var(--primary-color) !important;
    }

    .btn-outline-primary:hover {
        background-color: transparent !important;
        color: var(--primary-light) !important;
        border-color: var(--primary-light) !important;
    }

    /* Override Bootstrap's default hover states */
    .btn:active, .btn:focus {
        transform: none !important;
        box-shadow: none !important;
    }

    .dropdown-item:active, .dropdown-item:focus {
        background-color: transparent !important;
    }

    /* Remove transitions */
    * {
        transition: none !important;
    }

    /* Other existing styles remain unchanged */
    .badge {
        padding: 0.5em 0.8em;
        font-weight: 500;
    }

    .btn-light {
        background: #f8f9fa;
        border: none;
    }

    .text-warning {
        color: #ffc107 !important;
    }

    .text-primary {
        color: var(--primary-color) !important;
    }

    .bg-primary {
        background-color: var(--primary-color) !important;
    }

    .bg-primary-subtle {
        background-color: var(--primary-light) !important;
        color: var(--primary-color) !important;
    }

    .rounded-circle.bg-primary {
        background: var(--primary-color) !important;
    }

    .stars .bxs-star {
        color: var(--primary-color);
    }

    .stars .bx-star {
        color: #dee2e6;
    }

    .no-data-icon {
        color: var(--primary-light);
    }

    /* Mengubah warna icon bintang dari kuning ke biru */
    .bxs-star.text-warning {
        color: var(--primary-color) !important;
    }

    /* Header icon color */
    .bx-star.text-warning {
        color: var(--primary-color) !important;
    }

    /* Remove hover styles */
    .table tbody tr:hover {
        background-color: transparent;
    }

    /* Mobile Responsive Adjustments */
    @media (max-width: 576px) {
        .card {
            margin-bottom: 1rem; /* Add margin for spacing */
        }

        .dropdown-menu {
            width: 100%; /* Make dropdown full width on mobile */
        }

        .text-muted {
            font-size: 0.9rem; /* Adjust font size for better readability */
        }
    }
</style>

<?php include 'includes/footer.php'; ?> 