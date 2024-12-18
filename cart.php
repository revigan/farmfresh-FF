<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get cart items
$stmt = $pdo->prepare("
    SELECT cart_items.*, products.name, products.price, products.image, products.stock 
    FROM cart_items 
    JOIN products ON cart_items.product_id = products.id 
    WHERE cart_items.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="hero-section mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="fw-bold text-white mb-2">Keranjang Belanja</h2>
                <p class="text-white-50 mb-0">Review dan atur pesanan Anda</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="hero-icon">
                    <i class='bx bx-cart-alt'></i>
                </div>
            </div>
        </div>
    </div>

    <?php if(!empty($cart_items)): ?>
        <div class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card cart-card">
                    <div class="card-body p-4">
                        <?php foreach($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <img src="assets/uploads/products/<?php echo $item['image']; ?>" 
                                         alt="<?php echo $item['name']; ?>">
                                </div>
                                
                                <div class="cart-item-details">
                                    <h5><?php echo $item['name']; ?></h5>
                                    <p class="price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                                </div>

                                <div class="cart-item-quantity">
                                    <div class="quantity-control">
                                        <button class="btn-quantity" 
                                                onclick="updateQuantity(<?= $item['id']; ?>, 'decrease')"
                                                <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>
                                            <i class='bx bx-minus'></i>
                                        </button>
                                        <input type="number" class="quantity-input" 
                                               value="<?= $item['quantity']; ?>" 
                                               min="1" max="<?= $item['stock']; ?>" 
                                               id="quantity_<?= $item['id']; ?>"
                                               onchange="updateQuantity(<?= $item['id']; ?>, 'set', this.value)">
                                        <button class="btn-quantity" 
                                                onclick="updateQuantity(<?= $item['id']; ?>, 'increase')"
                                                <?= $item['quantity'] >= $item['stock'] ? 'disabled' : '' ?>>
                                            <i class='bx bx-plus'></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="cart-item-total">
                                    <h6>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></h6>
                                </div>

                                <button class="btn-remove" onclick="removeItem(<?= $item['id'] ?>)" title="Hapus Item">
                                    <span class="btn-remove-icon">
                                        <i class='bx bx-trash'></i>
                                    </span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Summary Card -->
            <div class="col-lg-4">
                <div class="card summary-card">
                    <div class="card-body p-4">
                        <h5 class="summary-title">Ringkasan Belanja</h5>
                        <div class="summary-item">
                            <span>Total Item</span>
                            <span><?php echo count($cart_items); ?> item</span>
                        </div>
                        <div class="summary-item total">
                            <span>Total Harga</span>
                            <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                        </div>
                        <div class="summary-actions">
                            <a href="checkout.php" class="btn-checkout">
                                <i class='bx bx-check-circle'></i>Checkout
                            </a>
                            <a href="products.php" class="btn-continue">
                                <i class='bx bx-arrow-back'></i>Lanjut Belanja
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">
                <i class='bx bx-shopping-bag'></i>
            </div>
            <h4 class="mt-4 mb-3">Keranjang Belanja Kosong</h4>
            <p class="text-muted mb-4">Yuk mulai belanja produk segar kami!</p>
            <a href="products.php" class="btn-shop-now">
                <i class='bx bx-store'></i>
                <span>Belanja Sekarang</span>
            </a>
        </div>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="assets/css/cart.css">

<script src="assets/js/cart.js"></script>

<?php include 'includes/footer.php'; ?>
