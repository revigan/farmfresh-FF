<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get cart items
$stmt = $pdo->prepare("
    SELECT cart_items.*, products.name, products.price, products.image 
    FROM cart_items 
    JOIN products ON cart_items.product_id = products.id 
    WHERE cart_items.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Get user's address
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Hero Section -->
    <div class="hero-section mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold text-white mb-2">Checkout</h2>
                <p class="text-white-50 mb-0">Lengkapi informasi pengiriman dan pembayaran</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="hero-icon">
                    <i class='bx bx-credit-card'></i>
                </div>
            </div>
        </div>
    </div>

    <?php if(isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="actions/place_order.php" class="needs-validation" novalidate>
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Shipping Address -->
                <div class="checkout-card mb-4">
                    <div class="card-body">
                        <div class="section-title">
                            <i class='bx bx-map-pin'></i>
                            <span>Alamat Pengiriman</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="recipient_name" class="form-control custom-input" 
                                           id="recipientName" placeholder="Nama Penerima"
                                           value="<?php echo $user['name']; ?>" required>
                                    <label for="recipientName">Nama Penerima</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="tel" name="phone" class="form-control custom-input" 
                                           id="phone" placeholder="Nomor Telepon"
                                           value="<?php echo $user['phone']; ?>" required>
                                    <label for="phone">Nomor Telepon</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea name="address" class="form-control custom-input" id="address" 
                                              placeholder="Alamat Lengkap" style="height: 100px" 
                                              required><?php echo $user['address']; ?></textarea>
                                    <label for="address">Alamat Lengkap</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="checkout-card mb-4">
                    <div class="card-body">
                        <div class="section-title">
                            <i class='bx bx-cart'></i>
                            <span>Pesanan Anda</span>
                        </div>
                        <?php foreach($cart_items as $item): ?>
                            <div class="order-item">
                                <img src="assets/uploads/products/<?php echo $item['image']; ?>" 
                                     alt="<?php echo $item['name']; ?>">
                                <div class="item-details">
                                    <h6><?php echo $item['name']; ?></h6>
                                    <span class="quantity">
                                        <?php echo $item['quantity']; ?> x 
                                        Rp <?php echo number_format($item['price'], 0, ',', '.'); ?>
                                    </span>
                                </div>
                                <div class="item-price">
                                    <h6>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></h6>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="checkout-card">
                    <div class="card-body">
                        <div class="section-title">
                            <i class='bx bx-wallet'></i>
                            <span>Metode Pembayaran</span>
                        </div>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM payment_methods WHERE is_active = 1");
                        $payment_methods = $stmt->fetchAll();
                        
                        if (empty($payment_methods)): ?>
                            <div class="alert alert-warning">
                                Tidak ada metode pembayaran yang tersedia
                            </div>
                        <?php else: 
                            foreach($payment_methods as $method): ?>
                            <label class="payment-method-option" for="payment_<?= $method['id'] ?>">
                                <input type="radio" name="payment_method" 
                                       id="payment_<?= $method['id'] ?>" 
                                       value="<?= $method['id'] ?>" required>
                                <div class="payment-method-content">
                                    <div class="payment-info">
                                        <strong><?= htmlspecialchars($method['name']) ?></strong>
                                        <div class="account-info">
                                            <?= htmlspecialchars($method['account_number']) ?> 
                                            (<?= htmlspecialchars($method['account_name']) ?>)
                                        </div>
                                        <?php if($method['description']): ?>
                                            <small><?= htmlspecialchars($method['description']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="payment-check">
                                        <i class='bx bx-check-circle'></i>
                                    </div>
                                </div>
                            </label>
                            <?php endforeach;
                        endif; ?>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="checkout-card summary-card">
                    <div class="card-body">
                        <div class="section-title">
                            <i class='bx bx-receipt'></i>
                            <span>Ringkasan Pembayaran</span>
                        </div>
                        <div class="summary-item">
                            <span>Total Item</span>
                            <span><?php echo count($cart_items); ?> item</span>
                        </div>
                        <div class="summary-item">
                            <span>Total Harga</span>
                            <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                        </div>
                        <div class="summary-total">
                            <span>Total Pembayaran</span>
                            <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                        </div>
                        <button type="submit" class="checkout-button">
                            <i class='bx bx-check-circle'></i>
                            <span>Selesaikan Pembayaran</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<link rel="stylesheet" href="assets/css/checkout.css">

<script src="assets/js/checkout.js"></script>

<?php include 'includes/footer.php'; ?>
