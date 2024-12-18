<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's orders
$stmt = $pdo->prepare("
    SELECT orders.*, 
           COUNT(order_items.id) as total_items,
           GROUP_CONCAT(CONCAT(products.name, ' (', order_items.quantity, ')') SEPARATOR ', ') as items,
           COALESCE(payment_methods.name, 'Metode pembayaran tidak tersedia') as payment_method_name
    FROM orders 
    LEFT JOIN order_items ON orders.id = order_items.order_id
    LEFT JOIN products ON order_items.product_id = products.id
    LEFT JOIN payment_methods ON orders.payment_method_id = payment_methods.id
    WHERE orders.user_id = ?
    GROUP BY orders.id
    ORDER BY orders.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Hero Section -->
    <div class="hero-section mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold text-white mb-2">Riwayat Pesanan</h2>
                <p class="text-white-50 mb-0">Pantau status pesanan dan berikan penilaian</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="hero-icon">
                    <i class='bx bx-package'></i>
                </div>
            </div>
        </div>
    </div>

    <?php if(isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class='bx bx-package'></i>
            </div>
            <h4>Belum ada pesanan</h4>
            <p>Anda belum memiliki riwayat pesanan</p>
           <a href="products.php" class="btn-shop">
    <i class='bx bx-shopping-bag'></i>
    <span>Mulai Belanja</span>
</a>

        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($orders as $order): ?>
                <div class="col-12 mb-4">
                    <div class="order-card">
                        <div class="order-header">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div class="order-info">
                                    <h5>Order #<?= $order['id'] ?></h5>
                                    <span class="order-date">
                                        <i class='bx bx-time-five'></i>
                                        <?= date('d F Y H:i', strtotime($order['created_at'])) ?>
                                    </span>
                                </div>
                                <div class="order-status">
                                    <?php if($order['payment_status'] == 'pending'): ?>
                                        <span class="status-badge warning">Menunggu Verifikasi</span>
                                    <?php elseif($order['payment_status'] == 'paid'): ?>
                                        <span class="status-badge success">Pembayaran Diterima</span>
                                    <?php endif; ?>

                                    <?php if($order['order_status'] == 'pending'): ?>
                                        <span class="status-badge warning">Menunggu Diproses</span>
                                    <?php elseif($order['order_status'] == 'processing'): ?>
                                        <span class="status-badge info">Sedang Diproses</span>
                                    <?php elseif($order['order_status'] == 'shipped'): ?>
                                        <span class="status-badge primary">Sedang Dikirim</span>
                                    <?php elseif($order['order_status'] == 'completed'): ?>
                                        <span class="status-badge success">Selesai</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="order-body">
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <div class="shipping-info">
                                        <h6>
                                            <i class='bx bx-map-pin'></i>
                                            Detail Pengiriman
                                        </h6>
                                        <div class="info-grid">
                                            <div class="info-item">
                                                <label>Penerima</label>
                                                <p><?= htmlspecialchars($order['recipient_name']) ?></p>
                                            </div>
                                            <div class="info-item">
                                                <label>Telepon</label>
                                                <p><?= htmlspecialchars($order['phone']) ?></p>
                                            </div>
                                            <div class="info-item full-width">
                                                <label>Alamat</label>
                                                <p><?= htmlspecialchars($order['address']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="payment-info">
                                        <h6>
                                            <i class='bx bx-wallet'></i>
                                            Pembayaran
                                        </h6>
                                        <div class="amount">
                                            <label>Total:</label>
                                            <h5>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></h5>
                                        </div>
                                        <div class="payment-method">
                                            <?= htmlspecialchars($order['payment_method_name']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if($order['order_status'] == 'shipped'): ?>
                                <div class="action-section">
                                    <button class="btn-confirm" onclick="confirmReceived(<?= $order['id'] ?>)">
                                        <i class='bx bx-check-double'></i>
                                        <span>Konfirmasi Pesanan Diterima</span>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php if($order['order_status'] == 'completed'): 
                                $stmt = $pdo->prepare("
                                    SELECT oi.*, p.name as product_name, 
                                           (SELECT rating FROM product_ratings WHERE order_item_id = oi.id) as rating
                                    FROM order_items oi 
                                    JOIN products p ON oi.product_id = p.id 
                                    WHERE oi.order_id = ?
                                ");
                                $stmt->execute([$order['id']]);
                                $items = $stmt->fetchAll();
                            ?>
                                <div class="rating-section">
                                    <h6>
                                        <i class='bx bx-star'></i>
                                        Rating Produk
                                    </h6>
                                    <div class="rating-list">
                                        <?php foreach($items as $item): ?>
                                            <div class="rating-item">
                                                <div class="product-info">
                                                    <span class="product-name"><?= htmlspecialchars($item['product_name']) ?></span>
                                                    <span class="quantity">(<?= $item['quantity'] ?> item)</span>
                                                </div>
                                                <?php if(!$item['rating']): ?>
                                                    <button class="btn-rate" 
                                                            onclick="showRatingModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['product_name']) ?>')">
                                                        <i class='bx bx-star'></i>
                                                        Beri Rating
                                                    </button>
                                                <?php else: ?>
                                                    <div class="rating-stars">
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <i class='bx <?= $i <= $item['rating'] ? 'bxs-star' : 'bx-star' ?>'></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if($item !== end($items)): ?>
                                                <hr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class='bx bx-star'></i>
                    Beri Rating
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="rating-content">
                    <div class="product-badge">
                        <i class='bx bx-package'></i>
                        <span id="productName"></span>
                    </div>
                    <div class="rating-stars-container">
                        <i class='bx bx-star star-rating' data-rating="1"></i>
                        <i class='bx bx-star star-rating' data-rating="2"></i>
                        <i class='bx bx-star star-rating' data-rating="3"></i>
                        <i class='bx bx-star star-rating' data-rating="4"></i>
                        <i class='bx bx-star star-rating' data-rating="5"></i>
                    </div>
                    <div class="form-floating">
                        <textarea id="review" class="form-control" placeholder="Tulis review Anda"></textarea>
                        <label for="review">Review (Opsional)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 d-flex gap-3">
                <button type="button" class="btn-cancel flex-fill" data-bs-dismiss="modal">
                    <i class='bx bx-x'></i>
                    <span>Batal</span>
                </button>
                <button type="button" class="btn-save flex-fill" onclick="submitRating()">
                    <i class='bx bx-check'></i>
                    <span>Simpan</span>
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="assets/css/orders.css">

<script src="assets/js/orders.js"></script>

<?php include 'includes/footer.php'; ?>
