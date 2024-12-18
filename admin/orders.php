<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Pagination setup
$limit = 10; // items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get total records for pagination
$count_query = "SELECT COUNT(*) FROM orders WHERE 1=1";
if ($search) {
    $count_query .= " AND (orders.id LIKE :search 
                    OR users.name LIKE :search 
                    OR users.email LIKE :search
                    OR orders.phone LIKE :search)";
}
if ($status && $status != 'all') {
    $count_query .= " AND orders.order_status = :status";
}

$count_stmt = $pdo->prepare($count_query);
if ($search) {
    $count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
if ($status && $status != 'all') {
    $count_stmt->bindValue(':status', $status, PDO::PARAM_STR);
}
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Modify your main query to include LIMIT and OFFSET
$query = "SELECT orders.*, 
          users.name as customer_name,
          users.email as customer_email,
          users.profile_image,
          payment_methods.name as payment_method,
          (SELECT COUNT(*) FROM order_items WHERE order_items.order_id = orders.id) as total_items
   FROM orders 
   JOIN users ON orders.user_id = users.id
   LEFT JOIN payment_methods ON orders.payment_method_id = payment_methods.id
   WHERE 1=1";

if ($search) {
    $query .= " AND (
        orders.id LIKE :search 
        OR users.name LIKE :search 
        OR users.email LIKE :search
        OR orders.phone LIKE :search
        OR orders.order_status LIKE :search
        OR orders.payment_status LIKE :search
        OR payment_methods.name LIKE :search
        OR orders.total_amount LIKE :search
        OR DATE_FORMAT(orders.created_at, '%d/%m/%Y') LIKE :search
    )";
}

if ($status && $status != 'all') {
    $query .= " AND orders.order_status = :status";
}

$query .= " ORDER BY orders.created_at DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);

if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
if ($status && $status != 'all') {
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
<div class="card-body">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="mb-3 mb-md-0">
            <h3 class="mb-0">
                <i class='bx bx-shopping-bag icon-blue'></i> Daftar Pesanan
            </h3>
            <p class="text-muted mb-0">Kelola semua pesanan pelanggan</p>
        </div>
        <div class="d-flex gap-2 w-100 w-md-auto">
            <!-- Search Box -->
            <form method="GET" class="me-2 w-100 w-md-auto">
                <div class="search-box">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light">
                            <i class='bx bx-search' style="color: #3498db;"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-0 bg-light" 
                               placeholder="Cari pesanan..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
            </form>

            <!-- Filter Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle w-100 w-md-auto" type="button" data-bs-toggle="dropdown">
                    <i class='bx bx-filter-alt me-1'></i> Filter Status
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm filter-dropdown">
                    <li><h6 class="dropdown-header">Status Pesanan</h6></li>
                    <li><a class="dropdown-item <?= $status == 'all' ? 'active' : '' ?>" href="orders.php">
                        <i class='bx bx-list-ul me-2'></i> Semua
                    </a></li>
                    <li><a class="dropdown-item <?= $status == 'pending' ? 'active' : '' ?>" href="?status=pending">
                        <i class='bx bx-time me-2'></i> Menunggu
                    </a></li>
                    <li><a class="dropdown-item <?= $status == 'processing' ? 'active' : '' ?>" href="?status=processing">
                        <i class='bx bx-package me-2'></i> Diproses
                    </a></li>
                    <li><a class="dropdown-item <?= $status == 'shipped' ? 'active' : '' ?>" href="?status=shipped">
                        <i class='bx bx-send me-2'></i> Dikirim
                    </a></li>
                    <li><a class="dropdown-item <?= $status == 'completed' ? 'active' : '' ?>" href="?status=completed">
                        <i class='bx bx-check-circle me-2'></i> Selesai
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</div>


            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if(isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class='bx bx-check-circle me-2'></i>
            <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Orders Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if(empty($orders)): ?>
                <div class="text-center py-5">
    <i class="bi bi-bag-x display-1 text-secondary" style="opacity: 0.6;"></i>
    <h5 class="text-muted mb-0">Belum ada pesanan</h5>
    <p class="text-muted mt-2">Pesanan baru akan muncul di sini</p>
</div>

            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Pelanggan</th>
                                <th>Total Item</th>
                                <th>Total Pembayaran</th>
                                <th>Metode Pembayaran</th>
                                <th>Status Pesanan</th>
                                <th>Status Pembayaran</th>
                                <th>Tanggal</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold">#<?php echo $order['id']; ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if(!empty($order['profile_image'])): ?>
                                                <img src="../assets/uploads/profiles/<?= $order['profile_image'] ?>" 
                                                     alt="<?= htmlspecialchars($order['customer_name']) ?>"
                                                     class="rounded-circle me-3"
                                                     style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            <?php else: ?>
                                                <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
                                                     style="width: 40px; height: 40px; background: var(--bs-primary); color: white; font-weight: 600;">
                                                    <?= strtoupper(substr($order['customer_name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-semibold"><?= htmlspecialchars($order['customer_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($order['customer_email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $order['total_items']; ?> items</td>
                                    <td>
                                        <span class="fw-bold">
                                            Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($order['payment_method']) ?>
                                        <?php if($order['payment_proof']): ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?php 
                                            echo $order['order_status'] == 'completed' ? 'success' : 
                                                ($order['order_status'] == 'processing' ? 'primary' : 
                                                ($order['order_status'] == 'shipped' ? 'info' :
                                                ($order['order_status'] == 'cancelled' ? 'danger' : 'secondary')));
                                        ?>">
                                            <?php 
                                                echo $order['order_status'] == 'pending' ? 'Menunggu' :
                                                    ($order['order_status'] == 'processing' ? 'Diproses' :
                                                    ($order['order_status'] == 'shipped' ? 'Dikirim' :
                                                    ($order['order_status'] == 'completed' ? 'Selesai' : 'Dibatalkan')));
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?php 
                                            echo $order['payment_status'] == 'paid' ? 'success' : 
                                                ($order['payment_status'] == 'pending' ? 'warning' : 'danger');
                                        ?>">
                                            <?php 
                                                echo $order['payment_status'] == 'paid' ? 'Lunas' : 
                                                    ($order['payment_status'] == 'pending' ? 'Menunggu Verifikasi' : 'Belum Bayar');
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                            </small>
                                            <small class="text-muted">
                                                <?php echo date('H:i', strtotime($order['created_at'])); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="order_detail.php?id=<?= $order['id'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bx bx-detail me-1"></i> Detail
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split"
                                                    data-bs-toggle="dropdown"></button>
                                            <ul class="dropdown-menu">
                                                <?php if($order['payment_status'] == 'pending' && $order['payment_proof']): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           onclick="verifyPayment(<?= $order['id'] ?>)">
                                                            <i class="bx bx-check-shield me-2"></i> Terima Pesanan
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <?php if($order['payment_status'] == 'paid'): ?>
                                                    <?php if($order['order_status'] == 'pending'): ?>
                                                        <li>
                                                            <a class="dropdown-item" href="#" 
                                                               onclick="updateStatus(<?= $order['id'] ?>, 'processing')">
                                                                <i class="bx bx-package me-2"></i> Proses Pesanan
                                                            </a>
                                                        </li>
                                                    <?php elseif($order['order_status'] == 'processing'): ?>
                                                        <li>
                                                            <a class="dropdown-item" href="#"
                                                               onclick="updateStatus(<?= $order['id'] ?>, 'shipped')">
                                                                <i class="bx bx-send me-2"></i> Kirim Pesanan
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <?php if($order['order_status'] != 'completed' && $order['order_status'] != 'cancelled'): ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#"
                                                           onclick="updateStatus(<?= $order['id'] ?>, 'cancelled')">
                                                            <i class="bx bx-x-circle me-2"></i> Batalkan
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination with modern styling -->
    <?php if($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link border-0 shadow-sm" href="?page=<?= $page-1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status ? '&status='.urlencode($status) : '' ?>">
                        <i class='bx bx-chevron-left'></i>
                    </a>
                </li>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link border-0 shadow-sm" href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status ? '&status='.urlencode($status) : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link border-0 shadow-sm" href="?page=<?= $page+1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $status ? '&status='.urlencode($status) : '' ?>">
                        <i class='bx bx-chevron-right'></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
function verifyPayment(orderId) {
    if(confirm('Terima pesanan ini? Status pembayaran akan diubah menjadi lunas.')) {
        fetch('actions/verify_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal memverifikasi pembayaran');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem');
        });
    }
}

function updateStatus(orderId, status) {
    if (confirm('Apakah Anda yakin ingin mengubah status pesanan ini?')) {
        fetch('actions/update_order_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal mengubah status pesanan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem');
        });
    }
}

function rotateImage(imageId, degree) {
    const img = document.getElementById(imageId);
    const currentRotation = img.style.transform ? 
        parseInt(img.style.transform.replace('rotate(', '').replace('deg)', '')) : 0;
    const newRotation = currentRotation + degree;
    img.style.transform = `rotate(${newRotation}deg)`;
}

function zoomImage(imageId, factor) {
    const img = document.getElementById(imageId);
    const currentScale = img.style.transform ? 
        parseFloat(img.style.transform.match(/scale\((.*?)\)/)?.[1] || 1) : 1;
    const newScale = Math.max(0.5, Math.min(3, currentScale + factor));
    img.style.transform = `scale(${newScale})`;
}

// Tambahkan event listener untuk reset transform saat modal ditutup
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('hidden.bs.modal', function () {
        const img = this.querySelector('img');
        if (img) {
            img.style.transform = '';
        }
    });
});
</script>

<style>
    :root {
    --primary-color: #3498db;
    --primary-dark: #2980b9;
}
.icon-blue {
    color: #3498db;
}

/* Modern Styling */
.card {
    border-radius: 15px;
    transition: all 0.3s ease; /* Menghapus efek hover */
}

.card:hover {
    transform: none; /* Menghilangkan efek hover */
}

.search-box .input-group {
    border-radius: 10px;
    overflow: hidden;
}

.search-box .form-control:focus {
    box-shadow: none;
}

.table th {
    font-weight: 600;
    color: #4a5568;
}

.table td {
    padding: 1rem;
}

.badge {
    padding: 0.5em 1em;
    font-weight: 500;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: none; /* Tidak ada efek hover */
}

.btn-outline-primary {
    color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-outline-primary:hover,
.btn-outline-primary:focus,
.btn-outline-primary:active {
    background-color: var(--primary-color);
    border-color: var(--primary-dark);
    color: #fff;
}

.dropdown-menu {
    border: 1px solid var(--primary-color);
}

.dropdown-item.active,
.dropdown-item:active {
    background-color: var(--primary-color);
    color: #fff;
}

.dropdown-item:hover {
    background-color: rgba(52, 152, 219, 0.1);
    color: var(--primary-dark);
}

.page-link {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px !important;
    margin: 0 3px;
    color: #4a5568;
}

.page-item.active .page-link {
    background-color: var(--bs-primary);
}

.modal-content {
    border: none;
    border-radius: 15px;
}

.alert {
    border-radius: 12px;
}

/* Header Styling */
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    color: #333;
    padding: 1rem 1.5rem;
}

.card-header:hover {
    background-color: #f8f9fa; /* Tetap tanpa efek hover */
}

</style>

<?php include 'includes/footer.php'; ?>