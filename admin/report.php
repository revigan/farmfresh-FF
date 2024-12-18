<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Filter date range
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Sales Report Data
$stmt = $pdo->prepare("
    SELECT 
        DATE(o.created_at) as sale_date,
        COUNT(DISTINCT o.id) as total_orders,
        SUM(o.total_amount) as total_sales,
        COUNT(DISTINCT o.user_id) as unique_customers
    FROM orders o
    WHERE o.payment_status = 'paid'
    AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY DATE(o.created_at)
    ORDER BY sale_date DESC
");
$stmt->execute([$start_date, $end_date]);
$sales_data = $stmt->fetchAll();

// Product Report Data
$stmt = $pdo->prepare("
    SELECT 
        p.id, p.name, p.price, p.stock,
        COUNT(DISTINCT oi.order_id) as total_orders,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.quantity * oi.price) as total_revenue,
        COALESCE(AVG(pr.rating), 0) as avg_rating,
        COUNT(pr.id) as total_reviews
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.id AND o.payment_status = 'paid'
    LEFT JOIN product_ratings pr ON oi.id = pr.order_item_id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY p.id
    ORDER BY total_revenue DESC
    LIMIT 5
");
$stmt->execute([$start_date, $end_date]);
$top_products = $stmt->fetchAll();

// Customer Report Data
$stmt = $pdo->prepare("
    SELECT 
        u.id, u.name, u.email,
        COUNT(DISTINCT o.id) as total_orders,
        SUM(o.total_amount) as total_spent,
        MAX(o.created_at) as last_order_date
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id AND o.payment_status = 'paid'
    WHERE u.user_type = 'customer'
    AND (o.created_at IS NULL OR DATE(o.created_at) BETWEEN ? AND ?)
    GROUP BY u.id
    ORDER BY total_spent DESC
    LIMIT 5
");
$stmt->execute([$start_date, $end_date]);
$top_customers = $stmt->fetchAll();

// Calculate summaries
$total_revenue = array_sum(array_column($sales_data, 'total_sales'));
$total_orders = array_sum(array_column($sales_data, 'total_orders'));
$avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;

// Get total products sold
$stmt = $pdo->prepare("
    SELECT SUM(oi.quantity) as total_products_sold
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.payment_status = 'paid'
    AND DATE(o.created_at) BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date]);
$total_products_sold = $stmt->fetch()['total_products_sold'] ?? 0;

// Get total active customers
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT user_id) as active_customers
    FROM orders
    WHERE payment_status = 'paid'
    AND DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date]);
$active_customers = $stmt->fetch()['active_customers'] ?? 0;

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">
                                <i class='bx bx-line-chart text-primary' style='color: #3498db;'></i> Laporan
                            </h3>
                            <p class="text-muted mb-0">Ringkasan data penjualan, produk, dan pelanggan</p>
                        </div>
                        <!-- Date Range Filter -->
                        <div class="d-flex flex-column flex-md-row gap-2 mt-3 mt-md-0">
                            <input type="date" class="form-control" value="<?= $start_date ?>" name="start_date">
                            <input type="date" class="form-control" value="<?= $end_date ?>" name="end_date">
                            <button class="btn btn-primary">
                                <i class='bx bx-filter-alt me-1'></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stats-icon bg-primary-subtle rounded-3 p-3">
                                <i class='bx bx-money fs-4 text-primary'></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted small mb-1">Total Pendapatan</p>
                            <h4 class="mb-0">Rp <?= number_format($total_revenue, 0, ',', '.') ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stats-icon bg-success-subtle rounded-3 p-3">
                                <i class='bx bx-shopping-bag fs-4 text-success'></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted small mb-1">Total Pesanan</p>
                            <h4 class="mb-0"><?= number_format($total_orders) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stats-icon bg-warning-subtle rounded-3 p-3">
                                <i class='bx bx-package fs-4 text-warning'></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted small mb-1">Produk Terjual</p>
                            <h4 class="mb-0"><?= number_format($total_products_sold) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stats-icon bg-info-subtle rounded-3 p-3">
                                <i class='bx bx-user fs-4 text-info'></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted small mb-1">Pelanggan Aktif</p>
                            <h4 class="mb-0"><?= number_format($active_customers) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-pills nav-fill bg-white p-2 rounded-3 shadow-sm mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active px-4" data-bs-toggle="tab" data-bs-target="#sales">
                <i class='bx bx-line-chart me-2'></i>Penjualan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4" data-bs-toggle="tab" data-bs-target="#products">
                <i class='bx bx-package me-2'></i>Produk
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4" data-bs-toggle="tab" data-bs-target="#customers">
                <i class='bx bx-user me-2'></i>Pelanggan
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="reportTabsContent">
        <!-- Sales Report Tab -->
        <div class="tab-pane fade show active" id="sales" role="tabpanel">
            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stats-icon bg-primary bg-opacity-10 text-primary rounded p-3">
                                        <i class='bx bx-money fs-3'></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">Total Pendapatan</h6>
                                    <h3 class="mb-0">Rp <?= number_format($total_revenue, 0, ',', '.') ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stats-icon bg-success bg-opacity-10 text-success rounded p-3">
                                        <i class='bx bx-shopping-bag fs-3'></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">Total Pesanan</h6>
                                    <h3 class="mb-0"><?= number_format($total_orders) ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stats-icon bg-info bg-opacity-10 text-info rounded p-3">
                                        <i class='bx bx-trending-up fs-3'></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">Rata-rata Nilai Pesanan</h6>
                                    <h3 class="mb-0">Rp <?= number_format($avg_order_value, 0, ',', '.') ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Chart -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Products Report Tab -->
        <div class="tab-pane fade" id="products" role="tabpanel">
            <!-- Products Chart -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>

            <!-- Products Table -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-end">Total Penjualan</th>
                                    <th class="text-end">Jumlah Terjual</th>
                                    <th class="text-center">Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td class="text-end">Rp <?= number_format($product['total_revenue'], 0, ',', '.') ?></td>
                                    <td class="text-end"><?= number_format($product['total_quantity']) ?></td>
                                    <td class="text-center">
                                        <div class="text-warning">
                                            <?php 
                                            $rating = round($product['avg_rating']);
                                            for($i = 1; $i <= 5; $i++): 
                                            ?>
                                                <i class='bx <?= $i <= $rating ? 'bxs-star' : 'bx-star' ?>'></i>
                                            <?php endfor; ?>
                                            <small class="text-muted">(<?= $product['total_reviews'] ?>)</small>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Report Tab -->
        <div class="tab-pane fade" id="customers" role="tabpanel">
            <!-- Customer Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stats-icon bg-primary-subtle rounded-3 p-3">
                                        <i class='bx bx-user-check fs-4 text-primary'></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted small mb-1">Total Pelanggan Aktif</p>
                                    <h4 class="mb-0"><?= number_format($active_customers) ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stats-icon bg-success-subtle rounded-3 p-3">
                                        <i class='bx bx-money fs-4 text-success'></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted small mb-1">Rata-rata Pembelian</p>
                                    <h4 class="mb-0">Rp <?= number_format($avg_order_value, 0, ',', '.') ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="stats-icon bg-info-subtle rounded-3 p-3">
                                        <i class='bx bx-cart fs-4 text-info'></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted small mb-1">Total Transaksi</p>
                                    <h4 class="mb-0"><?= number_format($total_orders) ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Chart -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="mb-0">Top 5 Pelanggan berdasarkan Pembelian</h5>
                </div>
                <div class="card-body">
                    <canvas id="customerSpendingChart" style="height: 300px;"></canvas>
                </div>
            </div>

            <!-- Customers Table -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="mb-0">Daftar Pelanggan Teratas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Pelanggan</th>
                                    <th>Email</th>
                                    <th class="text-end">Total Pesanan</th>
                                    <th class="text-end">Total Pembelian</th>
                                    <th>Terakhir Order</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($top_customers)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="bx bx-user-x" style="font-size: 50px; color: #6c757d;"></i>
                                            <h5 class="text-muted mb-0">Belum ada pelanggan</h5>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($top_customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-initial rounded-circle bg-primary-subtle text-primary me-2">
                                                    <?= strtoupper(substr($customer['name'], 0, 1)) ?>
                                                </div>
                                                <?= htmlspecialchars($customer['name']) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($customer['email']) ?></td>
                                        <td class="text-end"><?= number_format($customer['total_orders']) ?></td>
                                        <td class="text-end fw-semibold">
                                            Rp <?= number_format($customer['total_spent'], 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class='bx bx-time-five me-1'></i>
                                                <?= date('d F Y', strtotime($customer['last_order_date'])) ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                            $lastOrderDate = new DateTime($customer['last_order_date']);
                                            $now = new DateTime();
                                            $interval = $now->diff($lastOrderDate)->days;
                                            if($interval <= 30): ?>
                                                <span class="badge bg-success-subtle text-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning">Tidak Aktif</span>
                                            <?php endif; ?>
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
    </div>
</div>

<style>
:root {
    --primary-rgb: 13, 110, 253;
    --success-rgb: 25, 135, 84;
    --warning-rgb: 255, 193, 7;
    --info-rgb: 13, 202, 240;
}

.card {
    border-radius: 15px;
    transition: all 0.2s ease;
}

.stats-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-subtle {
    background-color: rgba(var(--primary-rgb), 0.1) !important;
}

.bg-success-subtle {
    background-color: rgba(var(--success-rgb), 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(var(--warning-rgb), 0.1) !important;
}

.bg-info-subtle {
    background-color: rgba(var(--info-rgb), 0.1) !important;
}

.nav-pills .nav-link {
    color: #6c757d;
    border-radius: 10px;
}

.nav-pills .nav-link.active {
    background-color: var(--bs-primary);
}

.table > :not(caption) > * > * {
    padding: 1rem 1.25rem;
}

.table thead th {
    font-weight: 600;
    color: #344767;
    background-color: #f8f9fa;
}

.form-control {
    border-radius: 10px;
    padding: 0.6rem 1rem;
}

.btn {
    border-radius: 10px;
    padding: 0.6rem 1.2rem;
}

.avatar-initial {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.table th {
    font-weight: 600;
    background-color: #f8f9fa;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.8em;
}

/* Remove hover styles */
.table-hover tbody tr:hover {
    background-color: transparent;
}

/* Mobile Responsive Adjustments */
@media (max-width: 576px) {
    .nav-pills .nav-link {
        font-size: 0.9rem; /* Adjust font size for mobile */
    }

    .card {
        margin-bottom: 1rem; /* Add margin for spacing */
    }

    .table th, .table td {
        font-size: 0.85rem; /* Adjust font size for better readability */
    }

    .stats-icon {
        width: 40px; /* Smaller icon size on mobile */
        height: 40px;
    }

    .avatar-initial {
        width: 30px; /* Smaller avatar size on mobile */
        height: 30px;
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
// Modifikasi script chart untuk menampilkan data baru
const salesData = <?= json_encode($sales_data) ?>;
const topProducts = <?= json_encode($top_products) ?>;
const topCustomers = <?= json_encode($top_customers) ?>;

// Sales Chart
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: salesData.map(data => data.sale_date).reverse(),
        datasets: [{
            label: 'Total Penjualan',
            data: salesData.map(data => data.total_sales).reverse(),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    // ... options tetap sama
});

// Top Products Chart
new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: topProducts.map(p => p.name),
        datasets: [{
            label: 'Total Penjualan',
            data: topProducts.map(p => p.total_revenue),
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            borderColor: '#0d6efd',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

// Customer Spending Chart
new Chart(document.getElementById('customerSpendingChart'), {
    type: 'doughnut',
    data: {
        labels: topCustomers.map(c => c.name),
        datasets: [{
            data: topCustomers.map(c => c.total_spent),
            backgroundColor: [
                'rgba(13, 110, 253, 0.1)',
                'rgba(25, 135, 84, 0.1)',
                'rgba(255, 193, 7, 0.1)',
                'rgba(220, 53, 69, 0.1)',
                'rgba(13, 202, 240, 0.1)'
            ],
            borderColor: [
                '#0d6efd',
                '#198754',
                '#ffc107',
                '#dc3545',
                '#0dcaf0'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
