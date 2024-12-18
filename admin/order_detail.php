<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = $_GET['id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT orders.*, 
           users.name as customer_name,
           users.email as customer_email,
           users.profile_image,
           payment_methods.name as payment_method,
           payment_methods.account_number
    FROM orders 
    JOIN users ON orders.user_id = users.id
    LEFT JOIN payment_methods ON orders.payment_method_id = payment_methods.id
    WHERE orders.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$stmt = $pdo->prepare("
    SELECT order_items.*, products.name as product_name
    FROM order_items 
    JOIN products ON order_items.product_id = products.id
    WHERE order_items.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex align-items-center">
                            <h3 class="mb-0 me-3">
                                <i class='bx bx-package' style="color: #3498db;"></i> Detail Pesanan #<?= $order_id ?>
                            </h3>
                        </div>
                        <a href="orders.php" class="btn btn-light">
                            <i class='bx bx-arrow-back me-2'></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Order Items Section -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class='bx bx-cart text-primary me-2'></i>Item Pesanan
                        </h5>
                        <span class="badge bg-primary rounded-pill">
                            <?= count($order_items) ?> Items
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Produk</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end pe-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($order_items as $item): ?>
                                <tr>
                                    <td class="ps-4">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['product_name']) ?></h6>
                                        <small class="text-muted">SKU: #<?= $item['product_id'] ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark rounded-pill">
                                            <?= $item['quantity'] ?>x
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        Rp <?= number_format($item['price'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-end pe-4 fw-bold">
                                        Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold ps-4">Total Pembayaran</td>
                                    <td class="text-end pe-4">
                                        <h5 class="text-primary mb-0">
                                            Rp <?= number_format($order['total_amount'], 0, ',', '.') ?>
                                        </h5>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Payment Info Section -->
        <div class="col-lg-4">
            <div class="row g-4">
                <!-- Customer Info Card -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="card-title mb-0">
                                <i class='bx bx-user-circle text-primary me-2'></i>Informasi Pelanggan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <?php if(!empty($order['profile_image'])): ?>
                                    <img src="../assets/uploads/profiles/<?= $order['profile_image'] ?>" 
                                         alt="<?= htmlspecialchars($order['customer_name']) ?>"
                                         class="rounded-circle me-3"
                                         style="width: 48px; height: 48px; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <?php else: ?>
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                         style="width: 48px; height: 48px; background: var(--bs-primary); color: white;">
                                        <?= strtoupper(substr($order['customer_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-1"><?= htmlspecialchars($order['customer_name']) ?></h6>
                                    <p class="mb-0 text-muted"><?= htmlspecialchars($order['customer_email']) ?></p>
                                </div>
                            </div>
                            <div class="mb-3 d-flex align-items-center">
                                <i class='bx bx-phone fs-5 me-2 text-primary'></i>
                                <span><?= htmlspecialchars($order['phone']) ?></span>
                            </div>
                            <div class="d-flex align-items-start">
                                <i class='bx bx-map fs-5 me-2 text-primary'></i>
                                <span><?= htmlspecialchars($order['address']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Info Card -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="card-title mb-0">
                                <i class='bx bx-credit-card text-primary me-2'></i>Informasi Pembayaran
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="text-muted small d-block mb-2">Metode Pembayaran</label>
                                    <span class="fw-semibold"><?= htmlspecialchars($order['payment_method']) ?></span>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small d-block mb-2">No. Rekening</label>
                                    <span class="fw-semibold"><?= htmlspecialchars($order['account_number']) ?></span>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small d-block mb-2">Status Pembayaran</label>
                                    <?php if($order['payment_status'] == 'pending'): ?>
                                        <span class="badge bg-warning">Menunggu Verifikasi</span>
                                    <?php elseif($order['payment_status'] == 'paid'): ?>
                                        <span class="badge bg-success">Lunas</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Belum Dibayar</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small d-block mb-2">Status Pesanan</label>
                                    <?php if($order['order_status'] == 'pending'): ?>
                                        <span class="badge bg-warning">Menunggu Diproses</span>
                                    <?php elseif($order['order_status'] == 'processing'): ?>
                                        <span class="badge bg-info">Sedang Diproses</span>
                                    <?php elseif($order['order_status'] == 'shipped'): ?>
                                        <span class="badge bg-primary">Sedang Dikirim</span>
                                    <?php elseif($order['order_status'] == 'completed'): ?>
                                        <span class="badge bg-success">Selesai</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if($order['payment_status'] == 'pending'): ?>
                                    <button class="btn btn-success" onclick="verifyPayment(<?= $order_id ?>)">
                                        <i class='bx bx-check-circle me-2'></i>Verifikasi Pembayaran
                                    </button>
                                <?php endif; ?>
                                
                                <?php if($order['payment_status'] == 'paid' && $order['order_status'] == 'pending'): ?>
                                    <button class="btn btn-primary" onclick="updateStatus(<?= $order_id ?>, 'processing')">
                                        <i class='bx bx-package me-2'></i>Proses Pesanan
                                    </button>
                                <?php endif; ?>
                                
                                <?php if($order['order_status'] == 'processing'): ?>
                                    <button class="btn btn-info text-white" onclick="updateStatus(<?= $order_id ?>, 'shipped')">
                                        <i class='bx bx-send me-2'></i>Kirim Pesanan
                                    </button>
                                <?php endif; ?>

                                <?php if($order['order_status'] == 'shipped'): ?>
                                    <button class="btn btn-success" onclick="updateStatus(<?= $order_id ?>, 'completed')">
                                        <i class='bx bx-check-double me-2'></i>Selesaikan Pesanan
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Styling */
.card {
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0;
}

.table th {
    font-weight: 600;
    color: #4a5568;
}

.badge {
    padding: 0.6em 1em;
    font-weight: 500;
}

.btn {
    padding: 0.7rem 1.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

.bg-light {
    background-color: #f8f9fa !important;
}

.breadcrumb-item + .breadcrumb-item::before {
    color: #6c757d;
}

.table > :not(caption) > * > * {
    padding: 1rem;
}



.text-primary {
    color: #3498db !important;
}
.badge.bg-primary {
    background-color: #3498db !important;
    color: #fff;
}
.border-bottom {
    border-color: #e9ecef !important;
}
</style>

<script>
function verifyPayment(orderId) {
    if(confirm('Konfirmasi pembayaran untuk pesanan ini?')) {
        const formData = new FormData();
        formData.append('order_id', orderId);

        fetch('actions/verify_payment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message || 'Pembayaran berhasil diverifikasi');
                location.reload();
            } else {
                throw new Error(data.message || 'Gagal memverifikasi pembayaran');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Terjadi kesalahan sistem');
        });
    }
}

function updateStatus(orderId, status) {
    if(confirm('Ubah status pesanan?')) {
        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('status', status);

        fetch('actions/update_order_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message || 'Status pesanan berhasil diubah');
                location.reload();
            } else {
                throw new Error(data.message || 'Gagal mengubah status pesanan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Terjadi kesalahan sistem');
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
