<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit();
}

$id = $_GET['id'];

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: users.php');
    exit();
}

// Get user's orders
$stmt = $pdo->prepare("
    SELECT orders.*, COUNT(order_items.id) as total_items 
    FROM orders 
    LEFT JOIN order_items ON orders.id = order_items.order_id 
    WHERE orders.user_id = ? 
    GROUP BY orders.id 
    ORDER BY orders.created_at DESC
");
$stmt->execute([$id]);
$orders = $stmt->fetchAll();

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
                                <i class='bx bx-user-circle' style='color: #3498db;'></i> Detail Pengguna
                            </h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 mt-2">
                                    <li class="breadcrumb-item">
                                        <a href="users.php" class="text-decoration-none">
                                            <i class='bx bx-users'></i> Pengguna
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item active"><?= $user['name'] ?></li>
                                </ol>
                            </nav>
                        </div>
                        <a href="users.php" class="btn btn-light">
                            <i class='bx bx-arrow-back me-2'></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- User Profile Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <?php if(!empty($user['profile_image'])): ?>
                        <img src="../assets/uploads/profiles/<?= $user['profile_image'] ?>" 
                             alt="<?= $user['name'] ?>"
                             class="rounded-circle mb-3"
                             style="width: 100px; height: 100px; object-fit: cover;">
                    <?php else: ?>
                        <div class="user-avatar mx-auto mb-3">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <h5 class="mb-1"><?= $user['name'] ?></h5>
                    <p class="text-muted mb-3">
                        <i class='bx bx-envelope me-1'></i><?= $user['email'] ?>
                    </p>
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-primary-subtle text-primary">
                            <i class='bx bx-user me-1'></i>Customer
                        </span>
                        <span class="badge bg-success-subtle text-success">
                            <i class='bx bx-check-circle me-1'></i>Aktif
                        </span>
                    </div>
                    <hr>
                    <div class="row text-start g-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Member Sejak</small>
                            <span><i class='bx bx-calendar me-1'></i><?= date('d M Y', strtotime($user['created_at'])) ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Total Pesanan</small>
                            <span><i class='bx bx-shopping-bag me-1'></i><?= count($orders) ?> Pesanan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order History -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class='bx bx-history' style='color: #3498db;' class="me-2"></i>Riwayat Pesanan
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if(empty($orders)): ?>
                        <div class="text-center py-5">
                            <img src="../assets/images/no-data.svg" alt="No Orders" 
                                 class="mb-3" style="width: 200px; opacity: 0.6">
                            <h6 class="text-muted mb-0">Belum ada pesanan</h6>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">ID Pesanan</th>
                                        <th>Tanggal</th>
                                        <th>Total Item</th>
                                        <th>Total Pembayaran</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($orders as $order): ?>
                                        <tr>
                                            <td class="ps-4 fw-semibold">#<?= $order['id'] ?></td>
                                            <td>
                                                <i class='bx bx-calendar me-1'></i>
                                                <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?= $order['total_items'] ?> Item
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">
                                                    Rp <?= number_format($order['total_amount'], 0, ',', '.') ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                    $status_class = [
                                                        'pending' => 'warning',
                                                        'processing' => 'info',
                                                        'shipped' => 'primary',
                                                        'completed' => 'success'
                                                    ][$order['order_status']];
                                                ?>
                                                <span class="badge bg-<?= $status_class ?>-subtle text-<?= $status_class ?>">
                                                    <?= ucfirst($order['order_status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="order_detail.php?id=<?= $order['id'] ?>" 
                                                   class="btn btn-sm btn-light">
                                                    <i class='bx bx-show'></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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

.user-avatar {
    width: 80px;
    height: 80px;
    background: #3498db;
    color: white;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 600;
}

.badge {
    padding: 0.6em 1em;
    font-weight: 500;
}

.table thead th {
    font-weight: 600;
    color: #344767;
}

.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.1);
}

.bg-success-subtle {
    background-color: rgba(46, 204, 113, 0.1);
}

.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
}

/* Hide horizontal scrollbar */
.table-responsive {
    overflow-x: auto; /* Enable horizontal scrolling */
    -ms-overflow-style: none;  /* Hide scrollbar for Internet Explorer and Edge */
    scrollbar-width: none;  /* Hide scrollbar for Firefox */
}

.table-responsive::-webkit-scrollbar {
    display: none; /* Hide scrollbar for Chrome, Safari, and Opera */
}

/* Remove hover effects */
.btn:hover,
.table tbody tr:hover {
    background-color: inherit !important; /* Keep background color the same */
    color: inherit !important; /* Keep text color the same */
    transform: none !important; /* Prevent floating effect */
}
</style>

<?php include 'includes/footer.php'; ?>
